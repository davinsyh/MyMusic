import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.data('audioPlayer', () => ({
        expanded: false,
        currentTrack: null,
        queue: [],
        originalQueue: [],
        queueContext: null,
        currentIndex: -1,
        isPlaying: false,
        currentTime: 0,
        duration: 0,
        progress: 0,
        volume: 100,
        isSaved: false,
        hoverTime: '',
        ytPlayerReady: false,
        ytInterval: null,
        mockInterval: null,
        pendingTrackId: null,
        isShuffle: false,
        repeatMode: 0,
        _altSkipIds: [],
        instanceId: Math.random().toString(36).substring(7),

        init() {
            window.allAudioPlayers = window.allAudioPlayers || [];
            // Bersihkan instansi yang sudah tidak terhubung ke DOM
            window.allAudioPlayers = window.allAudioPlayers.filter(p => p.$el && p.$el.isConnected);

            // Buat getter dinamis untuk activeAudioPlayer jika belum ada
            if (!Object.getOwnPropertyDescriptor(window, 'activeAudioPlayer')) {
                Object.defineProperty(window, 'activeAudioPlayer', {
                    get: function() {
                        if (!window.allAudioPlayers) return null;
                        return window.allAudioPlayers.find(p => p.$el && p.$el.isConnected) || null;
                    },
                    configurable: true
                });
            }

            // Daftarkan instansi saat ini
            window.allAudioPlayers.push(this);

            const activePlayer = window.activeAudioPlayer;
            if (activePlayer && activePlayer !== this) {
                console.log('[YT][' + this.instanceId + '] Instansi duplikat terdeteksi (sedang dalam transisi Livewire). Mengabaikan inisialisasi. Instansi aktif saat ini:', activePlayer.instanceId);
                return;
            }

            console.log('[YT][' + this.instanceId + '] Alpine component init() - instansi utama diaktifkan');

            if (window.musicPlayerState) {
                // Restore state dari global state
                this.currentTrack = window.musicPlayerState.currentTrack;
                this.queue = window.musicPlayerState.queue;
                this.originalQueue = window.musicPlayerState.originalQueue;
                this.queueContext = window.musicPlayerState.queueContext;
                this.currentIndex = window.musicPlayerState.currentIndex;
                this.isPlaying = window.musicPlayerState.isPlaying;
                this.currentTime = window.musicPlayerState.currentTime;
                this.duration = window.musicPlayerState.duration;
                this.progress = window.musicPlayerState.progress;
                this.volume = window.musicPlayerState.volume;
                this.isSaved = window.musicPlayerState.isSaved;
                this.isShuffle = window.musicPlayerState.isShuffle;
                this.repeatMode = window.musicPlayerState.repeatMode;
                this._altSkipIds = window.musicPlayerState._altSkipIds;

                if (this.isPlaying && window.ytPlayer) {
                    console.log('[YT][' + this.instanceId + '] Lagu sedang terputar, memulai timer update...');
                    this.startTimer();
                }
            } else {
                // Inisialisasi global state awal
                window.musicPlayerState = {
                    currentTrack: this.currentTrack,
                    queue: this.queue,
                    originalQueue: this.originalQueue,
                    queueContext: this.queueContext,
                    currentIndex: this.currentIndex,
                    isPlaying: this.isPlaying,
                    currentTime: this.currentTime,
                    duration: this.duration,
                    progress: this.progress,
                    volume: this.volume,
                    isSaved: this.isSaved,
                    isShuffle: this.isShuffle,
                    repeatMode: this.repeatMode,
                    _altSkipIds: this._altSkipIds,
                };
            }

            // Pasang watchers untuk menyinkronkan state lokal ke global state
            const keysToSync = [
                'currentTrack', 'queue', 'originalQueue', 'queueContext', 'currentIndex',
                'isPlaying', 'currentTime', 'duration', 'progress', 'volume', 'isSaved',
                'isShuffle', 'repeatMode', '_altSkipIds'
            ];
            keysToSync.forEach(key => {
                this.$watch(key, (value) => {
                    window.musicPlayerState[key] = value;
                });
            });

            this.initPlayer();
        },

        initPlayer() {
            // Guard: hanya buat player sekali secara global
            if (window._ytPlayerInit) {
                // Player sudah ada, sinkronkan state
                if (window.ytPlayer && typeof window.ytPlayer.loadVideoById === 'function') {
                    this.ytPlayerReady = true;
                }
                return;
            }

            const self = this;
            const tryCreatePlayer = () => {
                if (window.YT && window.YT.Player) {
                    self.createYTPlayer();
                } else {
                    window.onYouTubeIframeAPIReady = () => self.createYTPlayer();
                }
            };
            tryCreatePlayer();
        },

        createYTPlayer() {
            // Guard: cegah pembuatan player ganda
            if (window._ytPlayerInit) {
                if (window.ytPlayer && typeof window.ytPlayer.loadVideoById === 'function') {
                    this.ytPlayerReady = true;
                    if (this.pendingTrackId) {
                        window.ytPlayer.loadVideoById(this.pendingTrackId);
                        this.pendingTrackId = null;
                    }
                }
                return;
            }
            window._ytPlayerInit = true;

            console.log('[YT][' + this.instanceId + '] Creating new YT.Player');
            window.ytPlayer = new YT.Player('yt-player-container', {
                height: '300',
                width: '300',
                videoId: '',
                playerVars: {
                    'autoplay': 0,
                    'playsinline': 1,
                    'controls': 0,
                    'disablekb': 1,
                    'fs': 0,
                    'rel': 0,
                    'modestbranding': 1
                },
                events: {
                    'onReady': (e) => {
                        console.log('[YT] YT.Player onReady event');
                        if (window.activeAudioPlayer) window.activeAudioPlayer.onPlayerReady(e);
                    },
                    'onStateChange': (e) => {
                        console.log('[YT] YT.Player onStateChange event, data:', e.data);
                        if (window.activeAudioPlayer) window.activeAudioPlayer.onPlayerStateChange(e);
                    },
                    'onError': (e) => {
                        console.log('[YT] YT.Player onError event, data:', e.data);
                        if (window.activeAudioPlayer) window.activeAudioPlayer.onPlayerError(e);
                    }
                }
            });
        },

        onPlayerError(event) {
            console.warn('[YT][' + this.instanceId + '] Player Error code:', event.data, '| Track:', this.currentTrack?.title);

            if ((event.data === 150 || event.data === 101 || event.data === 100) && this.currentTrack) {
                console.log('[YT][' + this.instanceId + '] Mencari alternatif untuk:', this.currentTrack.title, '| Skip:', this._altSkipIds);
                this.searchAndPlayAlternative(this.currentTrack);
            } else {
                // Error lain (2=invalid ID, 5=HTML5 error) - skip ke lagu berikutnya
                this._altSkipIds = [];
                this.nextTrack();
            }
        },

        async searchAndPlayAlternative(track) {
            const currentId = track.id || track.videoId;
            // Tambahkan ID yang gagal ke daftar skip
            if (!this._altSkipIds.includes(currentId)) {
                this._altSkipIds.push(currentId);
            }

            // Maksimal 5 percobaan
            if (this._altSkipIds.length > 5) {
                console.warn('[YT][' + this.instanceId + '] Sudah mencoba', this._altSkipIds.length, 'video, skip ke lagu berikutnya');
                this._altSkipIds = [];
                this.nextTrack();
                return;
            }

            try {
                // Cari dengan kata kunci berbeda setiap percobaan untuk hasil bervariasi
                const attempts = [
                    `${track.title} ${track.artist}`,
                    `${track.title} ${track.artist} official`,
                    `${track.title} ${track.artist} lyrics`,
                    `${track.title} ${track.artist} audio`,
                    `${track.title} official music video`,
                ];
                const attemptIdx = Math.min(this._altSkipIds.length - 1, attempts.length - 1);
                const query = encodeURIComponent(attempts[attemptIdx]);

                const res = await fetch(`/api/music/search?q=${query}`);
                if (!res.ok) throw new Error('Search failed');
                const data = await res.json();

                const alternatives = (data.data || []).filter(r =>
                    r.id &&
                    !this._altSkipIds.includes(r.id) &&
                    !r.id.startsWith('lp-') &&
                    !r.id.startsWith('lm-') &&
                    r.id.length === 11  // YouTube video ID standar = 11 karakter
                );

                if (alternatives.length > 0) {
                    const alt = alternatives[0];
                    console.log('[YT][' + this.instanceId + '] Mencoba alternatif ke-' + this._altSkipIds.length + ':', alt.id, '|', alt.title);
                    this._altSkipIds.push(alt.id);
                    window.ytPlayer.loadVideoById(alt.id);
                } else {
                    console.warn('[YT][' + this.instanceId + '] Tidak ada alternatif, skip ke lagu berikutnya');
                    this._altSkipIds = [];
                    this.nextTrack();
                }
            } catch (e) {
                console.error('[YT][' + this.instanceId + '] Gagal mencari alternatif:', e);
                this._altSkipIds = [];
                this.nextTrack();
            }
        },

        onPlayerReady(event) {
            this.ytPlayerReady = true;
            window.ytPlayer.setVolume(this.volume);
            console.log('[YT][' + this.instanceId + '] Player siap!');
            if (this.pendingTrackId) {
                window.ytPlayer.loadVideoById(this.pendingTrackId);
                this.pendingTrackId = null;
            }
        },

        onPlayerStateChange(event) {
            console.log('[YT][' + this.instanceId + '] onPlayerStateChange. State:', event.data);
            if (event.data == YT.PlayerState.PLAYING) {
                this.isPlaying = true;
                this._altSkipIds = []; // Reset saat lagu berhasil diputar
                this.duration = window.ytPlayer.getDuration();
                this.startTimer();
            } else if (event.data == YT.PlayerState.PAUSED) {
                this.isPlaying = false;
                this.clearTimer();
            } else if (event.data == YT.PlayerState.ENDED) {
                this.isPlaying = false;
                this.clearTimer();
                this.trackEnded();
            }
        },

        playQueue({ queue, index, context = null }) {
            this.originalQueue = [...queue];
            this.queue = [...queue];
            this.currentIndex = index;
            this.queueContext = context;

            if (this.isShuffle) {
                this.applyShuffle();
            }

            this.playTrack(this.queue[this.currentIndex]);
        },

        applyShuffle() {
            if (this.queue.length > 1) {
                const current = this.queue[this.currentIndex];
                let remaining = this.queue.filter((_, i) => i !== this.currentIndex);
                for (let i = remaining.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [remaining[i], remaining[j]] = [remaining[j], remaining[i]];
                }
                this.queue = [current, ...remaining];
                this.currentIndex = 0;
            }
        },

        toggleShuffle() {
            this.isShuffle = !this.isShuffle;
            if (this.isShuffle) {
                this.applyShuffle();
            } else {
                if (this.originalQueue && this.originalQueue.length > 0) {
                    const current = this.queue[this.currentIndex];
                    this.queue = [...this.originalQueue];
                    this.currentIndex = this.queue.findIndex(t => t.id === current.id);
                }
            }
        },

        toggleRepeat() {
            this.repeatMode = (this.repeatMode + 1) % 3;
        },

        nextTrack() {
            if (this.queue.length > 0) {
                if (this.currentIndex < this.queue.length - 1) {
                    this.currentIndex++;
                    this.playTrack(this.queue[this.currentIndex]);
                } else if (this.repeatMode === 1) {
                    this.currentIndex = 0;
                    this.playTrack(this.queue[this.currentIndex]);
                }
            }
        },

        prevTrack() {
            if (this.queue.length > 0) {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                    this.playTrack(this.queue[this.currentIndex]);
                } else if (this.repeatMode === 1) {
                    this.currentIndex = this.queue.length - 1;
                    this.playTrack(this.queue[this.currentIndex]);
                }
            }
        },

        async playTrack(track) {
            this.currentTrack = track;
            const rawId = track.id || track.videoId;

            this.clearTimer();
            this.clearMockTimer();

            // Resolve YouTube Music internal IDs (lp-, lm- prefix) ke video ID biasa
            let trackId = rawId;
            if (rawId && (rawId.startsWith('lp-') || rawId.startsWith('lm-'))) {
                try {
                    const res = await fetch(`/api/music/resolve/${rawId}`);
                    if (res.ok) {
                        const data = await res.json();
                        trackId = data.videoId || rawId;
                        console.log('[YT][' + this.instanceId + '] Resolved', rawId, '->', trackId);
                    }
                } catch (e) {
                    // Fallback: strip prefix
                    trackId = rawId.substring(3);
                    console.warn('[YT][' + this.instanceId + '] Resolve gagal, pakai fallback:', trackId);
                }
            }

            console.log('[YT][' + this.instanceId + '] Mencoba memutar trackId:', trackId);
            console.log('[YT][' + this.instanceId + '] ytPlayerReady:', this.ytPlayerReady, '| window.ytPlayer:', window.ytPlayer);

            if (this.ytPlayerReady && window.ytPlayer && typeof window.ytPlayer.loadVideoById === 'function') {
                window.ytPlayer.loadVideoById(trackId);
            } else {
                this.pendingTrackId = trackId;
                console.log('[YT][' + this.instanceId + '] Player belum siap, disimpan sebagai pending:', trackId);

                let retries = 0;
                const retryInterval = setInterval(() => {
                    retries++;
                    if (window.ytPlayer && typeof window.ytPlayer.loadVideoById === 'function') {
                        console.log('[YT][' + this.instanceId + '] Player siap setelah', retries, 'percobaan, memutar...');
                        window.ytPlayer.loadVideoById(this.pendingTrackId || trackId);
                        this.pendingTrackId = null;
                        clearInterval(retryInterval);
                    } else if (retries >= 15) {
                        console.error('[YT][' + this.instanceId + '] Player gagal dimuat, fallback ke mockPlay');
                        clearInterval(retryInterval);
                        this.mockPlay();
                    }
                }, 300);
            }
        },

        trackEnded() {
            this.isPlaying = false;
            this.progress = 0;
            this.currentTime = 0;

            if (this.repeatMode === 2) {
                this.playTrack(this.queue[this.currentIndex]);
                return;
            }

            if (this.queue.length > 0) {
                if (this.currentIndex < this.queue.length - 1) {
                    this.nextTrack();
                } else if (this.repeatMode === 1) {
                    this.currentIndex = 0;
                    this.playTrack(this.queue[0]);
                }
            }
        },

        saveTrack() {
            if (!this.currentTrack) return;
            this.isSaved = !this.isSaved;
            this.$dispatch('saveTrackToLibrary', { track: this.currentTrack });
        },

        togglePlay() {
            console.log('[YT][' + this.instanceId + '] togglePlay called. isPlaying:', this.isPlaying);
            if (!this.currentTrack || !window.ytPlayer) {
                console.warn('[YT][' + this.instanceId + '] togglePlay aborted: no track or player');
                return;
            }

            let isCurrentlyPlaying = this.isPlaying;
            if (window.ytPlayer && typeof window.ytPlayer.getPlayerState === 'function') {
                const state = window.ytPlayer.getPlayerState();
                isCurrentlyPlaying = (state === 1 || state === 3);
                console.log('[YT][' + this.instanceId + '] getPlayerState():', state, '-> isCurrentlyPlaying:', isCurrentlyPlaying);
            }

            if (isCurrentlyPlaying) {
                console.log('[YT][' + this.instanceId + '] Call pauseVideo()');
                window.ytPlayer.pauseVideo();
            } else {
                console.log('[YT][' + this.instanceId + '] Call playVideo()');
                window.ytPlayer.playVideo();
            }
        },

        mockPlay() {
            this.isPlaying = true;
            this.duration = 180;
            this.currentTime = 0;
            this.progress = 0;
            this.startMockTimer();
        },

        startTimer() {
            if (window.activeYtInterval) clearInterval(window.activeYtInterval);
            window.activeYtInterval = setInterval(() => {
                if (this.isPlaying && window.ytPlayer && window.ytPlayer.getCurrentTime) {
                    this.currentTime = window.ytPlayer.getCurrentTime();
                    this.progress = (this.currentTime / this.duration) * 100;
                }
            }, 1000);
            this.ytInterval = window.activeYtInterval;
        },

        clearTimer() {
            if (window.activeYtInterval) {
                clearInterval(window.activeYtInterval);
                window.activeYtInterval = null;
            }
            this.ytInterval = null;
        },

        startMockTimer() {
            if (window.activeMockInterval) clearInterval(window.activeMockInterval);
            window.activeMockInterval = setInterval(() => {
                if (this.isPlaying) {
                    this.currentTime += 1;
                    this.progress = (this.currentTime / this.duration) * 100;
                    if (this.currentTime >= this.duration) {
                        this.isPlaying = false;
                        this.clearMockTimer();
                        this.trackEnded();
                    }
                }
            }, 1000);
            this.mockInterval = window.activeMockInterval;
        },

        clearMockTimer() {
            if (window.activeMockInterval) {
                clearInterval(window.activeMockInterval);
                window.activeMockInterval = null;
            }
            this.mockInterval = null;
        },

        hoverProgress(e) {
            if (!this.duration) return;
            const rect = e.currentTarget.getBoundingClientRect();
            const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            const timeInSeconds = this.duration * percent;
            this.hoverTime = this.formatTime(timeInSeconds);
        },

        setVolume(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            this.volume = percent * 100;
            if (window.ytPlayer && window.ytPlayer.setVolume) {
                window.ytPlayer.setVolume(this.volume);
            }
        },

        seek(e) {
            if (!this.duration) return;
            const rect = e.currentTarget.getBoundingClientRect();
            const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            this.currentTime = this.duration * percent;
            if (window.ytPlayer && window.ytPlayer.seekTo) {
                window.ytPlayer.seekTo(this.currentTime, true);
            }
            this.progress = percent * 100;
        },

        formatTime(seconds) {
            if (!seconds || isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },
    }));
});
