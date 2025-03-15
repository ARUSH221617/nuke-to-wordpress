<?php
if (!defined('ABSPATH')) {
    exit;
}

function nuke_to_wordpress_migration_page_content()
{
    $migration_state = get_option('nuke_to_wordpress_migration_state', ['status' => 'not_started']);
    ?>
    <div class="wrap">
        <h1>Nuke to WordPress Migration</h1>

        <div id="migration-progress" class="<?php echo $migration_state['status'] === 'not_started' ? 'hidden' : ''; ?>">
            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: 0%"></div>
            </div>
            <p class="progress-status">Processing: <span class="current-task"></span></p>
            <p class="progress-percentage">0% Complete</p>
            <p class="last-run">Last activity: <span></span></p>
            <div class="error-message hidden">
                <p class="notice notice-error"></p>
            </div>
        </div>

        <form method="post" action="" id="migration-form"
            class="<?php echo $migration_state['status'] !== 'not_started' ? 'hidden' : ''; ?>">
            <?php wp_nonce_field('start_migration', 'migration_nonce'); ?>
            <p>Click the button below to start the migration process. The migration will run in the background.</p>
            <input type="submit" name="start_migration" class="button button-primary" value="Start Migration" />
        </form>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            const CHECK_INTERVAL = 5000; // Check every 5 seconds
            let checkTimer;

            $('#migration-form').on('submit', function (e) {
                e.preventDefault();
                startMigration();
            });

            function startMigration() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'start_migration',
                        nonce: $('#migration_nonce').val()
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#migration-form').hide();
                            $('#migration-progress').show();
                            startStatusCheck();
                        }
                    },
                    error: function () {
                        showError('Failed to start migration');
                    }
                });
            }

            function checkMigrationStatus() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'check_migration_status',
                        nonce: $('#migration_nonce').val()
                    },
                    success: function (response) {
                        if (response.success) {
                            updateProgress(response.data);

                            if (response.data.status === 'completed') {
                                clearInterval(checkTimer);
                                $('#migration-progress').append('<p class="notice notice-success">Migration completed successfully!</p>');
                            } else if (response.data.last_error) {
                                showError(response.data.last_error);
                            }
                        }
                    },
                    error: function () {
                        showError('Failed to check migration status');
                    }
                });
            }

            function startStatusCheck() {
                checkMigrationStatus();
                checkTimer = setInterval(checkMigrationStatus, CHECK_INTERVAL);
            }

            function updateProgress(data) {
                $('.progress-bar-fill').css('width', data.progress + '%');
                $('.progress-percentage').text(Math.round(data.progress) + '% Complete');
                $('.current-task').text(data.current_task);
                $('.last-run span').text(data.last_run);
            }

            function showError(message) {
                $('.error-message').removeClass('hidden')
                    .find('.notice-error').text('Error: ' + message);
            }

            // Start checking if migration is already in progress
            if ($('#migration-progress').is(':visible')) {
                startStatusCheck();
            }
        });
    </script>
    <?php
}

function nuke_to_wordpress_start_migration()
{
    // Placeholder for migration logic
    echo '<div class="notice notice-success is-dismissible"><p>Migration started. Please check the progress below.</p></div>';
    // Add migration logic here
}

add_action('admin_menu', 'nuke_to_wordpress_admin_menu');
?>