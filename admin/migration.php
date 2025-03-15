<?php
if (!defined('ABSPATH')) {
    exit;
}

function nuke_to_wordpress_migration_page_content()
{
    $migration_state = get_option('nuke_to_wordpress_migration_state', ['status' => 'not_started']);
    ?>
    <div class="min-h-screen p-8 bg-background text-foreground">
        <div class="container max-w-2xl">
            <h1 class="scroll-m-20 text-4xl font-extrabold tracking-tight lg:text-5xl mb-8">
                Nuke to WordPress Migration
            </h1>

            <div id="migration-progress" class="<?php echo $migration_state['status'] === 'not_started' ? 'hidden' : ''; ?>">
                <div class="w-full h-4 rounded-full bg-secondary overflow-hidden mb-4">
                    <div class="progress-bar-fill h-full bg-primary transition-all duration-300" style="width: 0%"></div>
                </div>
                
                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground progress-status">
                        Processing: <span class="current-task font-medium"></span>
                    </p>
                    <p class="text-sm text-muted-foreground progress-percentage">0% Complete</p>
                    <p class="text-sm text-muted-foreground last-run">
                        Last activity: <span></span>
                    </p>
                </div>

                <div class="error-message hidden mt-4">
                    <div class="p-4 rounded-lg bg-destructive/15 text-destructive">
                        <p class="text-sm"></p>
                    </div>
                </div>

                <div class="migration-actions mt-6 space-x-4">
                    <?php if ($migration_state['status'] === 'failed'): ?>
                        <button id="retry-migration" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Retry Failed Task
                        </button>
                        <button id="rollback-migration" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                            Rollback Migration
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <form method="post" action="" id="migration-form" class="<?php echo $migration_state['status'] !== 'not_started' ? 'hidden' : ''; ?>">
                <?php wp_nonce_field('start_migration', 'migration_nonce'); ?>
                <p class="text-sm text-muted-foreground mb-4">
                    Click the button below to start the migration process. The migration will run in the background.
                </p>
                <button type="submit" name="start_migration" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    Start Migration
                </button>
            </form>
        </div>
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

            $('#retry-migration').on('click', function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'retry_migration',
                        nonce: $('#migration_nonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            showError('Failed to retry migration');
                        }
                    },
                    error: function() {
                        showError('Failed to retry migration');
                    }
                });
            });

            $('#rollback-migration').on('click', function() {
                if (!confirm('Are you sure you want to rollback the migration? This will undo all changes.')) {
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rollback_migration',
                        nonce: $('#migration_nonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            showError('Failed to rollback migration');
                        }
                    },
                    error: function() {
                        showError('Failed to rollback migration');
                    }
                });
            });

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
