<?php

// Register the shortcode
function aap_register_shortcode() {
    add_shortcode( 'audio_playlist', 'aap_shortcode_callback' );
}
add_action( 'init', 'aap_register_shortcode' );

// The shortcode callback function
function aap_shortcode_callback( $atts ) {
    // Extract shortcode attributes
    $atts = shortcode_atts( array(
        'id' => '', // Playlist ID
    ), $atts, 'audio_playlist' );

    if ( empty( $atts['id'] ) ) {
        return '<p>Error: No playlist ID provided.</p>';
    }

    $playlist_id = intval( $atts['id'] );
    $audio_ids = get_post_meta( $playlist_id, '_aap_playlist_audio_ids', true );

    if ( empty( $audio_ids ) ) {
        return '<p>This playlist is empty.</p>';
    }

    // Query the audio samples
    $args = array(
        'post_type' => 'audio_sample',
        'post__in' => $audio_ids,
        'orderby' => 'post__in',
    );
    $query = new WP_Query( $args );

    // Start output buffering
    ob_start();

    if ( $query->have_posts() ) {
        ?>
        <div id="aap-playlist-container-<?php echo esc_attr($playlist_id); ?>" class="aap-playlist-container">

            <!-- Filters -->
            <div class="aap-filters">
                <select class="aap-filter" data-attribute="style"><option value="">All Styles</option></select>
                <select class="aap-filter" data-attribute="microphone"><option value="">All Microphones</option></select>
                <select class="aap-filter" data-attribute="effects"><option value="">All Effects</option></select>
                <select class="aap-filter" data-attribute="amp"><option value="">All Amps</option></select>
            </div>

            <!-- Main Player and Track List Wrapper -->
            <div class="aap-player-wrapper">

                <!-- Track Selection Grid -->
                <div class="aap-track-list">
                    <?php $track_number = 1; ?>
                    <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                        <?php
                            $post_id = get_the_ID();
                            $style = get_post_meta( $post_id, '_aap_style', true );
                            $microphone = get_post_meta( $post_id, '_aap_microphone', true );
                            $effects = get_post_meta( $post_id, '_aap_effects', true );
                            $amp = get_post_meta( $post_id, '_aap_amp', true );
                            $audio_url = home_url( '/?serve_audio=' . $post_id );
                        ?>
                        <div class="aap-track-icon"
                             data-track-id="<?php echo esc_attr($post_id); ?>"
                             data-audio-src="<?php echo esc_url($audio_url); ?>"
                             data-title="<?php the_title_attribute(); ?>"
                             data-style="<?php echo esc_attr($style); ?>"
                             data-microphone="<?php echo esc_attr($microphone); ?>"
                             data-effects="<?php echo esc_attr($effects); ?>"
                             data-amp="<?php echo esc_attr($amp); ?>">
                            <span><?php echo $track_number++; ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Main Player UI -->
                <div class="aap-player">
                    <div class="aap-player-body">
                        <div class="aap-track-info">
                            <h4 class="aap-track-title">Select a track</h4>
                            <div class="aap-track-attributes"></div>
                        </div>

                        <div class="aap-progress-container">
                            <div class="aap-progress-bar"></div>
                            <span class="aap-current-time">0:00</span>
                            <span class="aap-duration">0:00</span>
                        </div>

                        <div class="aap-controls">
                            <button class="aap-prev-btn"><i class="dashicons dashicons-skip-previous"></i></button>
                            <button class="aap-play-pause-btn"><i class="dashicons dashicons-play-alt"></i></button>
                            <button class="aap-next-btn"><i class="dashicons dashicons-skip-next"></i></button>
                        </div>

                        <div class="aap-volume-container">
                             <i class="dashicons dashicons-volume-up"></i>
                             <input type="range" class="aap-volume-slider" min="0" max="1" step="0.05" value="1">
                        </div>
                    </div>
                </div>

            </div>

            <!-- The actual audio element, hidden -->
            <audio id="aap-audio-player-<?php echo esc_attr($playlist_id); ?>" class="aap-audio-player"></audio>

        </div>
        <?php
    } else {
        echo '<p>No audio samples found in this playlist.</p>';
    }

    wp_reset_postdata();

    // Return the buffered content
    return ob_get_clean();
}
