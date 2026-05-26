<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Captcha_Shield_Frontend {

    private $settings;
    private $verify;
    private $has_captcha = false;

    public function __construct( Captcha_Shield_Settings $settings, Captcha_Shield_Verify $verify ) {
        $this->settings = $settings;
        $this->verify   = $verify;

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_shortcode( 'captcha_shield', array( $this, 'render_shortcode' ) );
    }

    public function enqueue_scripts() {
        $options = $this->settings->get_options();

        if ( empty( $options['app_key'] ) ) {
            return;
        }

        $should_load = $this->should_load_scripts();

        if ( ! $should_load ) {
            return;
        }

        wp_enqueue_style(
            'captcha-shield-frontend',
            CAPTCHA_SHIELD_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CAPTCHA_SHIELD_VERSION
        );

        wp_enqueue_script(
            'captcha-shield-widget',
            'https://cdn.255705.com/captcha-widget.umd.cjs',
            array(),
            null,
            true
        );

        wp_enqueue_script(
            'captcha-shield-frontend',
            CAPTCHA_SHIELD_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery', 'captcha-shield-widget' ),
            CAPTCHA_SHIELD_VERSION,
            true
        );

        wp_localize_script( 'captcha-shield-frontend', 'captchaShield', array(
            'ajax_url'     => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( 'captcha_shield_verify' ),
            'app_key'      => $options['app_key'],
            'api_base'     => $options['api_base'],
            'lang'         => $options['lang'],
            'display_mode' => $options['display_mode'],
        ) );
    }

    private function should_load_scripts() {
        if ( is_login() ) {
            $options = $this->settings->get_options();
            if ( ! empty( $options['enable_login'] ) || ! empty( $options['enable_register'] ) ) {
                return true;
            }
        }

        if ( is_singular() && has_shortcode( get_post()->post_content, 'captcha_shield' ) ) {
            return true;
        }

        if ( ! is_admin() && ( comments_open() || pings_open() ) ) {
            $options = $this->settings->get_options();
            if ( ! empty( $options['enable_comment'] ) ) {
                return true;
            }
        }

        return false;
    }

    public function render_shortcode( $atts = array() ) {
        $options = $this->settings->get_options();

        if ( empty( $options['app_key'] ) ) {
            return '';
        }

        $atts = shortcode_atts( array(
            'lang'           => $options['lang'],
            'visible'        => 'true',
            'display_mode'   => $options['display_mode'],
            'bind_element'   => '',
            'submit_element' => '',
        ), $atts, 'captcha_shield' );

        $this->has_captcha = true;

        return $this->render_captcha_widget( $atts );
    }

    public function render_captcha_widget( $atts = array() ) {
        $options = $this->settings->get_options();

        if ( empty( $options['app_key'] ) ) {
            return '';
        }

        $app_key      = esc_attr( $options['app_key'] );
        $api_base     = esc_attr( $options['api_base'] );
        $lang         = esc_attr( isset( $atts['lang'] ) ? $atts['lang'] : $options['lang'] );
        $visible      = isset( $atts['visible'] ) ? filter_var( $atts['visible'], FILTER_VALIDATE_BOOLEAN ) : true;
        $display_mode = esc_attr( isset( $atts['display_mode'] ) ? $atts['display_mode'] : $options['display_mode'] );
        $bind_element = esc_attr( isset( $atts['bind_element'] ) ? $atts['bind_element'] : '' );
        $submit_element = esc_attr( isset( $atts['submit_element'] ) ? $atts['submit_element'] : '' );

        $html  = '<div class="captcha-shield-container">';
        $html .= '<captcha-widget';
        $html .= ' app-key="' . $app_key . '"';
        $html .= ' api-base="' . $api_base . '"';
        $html .= ' lang="' . $lang . '"';
        if ( $visible ) {
            $html .= ' visible';
        }
        $html .= ' display-mode="' . $display_mode . '"';
        if ( ! empty( $bind_element ) ) {
            $html .= ' bind-element="' . $bind_element . '"';
        }
        if ( ! empty( $submit_element ) ) {
            $html .= ' submit-element="' . $submit_element . '"';
        }
        $html .= '></captcha-widget>';
        $html .= '<input type="hidden" name="captcha_shield_lot_number" value="" />';
        $html .= '<input type="hidden" name="captcha_shield_sign_token" value="" />';
        $html .= '</div>';

        return $html;
    }

    public function has_captcha_on_page() {
        return $this->has_captcha;
    }
}
