<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Captcha_Shield {

    private static $instance = null;

    private $settings;
    private $frontend;
    private $verify;
    private $forms;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once CAPTCHA_SHIELD_PLUGIN_DIR . 'includes/class-captcha-shield-settings.php';
        require_once CAPTCHA_SHIELD_PLUGIN_DIR . 'includes/class-captcha-shield-frontend.php';
        require_once CAPTCHA_SHIELD_PLUGIN_DIR . 'includes/class-captcha-shield-verify.php';
        require_once CAPTCHA_SHIELD_PLUGIN_DIR . 'includes/class-captcha-shield-forms.php';

        $this->settings = new Captcha_Shield_Settings();
        $this->verify   = new Captcha_Shield_Verify( $this->settings );
        $this->frontend = new Captcha_Shield_Frontend( $this->settings, $this->verify );
        $this->forms    = new Captcha_Shield_Forms( $this->settings, $this->frontend, $this->verify );
    }

    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'admin_notices', array( $this, 'admin_notice' ) );
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'captcha-shield',
            false,
            dirname( CAPTCHA_SHIELD_PLUGIN_BASENAME ) . '/languages'
        );
    }

    public function admin_notice() {
        $options = get_option( 'captcha_shield_options', array() );
        if ( empty( $options['app_key'] ) ) {
            $settings_url = admin_url( 'options-general.php?page=captcha-shield' );
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . sprintf(
                esc_html__( '数字盾验验证码插件已激活，请前往 %s 配置 App Key。', 'captcha-shield' ),
                '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( '设置页面', 'captcha-shield' ) . '</a>'
            ) . '</p>';
            echo '</div>';
        }
    }

    public function get_settings() {
        return $this->settings;
    }

    public function get_frontend() {
        return $this->frontend;
    }

    public function get_verify() {
        return $this->verify;
    }

    public function get_forms() {
        return $this->forms;
    }
}
