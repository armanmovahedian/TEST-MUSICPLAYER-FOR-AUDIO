<?php

// Register the shortcode
function aap_register_shortcode() {
    add_shortcode( 'audio_playlist', 'aap_shortcode_callback' );
}
add_action( 'init', 'aap_register_shortcode' );

// The shortcode callback function
function aap_shortcode_callback( $atts ) {
    // Extract shortcode attributes
    $atts = shortcode_atts( array('id' => ''), $atts, 'audio_playlist' );
    if ( empty( $atts['id'] ) ) return '<p>Error: No playlist ID provided.</p>';

    $playlist_id = intval( $atts['id'] );
    $audio_ids = get_post_meta( $playlist_id, '_aap_playlist_audio_ids', true );
    if ( empty( $audio_ids ) ) return '<p>This playlist is empty.</p>';

    // Query the audio samples
    $query = new WP_Query( array('post_type' => 'audio_sample', 'post__in' => $audio_ids, 'orderby' => 'post__in') );

    // --- INLINE STYLES DEFINITION ---
    $brand_color = '#2a5b2c';
    $dark_green_bg = '#1a2b28';
    $red_active = '#e74c3c';
    $white_text = '#FFFFFF';
    $black_text = '#000000';
    $light_grey_text = '#bdc3c7';

    $styles = [
        'container' => 'font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f5; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0;',
        'filters' => 'display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;',
        'select' => "padding: 10px; border: 1px solid #ccc; border-radius: 6px; background-color: {$white_text}; flex-grow: 1; min-width: 150px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); -webkit-appearance: none; -moz-appearance: none; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%208l5%205%205-5z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 1em; padding-right: 2.5em;",
        'player_wrapper' => 'display: flex; flex-direction: column; gap: 20px;',
        'track_list' => 'display: flex; flex-wrap: wrap; gap: 10px; padding: 10px; background: #fff; border-radius: 8px; border: 1px solid #e0e0e0;',
        'track_icon' => "width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background-color: {$brand_color}; color: {$white_text}; border: 1px solid #3a7d3c; border-radius: 50%; cursor: pointer; font-weight: bold; transition: all 0.3s ease;",
        'player' => "display: flex; gap: 20px; padding: 20px; background-color: {$dark_green_bg}; color: #ecf0f1; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);",
        'player_body' => 'flex-grow: 1; display: flex; flex-direction: column; justify-content: center;',
        'track_title' => 'margin: 0 0 5px; font-size: 1.2em; font-weight: bold;',
        'track_attributes' => "margin: 10px 0; font-size: 0.9em; color: {$light_grey_text}; opacity: 0.8; display: grid; grid-template-columns: 1fr 1fr; gap: 5px 15px;",
        'attr_item' => 'display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;',
        'progress_container' => 'position: relative; width: 100%; height: 5px; background-color: #203d36; border-radius: 5px; cursor: pointer; margin: 15px 0;',
        'progress_bar' => "width: 0; height: 100%; background-color: {$brand_color}; border-radius: 5px;",
        'time' => "position: absolute; top: 10px; font-size: 0.8em; color: {$light_grey_text};",
        'controls' => 'display: flex; align-items: center; justify-content: center; gap: 20px;',
        'control_btn' => "background: none; border: none; color: {$white_text}; cursor: pointer; font-size: 24px; padding: 5px;",
        'play_pause_btn' => "font-size: 32px; background-color: {$white_text}; color: {$black_text}; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;",
        'volume_container' => 'display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 10px;',
        'volume_slider' => " -webkit-appearance: none; width: 100px; height: 4px; background: {$white_text}; outline: none; border-radius: 2px;",
    ];

    ob_start();
    if ( $query->have_posts() ) : ?>
        <div id="aap-playlist-container-<?php echo esc_attr($playlist_id); ?>" class="aap-playlist-container" style="<?php echo $styles['container']; ?>">
            <div class="aap-filters" style="<?php echo $styles['filters']; ?>">
                <select class="aap-filter" data-attribute="style" style="<?php echo $styles['select']; ?>"><option value="">All Styles</option></select>
                <select class="aap-filter" data-attribute="microphone" style="<?php echo $styles['select']; ?>"><option value="">All Microphones</option></select>
                <select class="aap-filter" data-attribute="effects" style="<?php echo $styles['select']; ?>"><option value="">All Effects</option></select>
                <select class="aap-filter" data-attribute="amp" style="<?php echo $styles['select']; ?>"><option value="">All Amps</option></select>
            </div>
            <div class="aap-player-wrapper" style="<?php echo $styles['player_wrapper']; ?>">
                <div class="aap-track-list" style="<?php echo $styles['track_list']; ?>">
                    <?php $track_number = 1; while ( $query->have_posts() ) : $query->the_post(); ?>
                        <?php
                            $post_id = get_the_ID();
                            $audio_url = home_url( '/?serve_audio=' . $post_id );
                        ?>
                        <div class="aap-track-icon" style="<?php echo $styles['track_icon']; ?>"
                             data-track-id="<?php echo esc_attr($post_id); ?>"
                             data-audio-src="<?php echo esc_url($audio_url); ?>"
                             data-title="<?php the_title_attribute(); ?>"
                             data-style="<?php echo esc_attr(get_post_meta( $post_id, '_aap_style', true )); ?>"
                             data-microphone="<?php echo esc_attr(get_post_meta( $post_id, '_aap_microphone', true )); ?>"
                             data-effects="<?php echo esc_attr(get_post_meta( $post_id, '_aap_effects', true )); ?>"
                             data-amp="<?php echo esc_attr(get_post_meta( $post_id, '_aap_amp', true )); ?>"
                             data-style-default="<?php echo $styles['track_icon']; ?>"
                             data-style-active="<?php echo str_replace($brand_color, $red_active, $styles['track_icon']); ?>">
                            <span><?php echo $track_number++; ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="aap-player" style="<?php echo $styles['player']; ?>">
                    <div class="aap-player-body" style="<?php echo $styles['player_body']; ?>">
                        <div class="aap-track-info">
                            <h4 class="aap-track-title" style="<?php echo $styles['track_title']; ?>">Select a track</h4>
                            <div class="aap-track-attributes" style="<?php echo $styles['track_attributes']; ?>"></div>
                        </div>
                        <div class="aap-progress-container" style="<?php echo $styles['progress_container']; ?>">
                            <div class="aap-progress-bar" style="<?php echo $styles['progress_bar']; ?>"></div>
                            <span class="aap-current-time" style="<?php echo $styles['time']; ?> left: 0;">0:00</span>
                            <span class="aap-duration" style="<?php echo $styles['time']; ?> right: 0;">0:00</span>
                        </div>
                        <div class="aap-controls" style="<?php echo $styles['controls']; ?>">
                            <button class="aap-prev-btn" style="<?php echo $styles['control_btn']; ?>"><i class="dashicons dashicons-skip-previous"></i></button>
                            <button class="aap-play-pause-btn" style="<?php echo $styles['play_pause_btn']; ?>"><i class="dashicons dashicons-play-alt"></i></button>
                            <button class="aap-next-btn" style="<?php echo $styles['control_btn']; ?>"><i class="dashicons dashicons-skip-next"></i></button>
                        </div>
                        <div class="aap-volume-container" style="<?php echo $styles['volume_container']; ?>">
                             <i class="dashicons dashicons-volume-up"></i>
                             <input type="range" class="aap-volume-slider" style="<?php echo $styles['volume_slider']; ?>" min="0" max="1" step="0.05" value="1">
                        </div>
                    </div>
                </div>
            </div>
            <audio id="aap-audio-player-<?php echo esc_attr($playlist_id); ?>" class="aap-audio-player"></audio>
        </div>
    <?php else : ?>
        <p>No audio samples found in this playlist.</p>
    <?php endif;
    wp_reset_postdata();
    return ob_get_clean();
}
