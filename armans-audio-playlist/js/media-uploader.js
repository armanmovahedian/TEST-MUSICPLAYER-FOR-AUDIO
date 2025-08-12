jQuery(document).ready(function($){
    var mediaUploader;

    $('#aap_upload_audio_button').click(function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Audio',
            button: {
                text: 'Choose Audio'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#aap_audio_file').val(attachment.url);
        });

        mediaUploader.open();
    });
});
