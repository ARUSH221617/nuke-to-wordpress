<?php
if (!defined('ABSPATH')) {
    exit;
}

function nuke_to_wordpress_settings_page_content() {
    ?>
    <div class="wrap">
        <h1>Nuke to WordPress Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('nuke_to_wordpress_settings_group');
            do_settings_sections('nuke-to-wordpress-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function nuke_to_wordpress_settings_init() {
    register_setting('nuke_to_wordpress_settings_group', 'nuke_to_wordpress_settings');

    add_settings_section(
        'nuke_to_wordpress_settings_section',
        'Nuke PHP Database Settings',
        'nuke_to_wordpress_settings_section_callback',
        'nuke-to-wordpress-settings'
    );

    add_settings_field(
        'nuke_to_wordpress_nuke_db_host',
        'Nuke DB Host',
        'nuke_to_wordpress_nuke_db_host_callback',
        'nuke-to-wordpress-settings',
        'nuke_to_wordpress_settings_section'
    );

    add_settings_field(
        'nuke_to_wordpress_nuke_db_name',
        'Nuke DB Name',
        'nuke_to_wordpress_nuke_db_name_callback',
        'nuke-to-wordpress-settings',
        'nuke_to_wordpress_settings_section'
    );

    add_settings_field(
        'nuke_to_wordpress_nuke_db_user',
        'Nuke DB User',
        'nuke_to_wordpress_nuke_db_user_callback',
        'nuke-to-wordpress-settings',
        'nuke_to_wordpress_settings_section'
    );

    add_settings_field(
        'nuke_to_wordpress_nuke_db_password',
        'Nuke DB Password',
        'nuke_to_wordpress_nuke_db_password_callback',
        'nuke-to-wordpress-settings',
        'nuke_to_wordpress_settings_section'
    );
}

function nuke_to_wordpress_settings_section_callback() {
    echo '<p>Enter your Nuke PHP database connection details below.</p>';
}

function nuke_to_wordpress_nuke_db_host_callback() {
    $options = get_option('nuke_to_wordpress_settings');
    ?>
    <input type="text" name="nuke_to_wordpress_settings[nuke_db_host]" value="<?php echo esc_attr($options['nuke_db_host']); ?>" />
    <?php
}

function nuke_to_wordpress_nuke_db_name_callback() {
    $options = get_option('nuke_to_wordpress_settings');
    ?>
    <input type="text" name="nuke_to_wordpress_settings[nuke_db_name]" value="<?php echo esc_attr($options['nuke_db_name']); ?>" />
    <?php
}

function nuke_to_wordpress_nuke_db_user_callback() {
    $options = get_option('nuke_to_wordpress_settings');
    ?>
    <input type="text" name="nuke_to_wordpress_settings[nuke_db_user]" value="<?php echo esc_attr($options['nuke_db_user']); ?>" />
    <?php
}

function nuke_to_wordpress_nuke_db_password_callback() {
    $options = get_option('nuke_to_wordpress_settings');
    ?>
    <input type="password" name="nuke_to_wordpress_settings[nuke_db_password]" value="<?php echo esc_attr($options['nuke_db_password']); ?>" />
    <?php
}

add_action('admin_init', 'nuke_to_wordpress_settings_init');
?>
