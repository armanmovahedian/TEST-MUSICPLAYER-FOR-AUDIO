jQuery(document).ready(function($) {
    // This function runs for each playlist instance on the page
    $('.aap-playlist-container').each(function() {
        const container = $(this);
        const audioPlayer = container.find('.aap-audio-player')[0];
        const trackList = container.find('.aap-track-list');
        let currentTrackIndex = -1;
        let trackElements = container.find('.aap-track-icon');

        // Player UI elements
        const playPauseBtn = container.find('.aap-play-pause-btn');
        const playIcon = 'dashicons-play-alt';
        const pauseIcon = 'dashicons-pause';
        const prevBtn = container.find('.aap-prev-btn');
        const nextBtn = container.find('.aap-next-btn');
        const progressContainer = container.find('.aap-progress-container');
        const progressBar = container.find('.aap-progress-bar');
        const currentTimeEl = container.find('.aap-current-time');
        const durationEl = container.find('.aap-duration');
        const volumeSlider = container.find('.aap-volume-slider');
        const trackTitleEl = container.find('.aap-track-title');
        const trackAttributesEl = container.find('.aap-track-attributes');

        // --- FILTERING LOGIC ---
        function populateFilters() {
            if (!window.aap_playlist_data || !window.aap_playlist_data.unique_attributes) return;
            const uniqueAttributes = window.aap_playlist_data.unique_attributes;
            for (const attribute in uniqueAttributes) {
                if (uniqueAttributes.hasOwnProperty(attribute)) {
                    const select = container.find('.aap-filter[data-attribute="' + attribute + '"]');
                    const options = uniqueAttributes[attribute];
                    if (select.length && options.length) {
                        options.forEach(function(option) {
                            select.append($('<option>', { value: option, text: option }));
                        });
                    }
                }
            }
        }
        populateFilters();

        container.find('.aap-filter').on('change', function() {
            const filters = {};
            container.find('.aap-filter').each(function() {
                const attribute = $(this).data('attribute');
                const value = $(this).val();
                if (value) {
                    filters[attribute] = value;
                }
            });

            trackElements.each(function() {
                const item = $(this);
                let show = true;
                for (const attribute in filters) {
                    if (filters.hasOwnProperty(attribute)) {
                        if (item.data(attribute) != filters[attribute]) {
                            show = false;
                            break;
                        }
                    }
                }
                item.toggle(show);
            });
            // Update trackElements to only include visible tracks for next/prev functionality
            trackElements = container.find('.aap-track-icon:visible');
        });


        // --- PLAYER LOGIC ---
        function loadTrack(trackIndex) {
            if (trackIndex < 0 || trackIndex >= trackElements.length) return;

            const track = $(trackElements[trackIndex]);

            audioPlayer.src = track.data('audio-src');
            trackTitleEl.text(track.data('title'));

            const attributes = `Style: ${track.data('style')} | Mic: ${track.data('microphone')} | FX: ${track.data('effects')} | Amp: ${track.data('amp')}`;
            trackAttributesEl.text(attributes);

            container.find('.aap-track-icon.active').removeClass('active');
            track.addClass('active');
            currentTrackIndex = trackIndex;
            playTrack();
        }

        function playTrack() {
            audioPlayer.play();
            playPauseBtn.find('.dashicons').removeClass(playIcon).addClass(pauseIcon);
        }

        function pauseTrack() {
            audioPlayer.pause();
            playPauseBtn.find('.dashicons').removeClass(pauseIcon).addClass(playIcon);
        }

        function playPauseToggle() {
            if (audioPlayer.paused) {
                playTrack();
            } else {
                pauseTrack();
            }
        }

        function nextTrack() {
            let newIndex = currentTrackIndex + 1;
            if (newIndex >= trackElements.length) {
                newIndex = 0; // Loop back to the start
            }
            loadTrack(newIndex);
        }

        function prevTrack() {
            let newIndex = currentTrackIndex - 1;
            if (newIndex < 0) {
                newIndex = trackElements.length - 1; // Loop to the end
            }
            loadTrack(newIndex);
        }

        function updateProgress() {
            const { duration, currentTime } = audioPlayer;
            const progressPercent = (currentTime / duration) * 100;
            progressBar.css('width', `${progressPercent}%`);

            // Update time display
            durationEl.text(formatTime(duration));
            currentTimeEl.text(formatTime(currentTime));
        }

        function setProgress(e) {
            const width = progressContainer.width();
            const clickX = e.offsetX;
            const duration = audioPlayer.duration;
            audioPlayer.currentTime = (clickX / width) * duration;
        }

        function formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const minutes = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
        }


        // --- EVENT LISTENERS ---
        trackList.on('click', '.aap-track-icon', function() {
            const clickedIndex = trackElements.index(this);
            loadTrack(clickedIndex);
        });

        playPauseBtn.on('click', playPauseToggle);
        nextBtn.on('click', nextTrack);
        prevBtn.on('click', prevTrack);

        audioPlayer.addEventListener('timeupdate', updateProgress);
        audioPlayer.addEventListener('loadedmetadata', updateProgress);
        audioPlayer.addEventListener('ended', nextTrack);

        progressContainer.on('click', setProgress);
        volumeSlider.on('input', function() {
            audioPlayer.volume = $(this).val();
        });

        // Initialize volume
        audioPlayer.volume = volumeSlider.val();
    });
});
