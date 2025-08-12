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
            <div class="aap-filters">
                <!-- Dropdown filters will go here -->
                <select class="aap-filter" data-attribute="style">
                    <option value="">All Styles</option>
                </select>
                <select class="aap-filter" data-attribute="microphone">
                    <option value="">All Microphones</option>
                </select>
                <select class="aap-filter" data-attribute="effects">
                    <option value="">All Effects</option>
                </select>
                <select class="aap-filter" data-attribute="amp">
                    <option value="">All Amps</option>
                </select>
            </div>
            <div class="aap-playlist">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php
                        $post_id = get_the_ID();
                        $style = get_post_meta( $post_id, '_aap_style', true );
                        $microphone = get_post_meta( $post_id, '_aap_microphone', true );
                        $effects = get_post_meta( $post_id, '_aap_effects', true );
                        $amp = get_post_meta( $post_id, '_aap_amp', true );
                        $audio_url = get_post_meta( $post_id, '_aap_audio_file', true );
                    ?>
                    <div class="aap-playlist-item"
                         data-style="<?php echo esc_attr($style); ?>"
                         data-microphone="<?php echo esc_attr($microphone); ?>"
                         data-effects="<?php echo esc_attr($effects); ?>"
                         data-amp="<?php echo esc_attr($amp); ?>">

                        <h4 class="aap-audio-title"><?php the_title(); ?></h4>
                        <audio controls src="<?php echo esc_url( home_url( '/?serve_audio=' . $post_id ) ); ?>"></audio>

                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
    } else {
        echo '<p>No audio samples found in this playlist.</p>';
    }

    wp_reset_postdata();

    // Return the buffered content
    return ob_get_clean();
}
