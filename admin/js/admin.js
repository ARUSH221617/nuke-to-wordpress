jQuery(document).ready(function($) {
    // Store initial form values
    const initialValues = {
        nuke_db_host: $('#nuke_db_host').val(),
        nuke_db_name: $('#nuke_db_name').val(),
        nuke_db_user: $('#nuke_db_user').val(),
        nuke_db_password: $('#nuke_db_password').val()
    };

    // Cancel button handler
    $('#cancel-settings').on('click', function() {
        // Reset form to initial values
        $('#nuke_db_host').val(initialValues.nuke_db_host);
        $('#nuke_db_name').val(initialValues.nuke_db_name);
        $('#nuke_db_user').val(initialValues.nuke_db_user);
        $('#nuke_db_password').val(initialValues.nuke_db_password);
        
        // Hide any messages
        $('#settings-message').hide();
        
        // Remove focus from the cancel button
        $(this).blur();
    });

    // Existing form submission code
    $('#nuke-to-wordpress-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const nonce = $('#nuke_to_wordpress_settings_nonce').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_nuke_settings',
                nonce: nonce,
                formData: formData
            },
            beforeSend: function() {
                $('#submit').prop('disabled', true);
                $('#cancel-settings').prop('disabled', true);
            },
            success: function(response) {
                $('#submit').prop('disabled', false);
                $('#cancel-settings').prop('disabled', false);
                const messageDiv = $('#settings-message');
                
                if (response.success) {
                    messageDiv.removeClass('notice-error').addClass('notice-success')
                        .html('<p>' + response.data.message + '</p>')
                        .show();
                    
                    // Update initial values after successful save
                    initialValues.nuke_db_host = $('#nuke_db_host').val();
                    initialValues.nuke_db_name = $('#nuke_db_name').val();
                    initialValues.nuke_db_user = $('#nuke_db_user').val();
                    initialValues.nuke_db_password = $('#nuke_db_password').val();
                } else {
                    messageDiv.removeClass('notice-success').addClass('notice-error')
                        .html('<p>Error: ' + response.data.message + '</p>')
                        .show();
                }
                
                setTimeout(() => {
                    messageDiv.fadeOut();
                }, 3000);
            },
            error: function(xhr, status, error) {
                $('#submit').prop('disabled', false);
                $('#cancel-settings').prop('disabled', false);
                const messageDiv = $('#settings-message');
                messageDiv.removeClass('notice-success').addClass('notice-error')
                    .html('<p>Error: Failed to save settings. Please try again.</p>')
                    .show();
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    });
});
