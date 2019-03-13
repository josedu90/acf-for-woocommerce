<?php
if (!class_exists('Merlin')) {
    require_once __DIR__ . "/merlin/vendor/autoload.php";
    require_once __DIR__ . "/merlin/class-merlin.php";
}

if (!class_exists('CpMerlin')) {
    class CpMerlin extends Merlin
    {

        public static $pluginPath;
        public $product_id;

        public $import_files_data;
        public $config;

        function __construct($pluginPath, $config, $options, $files = [])
        {
            $config["base_path"] = __DIR__;
            $config["base_url"] = plugin_dir_url(__FILE__);

            $this->config = $config = $this->extractConfigs($config);
            $options = $this->extractOptions($options);
            parent::__construct($config, $options);

            $this->import_files_data = $files;
            $this->theme = $this->fakeWpTheme($pluginPath);
            $this->slug = strtolower(preg_replace('#[^a-zA-Z]#', '', $this->theme->template));
            $this->ignore = $this->slug . '_ignore';

            if (isset($options['product_id'])) {
                $this->product_id = $options['product_id'];
            }

            $this->removeChildTheme();

            add_action('admin_init', [$this, 'checkMerlink']);
        }


        public function register_import_files()
        {
            $filter = $this->theme->template . 'merlin_import_files';
            $this->import_files = $this->validate_import_file_info(apply_filters($filter, $this->import_files_data));
        }


        public function _ajax_activate_license()
        {

            if (!check_ajax_referer('merlin_nonce', 'wpnonce')) {
                wp_send_json(
                    array(
                        'success' => false,
                        'message' => esc_html__('Yikes! The theme activation failed. Please try again or contact support.', '@@textdomain'),
                    )
                );
            }

            if (empty($_POST['license_key'])) {
                wp_send_json(
                    array(
                        'success' => false,
                        'message' => esc_html__('Please add your license key before attempting to activate one.', '@@textdomain'),
                    )
                );
            }

            $license_key = sanitize_key($_POST['license_key']);
            $license_email = sanitize_email($_POST['license_email']);

            if (empty($license_email) || !filter_var($license_email, FILTER_VALIDATE_EMAIL)) {
                wp_send_json([
                    'message' => esc_html__("Email is require", 'catsplugin'),
                    "success" => false
                ]);
            }

            $dataRequest = [
                'domain' => get_home_url(),
                'product_id' => $this->product_id,
                'email' => $license_email,
                'key' => $license_key
            ];

            $url = 'https://catsplugins.com/wp-json/catsplugins/v1/license/envato/?' . http_build_query($dataRequest);

            $res = wp_remote_get($url);
            $body = json_decode($res['body'], true);

            if ($res['response']['code'] == 200) {

                update_option($this->merlin_url . "_active_status", [
                    'status' => 'complete',
                    'key' => $license_key,
                    'email' => $license_email
                ]);

                wp_send_json([
                    'message' => esc_html__("Active plugin complete.", 'catsplugin'),
                    "success" => true,
                    $res
                ]);


            } else {
                update_option($this->merlin_url . "_active_status", false);

                wp_send_json([
                    'message' => $body['message'],
                    "success" => false,
                    $dataRequest,
                    $res
                ]);
            }

        }

        private function removeChildTheme()
        {
            add_filter($this->theme->template . '_merlin_steps', function ($steps) {
                if (isset($steps['child'])) unset($steps['child']);

                return $steps;
            }, 9999, 1);
        }


        private function is_theme_registered()
        {
            $is_registered = get_option($this->edd_theme_slug . '_license_key_status', false) === 'valid';
            return apply_filters('merlin_is_theme_registered', $is_registered);
        }

        /**
         * Theme EDD license step.
         */
        protected function license()
        {
            $is_theme_registered = $this->is_theme_registered();
            $action_url = $this->theme_license_help_url;
            $required = $this->license_required;

            $is_theme_registered_class = ($is_theme_registered) ? ' is-registered' : null;

            // Theme Name.
            $theme = ucfirst($this->theme);

            // Remove "Child" from the current theme name, if it's installed.
            $theme = str_replace(' Child', '', $theme);

            // Strings passed in from the config file.
            $strings = $this->strings;

            // Text strings.
            $header = !$is_theme_registered ? $strings['license-header%s'] : $strings['license-header-success%s'];
            $action = $strings['license-tooltip'];
            $label = $strings['license-label'];
            $skip = $strings['btn-license-skip'];
            $next = $strings['btn-next'];
            $paragraph = !$is_theme_registered ? $strings['license%s'] : $strings['license-success%s'];
            $install = $strings['btn-license-activate'];
            ?>

            <div class="merlin__content--transition">

                <?php echo wp_kses($this->svg(array('icon' => 'license')), $this->svg_allowed_html()); ?>

                <svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>

                <h1><?php echo esc_html(sprintf($header, $theme)); ?></h1>

                <p id="license-text"><?php echo esc_html(sprintf($paragraph, $theme)); ?></p>

                <?php if (!$is_theme_registered) : ?>
                    <div class="merlin__content--license-key">
                        <label for="license-key"><?php echo esc_html($label); ?></label>

                        <div class="merlin__content--license-key-wrapper">
                            <input placeholder="Email" type="text" id="license-email" class="js-license-email"
                                   autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <input placeholder="Purchase Code" type="text" id="license-key" class="js-license-key"
                                   autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <?php if (!empty($action_url)) : ?>
                                <a href="<?php echo esc_url($action_url); ?>" alt="<?php echo esc_attr($action); ?>"
                                   target="_blank">
								<span class="hint--top" aria-label="<?php echo esc_attr($action); ?>">
									<?php echo wp_kses($this->svg(array('icon' => 'help')), $this->svg_allowed_html()); ?>
								</span>
                                </a>
                            <?php endif ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <footer class="merlin__content__footer <?php echo esc_attr($is_theme_registered_class); ?>">

                <?php if (!$is_theme_registered) : ?>

                    <?php if (!$required) : ?>
                        <a href="<?php echo esc_url($this->step_next_link()); ?>"
                           class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html($skip); ?></a>
                    <?php endif ?>

                    <a href="<?php echo esc_url($this->step_next_link()); ?>"
                       class="merlin__button merlin__button--next button-next js-merlin-license-activate-button"
                       data-callback="activate_license">
                        <span class="merlin__button--loading__text"><?php echo esc_html($install); ?></span>
                        <?php echo wp_kses($this->loading_spinner(), $this->loading_spinner_allowed_html()); ?>
                    </a>

                <?php else : ?>
                    <a href="<?php echo esc_url($this->step_next_link()); ?>"
                       class="merlin__button merlin__button--next merlin__button--proceed merlin__button--colorchange"><?php echo esc_html($next); ?></a>
                <?php endif; ?>
                <?php wp_nonce_field('merlin'); ?>
            </footer>
            <?php
            $this->logger->debug(__('The license activation step has been displayed', '@@textdomain'));
        }


        public function fakeWpTheme($pluginPath)
        {
            if (!function_exists('get_plugin_data')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }

            $pluginPath = get_plugin_data($pluginPath, false, false);

            $theme = new Class
            {
                public $theme_root;
                public $stylesheet;
                public $template;
                public $name;
                public $author;
                public $version;

                public function __toString()
                {
                    return (string)__($this->name);
                }
            };
            $theme->theme_root = get_theme_root();
            $theme->stylesheet = $pluginPath['TextDomain'];
            $theme->template = sanitize_title($pluginPath['Name']);
            $theme->name = $pluginPath['Name'];
            $theme->author = $pluginPath['Author'];
            $theme->version = $pluginPath['Version'];

            return $theme;
        }


        private function extractConfigs($config)
        {
            $default = [
                'directory' => 'merlin',
                'merlin_url' => 'cats-merlin',
                'dev_mode' => false,
                'license_step' => false,
                'license_required' => true,
                'license_help_url' => '',
                'edd_remote_api_url' => '',
                'edd_item_name' => '',
                'edd_theme_slug' => 'cats-merlin',
                'parent_slug' => '',
                'force_active' => '',
            ];

            add_filter($config['merlin_url'], function () use ($config) {
                return admin_url() . 'admin.php?page=' . $config['merlin_url'];
            });

            return array_merge($default, $config);
        }

        private function extractOptions($options)
        {
            $default = [
                'admin-menu' => esc_html(__('Demo Setup')),
                /* translators: 1: Title Tag 2: Theme Name 3: Closing Title Tag */
                'title%s%s%s%s' => esc_html__('%1$s%2$s Plugins &lsaquo; Plugin Setup: %3$s%4$s', 'catsplugin'),
                'return-to-dashboard' => esc_html__('Return to the dashboard', 'catsplugin'),
                'ignore' => esc_html__('Disable this wizard', 'catsplugin'),
                'btn-skip' => esc_html__('Skip', 'catsplugin'),
                'btn-next' => esc_html__('Next', 'catsplugin'),
                'btn-start' => esc_html__('Start', 'catsplugin'),
                'btn-no' => esc_html__('Cancel', 'catsplugin'),
                'btn-plugins-install' => esc_html__('Install', 'catsplugin'),
                'btn-child-install' => esc_html__('Install', 'catsplugin'),
                'btn-content-install' => esc_html__('Install', 'catsplugin'),
                'btn-import' => esc_html__('Import', 'catsplugin'),
                'btn-license-activate' => esc_html__('Activate', 'catsplugin'),
                'btn-license-skip' => esc_html__('Later', 'catsplugin'),
                /* translators: Theme Name */
                'license-header%s' => esc_html__('Activate %s', 'catsplugin'),
                /* translators: Theme Name */
                'license-header-success%s' => esc_html__('%s is Activated', 'catsplugin'),
                /* translators: Theme Name */
                'license%s' => esc_html__('Enter your license key to enable remote updates and theme support.', 'catsplugin'),
                'license-label' => esc_html__('License key', 'catsplugin'),
                'license-success%s' => esc_html__('The theme is already registered, so you can go to the next step!', 'catsplugin'),
                'license-json-success%s' => esc_html__('Your theme is activated! Remote updates and theme support are enabled.', 'catsplugin'),
                'license-tooltip' => esc_html__('Need help?', 'catsplugin'),
                /* translators: Theme Name */
                'welcome-header%s' => esc_html__('Welcome to %s', 'catsplugin'),
                'welcome-header-success%s' => esc_html__('Hi. Welcome back', 'catsplugin'),
                'welcome%s' => esc_html__('This wizard will set up your theme, install plugins, and import content. It is optional & should take only a few minutes.', 'catsplugin'),
                'welcome-success%s' => esc_html__('You may have already run this theme setup wizard. If you would like to proceed anyway, click on the "Start" button below.', 'catsplugin'),

                'plugins-header' => esc_html__('Install Plugins', 'catsplugin'),
                'plugins-header-success' => esc_html__('You\'re up to speed!', 'catsplugin'),
                'plugins' => esc_html__('Let\'s install some essential WordPress plugins to get your site up to speed.', 'catsplugin'),
                'plugins-success%s' => esc_html__('The required WordPress plugins are all installed and up to date. Press "Next" to continue the setup wizard.', 'catsplugin'),
                'plugins-action-link' => esc_html__('Advanced', 'catsplugin'),
                'import-header' => esc_html__('Import Content', 'catsplugin'),
                'import' => esc_html__('Let\'s import content to your website, to help you get familiar with the theme.', 'catsplugin'),
                'import-action-link' => esc_html__('Advanced', 'catsplugin'),
                'ready-header' => esc_html__('All done. Have fun!', 'catsplugin'),
                /* translators: Theme Author */
                'ready%s' => esc_html__('Your theme has been all set up. Enjoy your new theme by %s.', 'catsplugin'),
                'ready-action-link' => esc_html__('Extras', 'catsplugin'),
                'ready-big-button' => esc_html__('View your website', 'catsplugin'),
                'ready-link-1' => sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://wordpress.org/support/', esc_html__('Explore WordPress', 'catsplugin')),
                'ready-link-2' => sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://themebeans.com/contact/', esc_html__('Get Theme Support', 'catsplugin')),
                'ready-link-3' => sprintf('<a href="%1$s">%2$s</a>', admin_url('customize.php'), esc_html__('Start Customizing', 'catsplugin')),
            ];

            return array_merge($default, $options);
        }

        public function import_finished()
        {
            update_option($this->merlin_url, 'completed');
            parent::import_finished();
        }

        public function checkMerlink()
        {

            if (is_admin() && !wp_doing_ajax()) {

                if (isset($_GET['page']) && $_GET['page'] == $this->merlin_url) {

                } else {
                    if (isset($this->config['force_active']) && $this->config['force_active'] == true) {
                        $statusActive = get_option($this->merlin_url . "_active_status");

                        if (is_array($statusActive) && isset($statusActive['status']) && $statusActive['status'] == 'complete') {

                        } else {
                            $url = get_admin_url() . "admin.php?page={$this->merlin_url}";
                            wp_redirect($url, $status = 302);
                            exit();
                        }
                    }

                    $completed = get_option($this->merlin_url);
                    if ($completed != 'completed' && $completed != 'redirected') {
                        update_option($this->merlin_url, 'redirected');
                        ob_start();

                        $url = get_admin_url() . "admin.php?page={$this->merlin_url}";
                        wp_redirect($url, $status = 302);
                        exit();
                    }
                }
            }
        }
    }
}