<?php

// Enqueue scripts and styles for the frontend
function aap_enqueue_scripts() {
    // The cache-busting via filemtime didn't work for the user, so we will now print styles inline.
    // The wp_enqueue_style call has been removed.

    // Enqueue the JavaScript with cache-busting.
    $js_file_path = AAP_PLUGIN_PATH . 'js/playlist.js';
    $js_version = file_exists($js_file_path) ? filemtime($js_file_path) : '1.0';
    wp_enqueue_script(
        'aap-playlist-script',
        AAP_PLUGIN_URL . 'js/playlist.js',
        array( 'jquery' ),
        $js_version,
        true
    );

    // Pass data to the script
    $unique_attributes = aap_get_unique_attributes_for_playlists();
    wp_localize_script( 'aap-playlist-script', 'aap_playlist_data', array(
        'unique_attributes' => $unique_attributes,
    ) );
}
add_action( 'wp_enqueue_scripts', 'aap_enqueue_scripts' );


// Helper function to get all unique attribute values across all playlists
function aap_get_unique_attributes_for_playlists() {
    $attributes = array(
        'style' => [],
        'microphone' => [],
        'effects' => [],
        'amp' => [],
    );

    $audio_samples = get_posts(array(
        'post_type' => 'audio_sample',
        'numberposts' => -1,
    ));

    foreach ($audio_samples as $sample) {
        $style = get_post_meta($sample->ID, '_aap_style', true);
        if ($style && !in_array($style, $attributes['style'])) {
            $attributes['style'][] = $style;
        }

        $microphone = get_post_meta($sample->ID, '_aap_microphone', true);
        if ($microphone && !in_array($microphone, $attributes['microphone'])) {
            $attributes['microphone'][] = $microphone;
        }

        $effects = get_post_meta($sample->ID, '_aap_effects', true);
        if ($effects && !in_array($effects, $attributes['effects'])) {
            $attributes['effects'][] = $effects;
        }

        $amp = get_post_meta($sample->ID, '_aap_amp', true);
        if ($amp && !in_array($amp, $attributes['amp'])) {
            $attributes['amp'][] = $amp;
        }
    }

    return $attributes;
}
