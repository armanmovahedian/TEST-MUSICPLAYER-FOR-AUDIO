<?php

// Add meta boxes to the audio sample edit screen
function aap_add_meta_boxes() {
    add_meta_box(
        'aap_audio_attributes',
        __( 'Audio Attributes', 'text_domain' ),
        'aap_audio_attributes_callback',
        'audio_sample',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'aap_add_meta_boxes' );

// The callback function to display the fields in the meta box
function aap_audio_attributes_callback( $post ) {
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'aap_save_meta_box_data', 'aap_meta_box_nonce' );

    // Use get_post_meta to retrieve an existing value from the database.
    $style = get_post_meta( $post->ID, '_aap_style', true );
    $microphone = get_post_meta( $post->ID, '_aap_microphone', true );
    $effects = get_post_meta( $post->ID, '_aap_effects', true );
    $amp = get_post_meta( $post->ID, '_aap_amp', true );
    $audio_file = get_post_meta( $post->ID, '_aap_audio_file', true );

    // Display the form fields.
    ?>
    <p>
        <label for="aap_style"><?php _e( 'Style', 'text_domain' ); ?></label>
        <input type="text" id="aap_style" name="aap_style" value="<?php echo esc_attr( $style ); ?>" class="widefat" />
    </p>
    <p>
        <label for="aap_microphone"><?php _e( 'Microphone', 'text_domain' ); ?></label>
        <input type="text" id="aap_microphone" name="aap_microphone" value="<?php echo esc_attr( $microphone ); ?>" class="widefat" />
    </p>
    <p>
        <label for="aap_effects"><?php _e( 'Effects', 'text_domain' ); ?></label>
        <input type="text" id="aap_effects" name="aap_effects" value="<?php echo esc_attr( $effects ); ?>" class="widefat" />
    </p>
    <p>
        <label for="aap_amp"><?php _e( 'Amplifier', 'text_domain' ); ?></label>
        <input type="text" id="aap_amp" name="aap_amp" value="<?php echo esc_attr( $amp ); ?>" class="widefat" />
    </p>
    <p>
        <label for="aap_audio_file"><?php _e( 'Audio File', 'text_domain' ); ?></label>
        <input type="text" id="aap_audio_file" name="aap_audio_file" value="<?php echo esc_attr( $audio_file ); ?>" class="widefat" readonly />
        <button id="aap_upload_audio_button" class="button"><?php _e( 'Upload Audio', 'text_domain' ); ?></button>
    </p>
    <?php
}

// Save the meta box data when the post is saved
function aap_save_meta_box_data( $post_id ) {
    // Check if our nonce is set.
    if ( ! isset( $_POST['aap_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['aap_meta_box_nonce'], 'aap_save_meta_box_data' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'audio_sample' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // Sanitize user input and update the meta fields.
    $fields = ['aap_style', 'aap_microphone', 'aap_effects', 'aap_amp', 'aap_audio_file'];
    foreach ($fields as $field) {
        if ( isset( $_POST[$field] ) ) {
            update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[$field] ) );
        }
    }
}
add_action( 'save_post', 'aap_save_meta_box_data' );

// Enqueue the media uploader script
function aap_enqueue_media_uploader() {
    global $typenow;
    if ( $typenow == 'audio_sample' ) {
        wp_enqueue_media();
        // Enqueue our custom script that will handle the media uploader
        wp_enqueue_script(
            'aap-media-uploader',
            AAP_PLUGIN_URL . 'js/media-uploader.js',
            array( 'jquery' ),
            '1.0',
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'aap_enqueue_media_uploader' );
