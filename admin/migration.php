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
    // $migration_state=[
    //     'status' => 'starting',
    //     'current_task' => '',
    //     'processed_items' => 0,
    //     'total_items' => 0,
    //     'last_run' => '',
    //     'last_error' => ''
    // ];

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

                <?php if ($migration_state['status'] === 'not_started'||$migration_state['status'] === 'cancelled'): ?>
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
                    <?php wp_nonce_field('start_migration', 'migration_nonce'); ?>
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
                    <div id="manual-controls" class="mt-6 <?php echo @$migration_state['mode'] === 'manual' ? '' : 'hidden'; ?>">
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
    <?php
}