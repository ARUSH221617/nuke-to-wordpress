<?php
if (!defined('ABSPATH')) {
    exit;
}

function nuke_to_wordpress_help_page_content() {
    ?>
    <div class="min-h-screen p-8 bg-background text-foreground">
        <div class="container max-w-4xl">
            <h1 class="scroll-m-20 text-4xl font-extrabold tracking-tight lg:text-5xl mb-8">
                Help & Documentation
            </h1>

            <!-- Quick Start Guide -->
            <div class="mb-12">
                <h2 class="scroll-m-20 text-2xl font-semibold tracking-tight mb-4">
                    Quick Start Guide
                </h2>
                <div class="space-y-4 bg-card p-6 rounded-lg border">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 border rounded-lg">
                            <h3 class="font-semibold mb-2">1. Configuration</h3>
                            <p class="text-sm text-muted-foreground">Set up your Nuke database credentials in the Settings page</p>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <h3 class="font-semibold mb-2">2. Preparation</h3>
                            <p class="text-sm text-muted-foreground">Backup your WordPress site and verify requirements</p>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <h3 class="font-semibold mb-2">3. Migration</h3>
                            <p class="text-sm text-muted-foreground">Start the migration process and monitor progress</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mb-12">
                <h2 class="scroll-m-20 text-2xl font-semibold tracking-tight mb-4">
                    Frequently Asked Questions
                </h2>
                <div class="space-y-4">
                    <details class="border rounded-lg p-4">
                        <summary class="font-medium cursor-pointer">How long does the migration take?</summary>
                        <div class="mt-2 text-sm text-muted-foreground">
                            Migration time depends on your content volume and server resources. For reference:
                            <ul class="list-disc list-inside mt-2">
                                <li>Small sites (< 1000 articles): 10-15 minutes</li>
                                <li>Medium sites (1000-5000 articles): 30-60 minutes</li>
                                <li>Large sites (> 5000 articles): 1-3 hours</li>
                            </ul>
                        </div>
                    </details>
                    <details class="border rounded-lg p-4">
                        <summary class="font-medium cursor-pointer">What happens if the migration fails?</summary>
                        <div class="mt-2 text-sm text-muted-foreground">
                            The plugin includes a rollback feature that automatically reverts changes if an error occurs. You can also manually trigger a rollback from the Migration page.
                        </div>
                    </details>
                    <details class="border rounded-lg p-4">
                        <summary class="font-medium cursor-pointer">Can I migrate specific content only?</summary>
                        <div class="mt-2 text-sm text-muted-foreground">
                            Yes, you can select which content types to migrate (categories, articles, images) from the Migration page.
                        </div>
                    </details>
                </div>
            </div>

            <!-- Troubleshooting -->
            <div class="mb-12">
                <h2 class="scroll-m-20 text-2xl font-semibold tracking-tight mb-4">
                    Troubleshooting
                </h2>
                <div class="space-y-4">
                    <div class="border rounded-lg p-6">
                        <h3 class="font-semibold mb-4">Common Issues</h3>
                        <div class="space-y-4">
                            <div class="border-l-4 border-destructive p-4 bg-destructive/10">
                                <h4 class="font-medium">Database Connection Failed</h4>
                                <p class="text-sm text-muted-foreground mt-1">
                                    1. Verify database credentials<br>
                                    2. Check if remote connections are allowed<br>
                                    3. Confirm database server is accessible
                                </p>
                            </div>
                            <div class="border-l-4 border-destructive p-4 bg-destructive/10">
                                <h4 class="font-medium">Migration Timeout</h4>
                                <p class="text-sm text-muted-foreground mt-1">
                                    1. Reduce batch size in settings<br>
                                    2. Increase PHP memory limit<br>
                                    3. Increase max execution time
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="mb-12">
                <h2 class="scroll-m-20 text-2xl font-semibold tracking-tight mb-4">
                    Need More Help?
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="https://github.com/yourusername/nuke-to-wordpress/issues" 
                       class="border rounded-lg p-6 hover:bg-accent transition-colors">
                        <h3 class="font-semibold mb-2">GitHub Issues</h3>
                        <p class="text-sm text-muted-foreground">Report bugs or request features</p>
                    </a>
                    <a href="mailto:support@example.com" 
                       class="border rounded-lg p-6 hover:bg-accent transition-colors">
                        <h3 class="font-semibold mb-2">Email Support</h3>
                        <p class="text-sm text-muted-foreground">Contact our support team directly</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
}