<div x-data="audioPlayer()" x-on:play-track.window="playTrack($event.detail)"
    class="w-full h-full flex flex-row items-center justify-between" wire:ignore>

    <!-- Left: Song Info -->
    <div class="flex items-center gap-2 md:gap-4 flex-1 md:w-1/3 min-w-0 pr-2">
        <div class="sketchy-border bg-surface w-12 h-12 md:w-16 md:h-16 shrink-0 rotate-[-2deg] flex items-center justify-center overflow-hidden cursor-pointer group"
            @click="expanded = true">
            <template x-if="currentTrack">
                <img :src="currentTrack.thumbnail"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform">
            </template>
            <template x-if="!currentTrack">
                <span
                    class="material-symbols-outlined text-4xl text-outline-variant group-hover:text-primary transition-colors">music_note</span>
            </template>
        </div>
        <div class="min-w-0 flex-1 cursor-pointer group" @click="expanded = true">
            <template x-if="currentTrack">
                <div>
                    <h4 class="font-headline-md text-[16px] md:text-[18px] text-on-surface truncate group-hover:underline"
                        x-text="currentTrack.title"></h4>
                    <p class="font-label-sm text-[12px] md:text-[14px] text-on-surface-variant truncate" x-text="currentTrack.artist"></p>
                </div>
            </template>
            <template x-if="!currentTrack">
                <div>
                    <h4 class="font-headline-md text-[16px] md:text-[18px] text-on-surface truncate">Select a song</h4>
                    <p class="font-label-sm text-[12px] md:text-[14px] text-on-surface-variant truncate">To start playing</p>
                </div>
            </template>
        </div>
        <button @click="saveTrack" :title="isSaved ? 'Hapus dari Favorite' : 'Simpan ke Favorite'"
            class="material-symbols-outlined hover:text-primary transition-colors ml-2 hidden md:block"
            :class="isSaved ? 'text-primary' : 'text-outline-variant'"
            :style="isSaved ? 'font-variation-settings: \'FILL\' 1;' : 'font-variation-settings: \'FILL\' 0;'">favorite</button>
    </div>

    <!-- Center: Controls -->
    <div class="flex flex-row md:flex-col items-center justify-end md:justify-center gap-2 md:gap-1 w-auto md:w-1/3 max-w-xl">
        <div class="flex items-center gap-2 md:gap-6">
            <button @click="toggleShuffle" :title="isShuffle ? 'Matikan Acak' : 'Nyalakan Acak'"
                class="material-symbols-outlined hover:text-primary active:scale-95 transition-all text-xl hidden md:block"
                :class="isShuffle ? 'text-primary' : 'text-on-surface-variant'">shuffle</button>
            <button @click="prevTrack" title="Previous"
                class="material-symbols-outlined text-on-surface hover:text-primary active:scale-95 transition-all text-3xl hidden md:block"
                :class="{'opacity-50 cursor-not-allowed': currentIndex <= 0 && repeatMode !== 1}"
                :disabled="currentIndex <= 0 && repeatMode !== 1">skip_previous</button>

            <button @click="togglePlay" :title="isPlaying ? 'Pause' : 'Play'"
                class="sketchy-border w-10 h-10 md:w-12 md:h-12 rounded-full bg-primary-container hover:bg-inverse-primary flex items-center justify-center shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] active:shadow-none active:translate-y-[2px] active:translate-x-[2px] transition-all group">
                <template x-if="!isPlaying">
                    <span
                        class="material-symbols-outlined text-on-background text-2xl md:text-3xl group-hover:scale-110 transition-transform"
                        style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                </template>
                <template x-if="isPlaying">
                    <span
                        class="material-symbols-outlined text-on-background text-2xl md:text-3xl group-hover:scale-110 transition-transform"
                        style="font-variation-settings: 'FILL' 1;">pause</span>
                </template>
            </button>

            <button @click="nextTrack" title="Next"
                class="material-symbols-outlined text-on-surface hover:text-primary active:scale-95 transition-all text-2xl md:text-3xl"
                :class="{'opacity-50 cursor-not-allowed': (currentIndex >= queue.length - 1 || queue.length === 0) && repeatMode !== 1}"
                :disabled="(currentIndex >= queue.length - 1 || queue.length === 0) && repeatMode !== 1">skip_next</button>
            <button @click="toggleRepeat"
                :title="repeatMode === 0 ? 'Nyalakan Ulang' : (repeatMode === 1 ? 'Ulangi Satu Lagu' : 'Matikan Ulang')"
                class="material-symbols-outlined hover:text-primary active:scale-95 transition-all text-xl hidden md:block"
                :class="repeatMode > 0 ? 'text-primary' : 'text-on-surface-variant'"
                x-text="repeatMode === 2 ? 'repeat_one' : 'repeat'">repeat</button>
        </div>
        <div class="hidden md:flex items-center gap-3 w-full">
            <span class="font-label-sm text-[10px] text-on-surface-variant w-8 text-right"
                x-text="formatTime(currentTime)">0:00</span>
            <div class="h-2 flex-grow sketchy-border bg-surface-container-highest rounded-full cursor-pointer group relative"
                @click="seek" @mousemove="hoverProgress($event)" @mouseleave="hoverTime = ''" :title="hoverTime">
                <div class="h-full bg-primary-container border-r-2 border-on-background relative rounded-l-full transition-all"
                    :style="'width: ' + progress + '%'"></div>
            </div>
            <span class="font-label-sm text-[10px] text-on-surface-variant w-8"
                x-text="formatTime(duration)">0:00</span>
        </div>
    </div>

    <!-- Right: Extra Controls -->
    <div class="hidden md:flex items-center justify-end gap-4 w-1/3 min-w-0">
        <div class="flex items-center gap-2 w-32 group hidden sm:flex">
            <button
                class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors text-xl">volume_up</button>
            <div class="h-2 flex-grow sketchy-border bg-surface-container-highest rounded-full overflow-hidden cursor-pointer"
                @click="setVolume">
                <div class="h-full bg-primary-container border-r-2 border-on-background transition-all"
                    :style="'width: ' + (volume * 100) + '%'"></div>
            </div>
        </div>
        <button @click="expanded = !expanded" title="Expand View"
            class="material-symbols-outlined text-on-surface hover:text-primary active:scale-95 transition-all rotate-[90deg]">open_in_full</button>
    </div>

    <!-- Mobile Thin Progress Bar -->
    <div class="absolute bottom-0 left-0 right-0 h-1 bg-surface-container-highest md:hidden" @click="seek">
        <div class="h-full bg-primary transition-all" :style="'width: ' + progress + '%'"></div>
    </div>

    <!-- Maximized Overlay (Watch View) -->
    <template x-teleport="body">
        <div x-show="expanded" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-full" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            class="fixed inset-0 z-[100] bg-surface flex flex-col md:flex-row overflow-y-auto md:overflow-hidden" style="display: none;">

            <button @click="expanded = false"
                class="absolute top-6 left-6 p-2 rounded-full bg-surface border-[2px] border-text-main shadow-[2px_2px_0px_#111827] hover:-translate-y-1 hover:shadow-[4px_4px_0px_#49B6E5] text-text-main z-[110] transition-all">
                <span class="material-symbols-outlined">expand_more</span>
            </button>

            <!-- Left Column: Album Art & Controls -->
            <div
                class="w-full md:w-2/3 min-h-[100dvh] md:min-h-0 md:h-full shrink-0 flex flex-col items-center justify-center p-8 border-b-2 md:border-b-0 md:border-r-2 border-on-background border-dashed gap-8 relative">
                <template x-if="currentTrack">
                    <div class="flex flex-col items-center w-full max-w-md">
                        <!-- Album Art -->
                        <div
                            class="sketchy-border w-full aspect-square bg-surface shadow-[8px_8px_0px_0px_rgba(28,27,27,1)] overflow-hidden relative rotate-[-1deg] group mb-6">
                            <img :src="currentTrack.thumbnail"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <!-- Subtle gradient overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-background/50 to-transparent"></div>
                        </div>

                        <!-- Track Info & Favorite -->
                        <div class="flex items-center justify-between w-full mb-8">
                            <div class="min-w-0 flex-1">
                                <h2 class="font-headline-xl text-[28px] leading-tight text-on-surface truncate group-hover:underline" x-text="currentTrack.title"></h2>
                                <p class="font-label-sm text-[16px] text-on-surface-variant truncate mt-1" x-text="currentTrack.artist"></p>
                            </div>
                            <button @click="saveTrack" :title="isSaved ? 'Hapus dari Favorite' : 'Simpan ke Favorite'"
                                class="material-symbols-outlined hover:text-primary transition-colors ml-4 text-4xl"
                                :class="isSaved ? 'text-primary' : 'text-outline-variant'"
                                :style="isSaved ? 'font-variation-settings: \'FILL\' 1;' : 'font-variation-settings: \'FILL\' 0;'">favorite</button>
                        </div>

                        <!-- Progress Bar -->
                        <div class="flex items-center gap-3 w-full mb-8">
                            <span class="font-label-sm text-[12px] text-on-surface-variant w-10 text-right"
                                x-text="formatTime(currentTime)">0:00</span>
                            <div class="h-3 flex-grow sketchy-border bg-surface-container-highest rounded-full cursor-pointer group relative"
                                @click="seek" @mousemove="hoverProgress($event)" @mouseleave="hoverTime = ''" :title="hoverTime">
                                <div class="h-full bg-primary-container border-r-2 border-on-background relative rounded-l-full transition-all"
                                    :style="'width: ' + progress + '%'"></div>
                            </div>
                            <span class="font-label-sm text-[12px] text-on-surface-variant w-10"
                                x-text="formatTime(duration)">0:00</span>
                        </div>

                        <!-- Playback Controls -->
                        <div class="flex items-center justify-center gap-8 w-full">
                            <button @click="toggleShuffle" :title="isShuffle ? 'Matikan Acak' : 'Nyalakan Acak'"
                                class="material-symbols-outlined hover:text-primary active:scale-95 transition-all text-3xl"
                                :class="isShuffle ? 'text-primary' : 'text-on-surface-variant'">shuffle</button>
                            <button @click="prevTrack" title="Previous"
                                class="material-symbols-outlined text-on-surface hover:text-primary active:scale-95 transition-all text-5xl"
                                :class="{'opacity-50 cursor-not-allowed': currentIndex <= 0 && repeatMode !== 1}"
                                :disabled="currentIndex <= 0 && repeatMode !== 1">skip_previous</button>

                            <button @click="togglePlay" :title="isPlaying ? 'Pause' : 'Play'"
                                class="sketchy-border w-20 h-20 rounded-full bg-primary-container hover:bg-inverse-primary flex items-center justify-center shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] active:shadow-none active:translate-y-[4px] active:translate-x-[4px] transition-all group">
                                <template x-if="!isPlaying">
                                    <span class="material-symbols-outlined text-on-background text-5xl group-hover:scale-110 transition-transform"
                                        style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                                </template>
                                <template x-if="isPlaying">
                                    <span class="material-symbols-outlined text-on-background text-5xl group-hover:scale-110 transition-transform"
                                        style="font-variation-settings: 'FILL' 1;">pause</span>
                                </template>
                            </button>

                            <button @click="nextTrack" title="Next"
                                class="material-symbols-outlined text-on-surface hover:text-primary active:scale-95 transition-all text-5xl"
                                :class="{'opacity-50 cursor-not-allowed': (currentIndex >= queue.length - 1 || queue.length === 0) && repeatMode !== 1}"
                                :disabled="(currentIndex >= queue.length - 1 || queue.length === 0) && repeatMode !== 1">skip_next</button>
                            <button @click="toggleRepeat"
                                :title="repeatMode === 0 ? 'Nyalakan Ulang' : (repeatMode === 1 ? 'Ulangi Satu Lagu' : 'Matikan Ulang')"
                                class="material-symbols-outlined hover:text-primary active:scale-95 transition-all text-3xl"
                                :class="repeatMode > 0 ? 'text-primary' : 'text-on-surface-variant'"
                                x-text="repeatMode === 2 ? 'repeat_one' : 'repeat'">repeat</button>
                        </div>
                    </div>
                </template>
                <!-- Scroll Indicator for mobile -->
                <div class="mt-4 md:hidden animate-bounce text-on-surface-variant flex flex-col items-center">
                    <span class="text-[10px] font-label-sm uppercase tracking-widest">Up Next</span>
                    <span class="material-symbols-outlined text-xl">keyboard_arrow_down</span>
                </div>
            </div>

            <!-- Right Column: Up Next Queue -->
            <div class="w-full md:w-1/3 min-h-[100dvh] md:min-h-0 md:h-full shrink-0 flex flex-col bg-surface">
                <div class="p-6 border-b-2 border-on-background border-dashed bg-secondary-container/20 flex flex-col">
                    <template x-if="queueContext && queueContext.type !== 'Track'">
                        <div>
                            <h3 class="font-headline-md text-[20px] text-on-surface truncate"
                                x-text="queueContext.title"></h3>
                            <p class="font-label-sm text-on-surface-variant mt-1">
                                <span x-text="queue.length + ' songs • '"></span>
                                <span
                                    x-text="queueContext.type + (queueContext.author ? ' • ' + queueContext.author : '')"></span>
                            </p>
                        </div>
                    </template>
                    <template x-if="!queueContext || queueContext.type === 'Track'">
                        <h3 class="font-headline-md text-[24px] text-on-surface">Up Next</h3>
                    </template>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar">
                    <template x-for="(track, index) in queue" :key="index">
                        <div class="flex items-center p-3 rounded-xl cursor-pointer transition-all sketchy-border hover:bg-secondary-container/30 hover:rotate-[-1deg] group"
                            :class="{'bg-primary-container/20 border-on-background shadow-[4px_4px_0px_0px_rgba(28,27,27,1)]': currentIndex === index, 'bg-surface': currentIndex !== index}"
                            @click="currentIndex = index; playTrack(queue[currentIndex])">
                            <div
                                class="relative h-14 w-14 shrink-0 rounded-lg overflow-hidden border-2 border-on-background">
                                <img :src="track.thumbnail"
                                    class="object-cover w-full h-full group-hover:scale-110 transition duration-300">
                                <template x-if="currentIndex === index">
                                    <div
                                        class="absolute inset-0 bg-primary/40 flex items-center justify-center backdrop-blur-[2px]">
                                        <div class="text-white flex items-end justify-center h-4 gap-[2px]">
                                            <!-- Equalizer animation -->
                                            <div class="w-1 h-2 bg-surface animate-[bounce_1s_infinite]"></div>
                                            <div class="w-1 h-4 bg-surface animate-[bounce_1.2s_infinite]"></div>
                                            <div class="w-1 h-3 bg-surface animate-[bounce_0.8s_infinite]"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="ml-4 flex-1 min-w-0">
                                <h4 class="font-headline-md text-[16px] text-on-surface truncate group-hover:underline"
                                    x-text="track.title"></h4>
                                <p class="font-label-sm text-on-surface-variant truncate" x-text="track.artist"></p>
                            </div>
                            <button @click.prevent.stop="@auth track.isSaved = !track.isSaved; $dispatch('saveTrackToLibrary', { track: track }) @else showLoginModal = true; @endauth"
                                :title="track.isSaved ? 'Hapus dari Favorite' : 'Simpan ke Favorite'"
                                class="material-symbols-outlined hover:text-primary transition-colors ml-3 text-2xl"
                                :class="track.isSaved ? 'text-primary' : 'text-outline-variant'"
                                :style="`font-variation-settings: 'FILL' ${track.isSaved ? 1 : 0};`">favorite</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <!-- Hidden HTML5 Audio Element -->
    <audio x-ref="audioEl" @timeupdate="updateProgress" @ended="trackEnded" @loadedmetadata="setDuration"
        @play="isPlaying = true" @pause="isPlaying = false"></audio>

    <script>
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
                volume: 1,
                isSaved: false,
                hoverTime: '',
                isShuffle: false,
                repeatMode: 0, // 0: off, 1: all, 2: one

                init() {
                    this.$refs.audioEl.volume = this.volume;
                    window.addEventListener('play-queue', (e) => {
                        this.playQueue(e.detail);
                    });
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
                    const trackId = track.id || track.videoId;

                    @this.call('recordHistory', trackId, track.title, track.artist, track.thumbnail);
                    this.isSaved = await this.$wire.checkIsSaved(trackId);

                    if (this.mockInterval) clearInterval(this.mockInterval);

                    try {
                        const response = await fetch(`/api/track/${trackId}`);
                        const data = await response.json();

                        if (data && data.stream_url) {
                            let finalStreamUrl = data.stream_url;
                            @if(env('APP_ENV') === 'production')
                                finalStreamUrl = '/stream/' + trackId;
                            @endif
                            
                            this.$refs.audioEl.src = finalStreamUrl;
                            this.$refs.audioEl.play();
                            this.isPlaying = true;
                        } else {
                            console.error('No stream URL returned');
                            this.mockPlay();
                        }
                    } catch (e) {
                        console.error(e);
                        this.mockPlay();
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

                togglePlay() {
                    if (!this.currentTrack || !this.$refs.audioEl.src) return;
                    if (this.$refs.audioEl.paused) {
                        this.$refs.audioEl.play();
                    } else {
                        this.$refs.audioEl.pause();
                    }
                },

                updateProgress() {
                    if (!this.$refs.audioEl.duration) return;
                    this.currentTime = this.$refs.audioEl.currentTime;
                    this.duration = this.$refs.audioEl.duration;
                    this.progress = (this.currentTime / this.duration) * 100;
                },

                setDuration() {
                    this.duration = this.$refs.audioEl.duration;
                },

                seek(e) {
                    if (!this.currentTrack) return;
                    const rect = e.currentTarget.getBoundingClientRect();
                    const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
                    this.currentTime = this.duration * percent;
                    this.progress = percent * 100;
                    if (this.$refs.audioEl.src) {
                        this.$refs.audioEl.currentTime = this.currentTime;
                    }
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
                    this.volume = percent;
                    this.$refs.audioEl.volume = percent;
                },

                formatTime(seconds) {
                    if (!seconds || isNaN(seconds)) return '0:00';
                    const mins = Math.floor(seconds / 60);
                    const secs = Math.floor(seconds % 60);
                    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
                },

                trackEnded() {
                    this.isPlaying = false;
                    this.progress = 0;
                    this.currentTime = 0;

                    if (this.repeatMode === 2) { // repeat one
                        this.playTrack(this.queue[this.currentIndex]);
                        return;
                    }

                    if (this.queue.length > 0) {
                        if (this.currentIndex < this.queue.length - 1) {
                            this.nextTrack();
                        } else if (this.repeatMode === 1) { // repeat all
                            this.nextTrack(); // nextTrack already handles looping
                        }
                    }
                },

                saveTrack() {
                    if (!this.currentTrack) return;
                    @auth
                        this.isSaved = !this.isSaved;
                        this.$dispatch('saveTrackToLibrary', { track: this.currentTrack });
                    @else
                        showLoginModal = true;
                    @endauth
                }
            }));
        });
    </script>
</div>