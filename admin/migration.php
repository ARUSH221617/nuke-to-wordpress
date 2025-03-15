<?php
if (!defined('ABSPATH')) {
    exit;
}

function nuke_to_wordpress_migration_page_content() {
    $migration_state = get_option('nuke_to_wordpress_migration_state', [
        'status' => 'not_started',
        'current_task' => '',
        'processed_items' => 0,
        'total_items' => 0,
        'last_run' => '',
        'last_error' => ''
    ]);

    $progress = 0;
    if ($migration_state['total_items'] > 0) {
        $progress = ($migration_state['processed_items'] / $migration_state['total_items']) * 100;
    }
    ?>
    <div class="wrap">
        <div class="min-h-screen p-8 bg-background text-foreground">
            <div class="container max-w-2xl">
                <h1 class="scroll-m-20 text-4xl font-extrabold tracking-tight lg:text-5xl mb-8">
                    Nuke to WordPress Migration
                </h1>

                <?php if ($migration_state['status'] === 'not_started'): ?>
                    <!-- Migration Start Form -->
                    <div class="bg-card rounded-xl p-6 border mb-6">
                        <h2 class="text-2xl font-semibold mb-4">Start Migration</h2>
                        <p class="text-muted-foreground mb-6">
                            This will migrate your content from Nuke CMS to WordPress. The process includes:
                        </p>
                        <ul class="list-disc list-inside space-y-2 mb-6 text-muted-foreground">
                            <li>Categories migration</li>
                            <li>Articles migration</li>
                            <li>Images migration</li>
                        </ul>
                        
                        <!-- Migration Mode Selection -->
                        <div class="bg-card rounded-lg p-4 border mb-6">
                            <h3 class="font-semibold text-lg mb-3">Migration Mode</h3>
                            <div class="space-y-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="mode_auto" name="migration_mode" value="auto" 
                                            class="h-4 w-4" checked>
                                        <label for="mode_auto" class="text-sm font-medium">
                                            Automatic (Background Process)
                                        </label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="mode_manual" name="migration_mode" value="manual" 
                                            class="h-4 w-4">
                                        <label for="mode_manual" class="text-sm font-medium">
                                            Manual (Step by Step)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form id="migration-form" method="post">
                            <?php wp_nonce_field('start_migration', 'migration_nonce'); ?>
                            <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm 
                                font-medium ring-offset-background transition-colors focus-visible:outline-none 
                                focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 
                                disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground 
                                hover:bg-primary/90 h-10 px-4 py-2">
                                Start Migration
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Migration Progress -->
                    <div class="bg-card rounded-xl p-6 border">
                        <h2 class="text-2xl font-semibold mb-4">Migration Progress</h2>
                        
                        <!-- Progress Bar -->
                        <div class="w-full h-4 rounded-full bg-secondary overflow-hidden mb-4">
                            <div class="progress-bar h-full bg-primary transition-all duration-300" 
                                style="width: <?php echo esc_attr($progress); ?>%">
                            </div>
                        </div>

                        <!-- Status Information -->
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Status:</span>
                                <span class="font-medium">
                                    <?php echo esc_html(ucfirst($migration_state['status'])); ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Current Task:</span>
                                <span class="font-medium">
                                    <?php echo esc_html($migration_state['current_task']); ?>
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Progress:</span>
                                <span class="font-medium">
                                    <?php echo esc_html(round($progress, 1)); ?>% Complete
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Items Processed:</span>
                                <span class="font-medium">
                                    <?php echo esc_html($migration_state['processed_items']); ?> / 
                                    <?php echo esc_html($migration_state['total_items']); ?>
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Last Activity:</span>
                                <span class="font-medium">
                                    <?php echo esc_html($migration_state['last_run']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <?php if (!empty($migration_state['last_error'])): ?>
                            <div class="bg-destructive/15 text-destructive rounded-lg p-4 mb-6">
                                <p class="text-sm font-medium">Error: <?php echo esc_html($migration_state['last_error']); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="flex space-x-4">
                            <?php if ($migration_state['status'] === 'failed'): ?>
                                <button id="retry-migration" class="inline-flex items-center justify-center rounded-md 
                                    text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none 
                                    focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 
                                    disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground 
                                    hover:bg-primary/90 h-10 px-4 py-2">
                                    Retry Migration
                                </button>
                            <?php endif; ?>

                            <?php if (in_array($migration_state['status'], ['in_progress', 'failed', 'starting'])): ?>
                                <button id="cancel-migration" class="inline-flex items-center justify-center rounded-md 
                                    text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none 
                                    focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 
                                    disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground 
                                    hover:bg-destructive/90 h-10 px-4 py-2">
                                    Cancel Migration
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Manual Steps Section -->
                    <div id="manual-controls" class="mt-6 <?php echo $migration_state['mode'] === 'manual' ? '' : 'hidden'; ?>">
                        <div class="bg-card rounded-xl p-6 border">
                            <h3 class="text-xl font-semibold mb-4">Manual Migration Steps</h3>
                            <div class="space-y-4">
                                <?php
                                $steps = [
                                    'categories' => 'Migrate Categories',
                                    'articles' => 'Migrate Articles',
                                    'images' => 'Migrate Images'
                                ];
                                $current_step = array_search($migration_state['current_task'], array_keys($steps));
                                $index = 0;
                                foreach ($steps as $step => $label):
                                    $is_completed = $migration_state['completed_steps'][$step] ?? false;
                                    $is_current = $migration_state['current_task'] === $step;
                                    $is_disabled = !$is_current && !$is_completed;
                                ?>
                                    <button type="button" 
                                        data-step="<?php echo esc_attr($step); ?>" 
                                        class="manual-step-btn w-full text-left px-4 py-3 border rounded-md hover:bg-accent 
                                            <?php echo $is_disabled ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                        <?php echo $is_disabled ? 'disabled' : ''; ?>>
                                        <div class="flex items-center">
                                            <span class="step-number mr-2"><?php echo esc_html($index + 1); ?>.</span>
                                            <span><?php echo esc_html($label); ?></span>
                                            <span class="step-status ml-auto">
                                                <?php
                                                if ($is_completed) {
                                                    echo '<span class="text-success">✓ Complete</span>';
                                                } elseif ($is_current) {
                                                    echo '<span class="text-primary">In Progress</span>';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </button>
                                <?php 
                                    $index++;
                                endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            const CHECK_INTERVAL = 5000; // Check every 5 seconds
            let checkTimer;

            // Migration mode toggle
            $('input[name="migration_mode"]').on('change', function() {
                const isManual = $(this).val() === 'manual';
                $('#manual-controls').toggleClass('hidden', !isManual);
                $('#migration-form').toggleClass('hidden', isManual);
            });

            // Start migration form submission
            $('#migration-form').on('submit', function(e) {
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
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            showError(response.data.message || 'Failed to start migration');
                        }
                    },
                    error: function() {
                        showError('Failed to start migration');
                    }
                });
            }

            // Manual step execution
            $('.manual-step-btn').on('click', function() {
                const $btn = $(this);
                const step = $btn.data('step');
                
                if ($btn.prop('disabled')) {
                    return;
                }

                executeManualStep($btn, step);
            });

            function executeManualStep($btn, step) {
                $btn.prop('disabled', true)
                    .find('.step-status')
                    .html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'manual_migration_step',
                        step: step,
                        nonce: $('#migration_nonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.find('.step-status').html('<span class="text-success">✓ Complete</span>');
                            $btn.next('.manual-step-btn').prop('disabled', false);
                            updateProgress(response.data.progress);
                        } else {
                            $btn.find('.step-status').html('<span class="text-destructive">✗ Failed</span>');
                            $btn.prop('disabled', false);
                            showError(response.data.message || 'Step failed');
                        }
                    },
                    error: function() {
                        $btn.find('.step-status').html('<span class="text-destructive">✗ Error</span>');
                        $btn.prop('disabled', false);
                        showError('Network error occurred');
                    }
                });
            }

            // Progress checking for automatic mode
            if ($('#migration-progress').is(':visible')) {
                startStatusCheck();
            }

            function startStatusCheck() {
                checkMigrationStatus();
                checkTimer = setInterval(checkMigrationStatus, CHECK_INTERVAL);
            }

            function checkMigrationStatus() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'check_migration_status',
                        nonce: $('#migration_nonce').val()
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            updateProgress(response.data);

                            if (response.data.status === 'completed') {
                                clearInterval(checkTimer);
                                location.reload();
                            } else if (response.data.status === 'failed') {
                                clearInterval(checkTimer);
                                location.reload();
                            }
                        }
                    }
                });
            }

            function updateProgress(data) {
                if (!data) return;
                
                const progress = Math.round(data.progress || 0);
                $('.progress-bar').css('width', progress + '%');
                $('.progress-percentage').text(progress + '% Complete');
                $('.current-task').text(data.current_task || 'Initializing...');
                $('.last-run span').text(data.last_run || 'Just now');
            }

            function showError(message) {
                const errorHtml = `
                    <div class="bg-destructive/15 text-destructive rounded-lg p-4 mb-6">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                `;
                
                $('.error-message').html(errorHtml).removeClass('hidden');
            }

            // Action buttons
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

            $('#cancel-migration').on('click', function() {
                if (!confirm('Are you sure you want to cancel the migration? This will stop the process and rollback changes.')) {
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cancel_migration',
                        nonce: $('#migration_nonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            showError('Failed to cancel migration');
                        }
                    },
                    error: function() {
                        showError('Failed to cancel migration');
                    }
                });
            });
        });
    </script>
    <?php
}?>
