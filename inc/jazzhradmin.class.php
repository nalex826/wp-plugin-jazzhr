<?php
/**
 * WP Custom Admin JazzHR API Class
 *
 * This class handles JazzHR API settings and cache management in the WordPress admin panel.
 */
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/* Check if Class Exists. */
if (! class_exists('JazzHrAdmin')) {
    class JazzHrAdmin
    {
        /**
         * Constructor. Initializes JazzHR admin actions.
         */
        public function __construct()
        {
            if (is_admin()) { // Admin actions
                add_action('admin_menu', [$this, 'add_admin_page']);
                add_action('admin_init', [$this, 'add_custom_settings']);
            }
        }

        /**
         * Delete JazzHR cache.
         */
        public function delete_cache()
        {
            delete_transient('jazzhr_cache');
            add_action('admin_notices', [$this, 'admin_notice']);
        }

        /**
         * Display admin notice after cache deletion.
         */
        public function admin_notice()
        {
            echo '<div class="updated"><p>JazzHR cache has been wiped.</p></div>';
        }

        /**
         * Add JazzHR API settings page to admin menu.
         */
        public function add_admin_page()
        {
            add_menu_page(
                'JazzHR API Settings',
                'JazzHR API',
                'manage_options',
                'jazzhr-api',
                [$this, 'render_settings_page'],
                'dashicons-groups',
                50
            );
        }

        /**
         * Add custom settings section for JazzHR API.
         */
        public function add_custom_settings()
        {
            add_settings_section('jazzhr_settings', '', [$this, 'settings_callback'], 'jazzhr-api-settings');
            register_setting('jazzhr_settings', 'jazzhr_api');
        }

        /**
         * Render settings page content.
         */
        public function render_settings_page()
        {
            ?>
<div class="wrap">
  <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
  <form method="post" action="options.php" autocomplete="off">
    <?php
            do_settings_sections('jazzhr-api-settings');
            submit_button('Save Settings', 'primary', 'submit');
            ?>
  </form>
</div>
<hr>
<p>
  <a href="/wp-admin/admin.php?page=jazzhr-api&action=delete" class="button button-primary">Wipe Feed Cache</a>
</p>
<?php
        }

        /**
         * Callback function for settings section.
         */
        public function settings_callback()
        {
            settings_fields('jazzhr_settings');
            $this->api_settings_callback();
        }

        /**
         * Callback function for API settings.
         */
        public function api_settings_callback()
        {
            $jazzhr_api = get_option('jazzhr_api');
            ?>
<div class="form-wrap" style="max-width: 500px;">
  <div class="form-field">
    <label for="jazzhr_api[apikey]">JazzHR API Key</label>
    <input name="jazzhr_api[apikey]" required type="password" value="<?php echo ! empty($jazzhr_api['apikey']) ? $jazzhr_api['apikey'] : ''; ?>" />
  </div>
</div>
<?php
        }
    }
}
?>