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

        init() {
            this.initPlayer();
            window.addEventListener('play-queue', (e) => {
                this.playQueue(e.detail);
            });
        },

        initPlayer() {
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
            if (window.ytPlayer && typeof window.ytPlayer.loadVideoById === 'function') {
                this.ytPlayerReady = true;
                if (this.pendingTrackId) {
                    window.ytPlayer.loadVideoById(this.pendingTrackId);
                    this.pendingTrackId = null;
                }
                return;
            }
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
                    'onReady': (e) => this.onPlayerReady(e),
                    'onStateChange': (e) => this.onPlayerStateChange(e),
                    'onError': (e) => this.onPlayerError(e)
                }
            });
        },

        onPlayerError(event) {
            console.error('[YT] Player Error:', event.data);
            this.mockPlay();
        },

        onPlayerReady(event) {
            this.ytPlayerReady = true;
            window.ytPlayer.setVolume(this.volume);
            console.log('[YT] Player siap!');
            if (this.pendingTrackId) {
                window.ytPlayer.loadVideoById(this.pendingTrackId);
                this.pendingTrackId = null;
            }
        },

        onPlayerStateChange(event) {
            if (event.data == YT.PlayerState.PLAYING) {
                this.isPlaying = true;
                this.duration = window.ytPlayer.getDuration();
                if (this.ytInterval) clearInterval(this.ytInterval);
                this.ytInterval = setInterval(() => {
                    if (this.isPlaying && window.ytPlayer && window.ytPlayer.getCurrentTime) {
                        this.currentTime = window.ytPlayer.getCurrentTime();
                        this.progress = (this.currentTime / this.duration) * 100;
                    }
                }, 1000);
            } else if (event.data == YT.PlayerState.PAUSED) {
                this.isPlaying = false;
                if (this.ytInterval) clearInterval(this.ytInterval);
            } else if (event.data == YT.PlayerState.ENDED) {
                this.isPlaying = false;
                if (this.ytInterval) clearInterval(this.ytInterval);
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

            if (this.ytInterval) clearInterval(this.ytInterval);
            if (this.mockInterval) clearInterval(this.mockInterval);

            // Resolve YouTube Music internal IDs (lp-, lm- prefix) ke video ID biasa
            let trackId = rawId;
            if (rawId && (rawId.startsWith('lp-') || rawId.startsWith('lm-'))) {
                try {
                    const res = await fetch(`/api/music/resolve/${rawId}`);
                    if (res.ok) {
                        const data = await res.json();
                        trackId = data.videoId || rawId;
                        console.log('[YT] Resolved', rawId, '->', trackId);
                    }
                } catch (e) {
                    // Fallback: strip prefix
                    trackId = rawId.substring(3);
                    console.warn('[YT] Resolve gagal, pakai fallback:', trackId);
                }
            }

            console.log('[YT] Mencoba memutar trackId:', trackId);
            console.log('[YT] ytPlayerReady:', this.ytPlayerReady, '| window.ytPlayer:', window.ytPlayer);

            if (this.ytPlayerReady && window.ytPlayer && typeof window.ytPlayer.loadVideoById === 'function') {
                window.ytPlayer.loadVideoById(trackId);
            } else {
                this.pendingTrackId = trackId;
                console.log('[YT] Player belum siap, disimpan sebagai pending:', trackId);

                let retries = 0;
                const retryInterval = setInterval(() => {
                    retries++;
                    if (window.ytPlayer && typeof window.ytPlayer.loadVideoById === 'function') {
                        console.log('[YT] Player siap setelah', retries, 'percobaan, memutar...');
                        window.ytPlayer.loadVideoById(this.pendingTrackId || trackId);
                        this.pendingTrackId = null;
                        clearInterval(retryInterval);
                    } else if (retries >= 15) {
                        console.error('[YT] Player gagal dimuat, fallback ke mockPlay');
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
            if (!this.currentTrack || !window.ytPlayer) return;
            if (this.isPlaying) {
                window.ytPlayer.pauseVideo();
            } else {
                window.ytPlayer.playVideo();
            }
        },

        mockPlay() {
            this.isPlaying = true;
            this.duration = 180;
            this.currentTime = 0;
            this.progress = 0;
            this.mockInterval = setInterval(() => {
                if (this.isPlaying) {
                    this.currentTime += 1;
                    this.progress = (this.currentTime / this.duration) * 100;
                    if (this.currentTime >= this.duration) {
                        this.isPlaying = false;
                        clearInterval(this.mockInterval);
                        this.trackEnded();
                    }
                }
            }, 1000);
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
