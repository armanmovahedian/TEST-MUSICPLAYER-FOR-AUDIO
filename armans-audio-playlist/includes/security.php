<?php

// Serve the audio file securely
function aap_serve_audio_file() {
    if ( isset( $_GET['serve_audio'] ) ) {
        $post_id = intval( $_GET['serve_audio'] );
        $audio_url = get_post_meta( $post_id, '_aap_audio_file', true );

        if ( ! $audio_url ) {
            status_header( 404 );
            die( 'Audio file not found.' );
        }

        // Get the path to the file from the URL
        $file_path = str_replace( content_url(), WP_CONTENT_DIR, $audio_url );


        if ( file_exists( $file_path ) ) {
            // Set headers
            header( 'Content-Type: audio/mpeg' ); // Adjust content type based on file type
            header( 'Content-Length: ' . filesize( $file_path ) );
            header( 'Accept-Ranges: bytes' );
            header( 'Content-Disposition: inline; filename="' . basename( $file_path ) . '"' );

            // Read the file and output its contents
            readfile( $file_path );
            exit;
        } else {
            status_header( 404 );
            die( 'Audio file not found on server.' );
        }
    }
}
add_action( 'template_redirect', 'aap_serve_audio_file' );
