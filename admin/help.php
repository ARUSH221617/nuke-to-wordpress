<?php
if (!defined('ABSPATH')) {
    exit;
}

function nuke_to_wordpress_help_page_content()
{
    // Ensure we're in the WordPress admin
    if (!is_admin()) {
        return;
    }
    ?>
    <div class="wrap">
        <div class="min-h-screen p-8 bg-background text-foreground">
            <div class="container max-w-4xl mx-auto">
                <!-- Hero Section -->
                <div class="mb-12 text-center">
                    <h1 class="scroll-m-20 text-4xl font-extrabold tracking-tight lg:text-5xl mb-4">
                        Help & Documentation
                    </h1>
                    <p class="text-muted-foreground text-lg">
                        Everything you need to know about migrating from Nuke to WordPress
                    </p>
                </div>

                <!-- Quick Start Guide -->
                <div class="mb-12">
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <h2 class="scroll-m-20 text-2xl font-semibold tracking-tight">Quick Start Guide</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-card rounded-xl p-6 shadow-sm border hover:border-primary/50 transition-colors">
                            <div
                                class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary mb-4">
                                1</div>
                            <h3 class="font-semibold text-xl mb-2">Configuration</h3>
                            <p class="text-muted-foreground">Set up your Nuke database credentials in the Settings page</p>
                        </div>
                        <div class="bg-card rounded-xl p-6 shadow-sm border hover:border-primary/50 transition-colors">
                            <div
                                class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary mb-4">
                                2</div>
                            <h3 class="font-semibold text-xl mb-2">Preparation</h3>
                            <p class="text-muted-foreground">Backup your WordPress site and verify requirements</p>
                        </div>
                        <div class="bg-card rounded-xl p-6 shadow-sm border hover:border-primary/50 transition-colors">
                            <div
                                class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary mb-4">
                                3</div>
                            <h3 class="font-semibold text-xl mb-2">Migration</h3>
                            <p class="text-muted-foreground">Start the migration process and monitor progress</p>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="mb-12">
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="scroll-m-20 text-2xl font-semibold tracking-tight">Frequently Asked Questions</h2>
                    </div>
                    <div class="space-y-4">
                        <details class="group bg-card rounded-xl overflow-hidden border">
                            <summary class="font-medium cursor-pointer p-6 flex items-center justify-between">
                                How long does the migration take?
                                <svg class="w-5 h-5 transition-transform group-open:rotate-180" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div class="p-6 pt-0 text-muted-foreground">
                                Migration time depends on your content volume and server resources. For reference:
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Small sites (< 1000 articles): 10-15 minutes</li>
                                    <li>Medium sites (1000-5000 articles): 30-60 minutes</li>
                                    <li>Large sites (> 5000 articles): 1-3 hours</li>
                                </ul>
                            </div>
                        </details>
                        <details class="group bg-card rounded-xl overflow-hidden border">
                            <summary class="font-medium cursor-pointer p-6 flex items-center justify-between">
                                What happens if the migration fails?
                                <svg class="w-5 h-5 transition-transform group-open:rotate-180" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div class="p-6 pt-0 text-muted-foreground">
                                The plugin includes a rollback feature that automatically reverts changes if an error
                                occurs. You can also manually trigger a rollback from the Migration page.
                            </div>
                        </details>
                        <details class="group bg-card rounded-xl overflow-hidden border">
                            <summary class="font-medium cursor-pointer p-6 flex items-center justify-between">
                                Can I migrate specific content only?
                                <svg class="w-5 h-5 transition-transform group-open:rotate-180" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div class="p-6 pt-0 text-muted-foreground">
                                Yes, you can select which content types to migrate (categories, articles, images) from the
                                Migration page.
                            </div>
                        </details>
                    </div>
                </div>

                <!-- Troubleshooting -->
                <div class="mb-12">
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        <h2 class="scroll-m-20 text-2xl font-semibold tracking-tight">Troubleshooting</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-destructive/10 rounded-xl p-6 border border-destructive/20">
                            <h3 class="font-semibold text-xl mb-4 text-destructive">Database Connection Failed</h3>
                            <ol class="list-decimal list-inside space-y-2 text-muted-foreground">
                                <li>Verify database credentials</li>
                                <li>Check if remote connections are allowed</li>
                                <li>Confirm database server is accessible</li>
                            </ol>
                        </div>
                        <div class="bg-destructive/10 rounded-xl p-6 border border-destructive/20">
                            <h3 class="font-semibold text-xl mb-4 text-destructive">Migration Timeout</h3>
                            <ol class="list-decimal list-inside space-y-2 text-muted-foreground">
                                <li>Reduce batch size in settings</li>
                                <li>Increase PHP memory limit</li>
                                <li>Increase max execution time</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- WordPress Cron Configuration -->
                <div class="mb-12">
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="scroll-m-20 text-2xl font-semibold tracking-tight">WordPress Cron Setup</h2>
                    </div>

                    <div class="space-y-6">
                        <!-- Explanation -->
                        <div class="bg-card rounded-xl p-6 border">
                            <h3 class="font-semibold text-xl mb-2">Why Configure WordPress Cron?</h3>
                            <p class="text-muted-foreground mb-4">
                                The migration process runs in the background using WordPress cron jobs. For reliable
                                operation,
                                especially with large migrations, we recommend setting up a system-level cron job instead of
                                WordPress's default virtual cron.
                            </p>
                        </div>

                        <!-- Steps -->
                        <div class="bg-card rounded-xl p-6 border">
                            <h3 class="font-semibold text-xl mb-4">Configuration Steps</h3>

                            <div class="space-y-4">
                                <div class="bg-secondary/20 rounded-lg p-4">
                                    <h4 class="font-medium mb-2">1. Disable WordPress Virtual Cron</h4>
                                    <p class="text-sm text-muted-foreground mb-2">
                                        Add this line to your wp-config.php file:
                                    </p>
                                    <pre
                                        class="bg-card p-3 rounded border text-sm font-mono">define('DISABLE_WP_CRON', true);</pre>
                                </div>

                                <div class="bg-secondary/20 rounded-lg p-4">
                                    <h4 class="font-medium mb-2">2. Set Up System Cron</h4>
                                    <p class="text-sm text-muted-foreground mb-2">
                                        Add one of these commands to your server's crontab:
                                    </p>
                                    <div class="space-y-2">
                                        <div>
                                            <p class="text-sm font-medium mb-1">Linux/Unix:</p>
                                            <pre
                                                class="bg-card p-3 rounded border text-sm font-mono">*/5 * * * * wget -q -O - http://your-site.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1</pre>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium mb-1">Alternative using curl:</p>
                                            <pre
                                                class="bg-card p-3 rounded border text-sm font-mono">*/5 * * * * curl -s http://your-site.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1</pre>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-secondary/20 rounded-lg p-4">
                                    <h4 class="font-medium mb-2">3. Verify Configuration</h4>
                                    <ol class="list-decimal list-inside space-y-2 text-sm text-muted-foreground">
                                        <li>Check if the line was added correctly to wp-config.php</li>
                                        <li>Verify the cron job is running using: <code
                                                class="bg-card px-2 py-1 rounded">crontab -l</code></li>
                                        <li>Monitor server logs for any errors</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Common Issues -->
                        <div class="bg-destructive/10 rounded-xl p-6 border border-destructive/20">
                            <h3 class="font-semibold text-xl mb-4 text-destructive">Common Issues</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-destructive mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium">Permission Errors</p>
                                        <p class="text-sm text-muted-foreground">Ensure the web server has proper
                                            permissions to execute wp-cron.php</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-destructive mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium">SSL Certificate Issues</p>
                                        <p class="text-sm text-muted-foreground">If using HTTPS, verify SSL certificate is
                                            valid and trusted</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-destructive mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium">Incorrect Site URL</p>
                                        <p class="text-sm text-muted-foreground">Double-check the website URL in the cron
                                            command matches your site</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <div>
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="scroll-m-20 text-2xl font-semibold tracking-tight">Need More Help?</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="https://github.com/yourusername/nuke-to-wordpress/issues"
                            class="group bg-card rounded-xl p-6 border hover:border-primary/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                <h3 class="font-semibold text-xl group-hover:text-primary transition-colors">GitHub Issues
                                </h3>
                            </div>
                            <p class="mt-2 text-muted-foreground">Report bugs or request features</p>
                        </a>
                        <a href="mailto:support@example.com"
                            class="group bg-card rounded-xl p-6 border hover:border-primary/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <h3 class="font-semibold text-xl group-hover:text-primary transition-colors">Email Support
                                </h3>
                            </div>
                            <p class="mt-2 text-muted-foreground">Contact our support team directly</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
