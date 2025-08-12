<?php

// Register Custom Post Type for Playlists
function aap_register_playlist_post_type() {
    $labels = array(
        'name'                  => _x( 'Playlists', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'Playlist', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'Playlists', 'text_domain' ),
        'name_admin_bar'        => __( 'Playlist', 'text_domain' ),
        'all_items'             => __( 'All Playlists', 'text_domain' ),
        'add_new_item'          => __( 'Add New Playlist', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Playlist', 'text_domain' ),
        'edit_item'             => __( 'Edit Playlist', 'text_domain' ),
        'update_item'           => __( 'Update Playlist', 'text_domain' ),
        'view_item'             => __( 'View Playlist', 'text_domain' ),
        'search_items'          => __( 'Search Playlist', 'text_domain' ),
    );
    $args = array(
        'label'                 => __( 'Playlist', 'text_domain' ),
        'description'           => __( 'A post type for audio playlists.', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array( 'title' ),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => 'edit.php?post_type=audio_sample',
        'can_export'            => true,
        'capability_type'       => 'post',
    );
    register_post_type( 'aap_playlist', $args );
}
add_action( 'init', 'aap_register_playlist_post_type', 0 );

// Add meta boxes to the playlist edit screen
function aap_add_playlist_meta_boxes() {
    add_meta_box(
        'aap_playlist_audio_samples',
        __( 'Audio Samples', 'text_domain' ),
        'aap_playlist_audio_samples_callback',
        'aap_playlist',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'aap_add_playlist_meta_boxes' );

// The callback function to display the fields in the meta box
function aap_playlist_audio_samples_callback( $post ) {
    wp_nonce_field( 'aap_save_playlist_meta_box_data', 'aap_playlist_meta_box_nonce' );

    $audio_ids = get_post_meta( $post->ID, '_aap_playlist_audio_ids', true );
    if ( ! is_array( $audio_ids ) ) {
        $audio_ids = [];
    }

    $all_audio_samples = get_posts( array(
        'post_type' => 'audio_sample',
        'numberposts' => -1,
        'post_status' => 'publish',
    ) );

    if ( empty($all_audio_samples) ) {
        echo '<p>No audio samples found. Please add some first.</p>';
        return;
    }

    echo '<h4>Select audio samples for this playlist:</h4>';
    echo '<ul>';
    foreach ( $all_audio_samples as $audio ) {
        $checked = in_array( $audio->ID, $audio_ids ) ? 'checked' : '';
        echo '<li>';
        echo '<label>';
        echo '<input type="checkbox" name="aap_audio_ids[]" value="' . esc_attr( $audio->ID ) . '" ' . $checked . '> ';
        echo esc_html( $audio->post_title );
        echo '</label>';
        echo '</li>';
    }
    echo '</ul>';
}

// Save the meta box data when the post is saved
function aap_save_playlist_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['aap_playlist_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['aap_playlist_meta_box_nonce'], 'aap_save_playlist_meta_box_data' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['post_type'] ) && 'aap_playlist' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    if ( isset( $_POST['aap_audio_ids'] ) ) {
        $audio_ids = array_map( 'intval', $_POST['aap_audio_ids'] );
        update_post_meta( $post_id, '_aap_playlist_audio_ids', $audio_ids );
    } else {
        delete_post_meta( $post_id, '_aap_playlist_audio_ids' );
    }
}
add_action( 'save_post', 'aap_save_playlist_meta_box_data' );

// Add a shortcode column to the playlist list table
function aap_add_playlist_shortcode_column( $columns ) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter( 'manage_aap_playlist_posts_columns', 'aap_add_playlist_shortcode_column' );

// Display the shortcode in the custom column
function aap_display_playlist_shortcode_column( $column, $post_id ) {
    if ( $column == 'shortcode' ) {
        echo '<code>[audio_playlist id="' . $post_id . '"]</code>';
    }
}
add_action( 'manage_aap_playlist_posts_custom_column', 'aap_display_playlist_shortcode_column', 10, 2 );
