<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Captcha_Shield_Forms {

    private $settings;
    private $frontend;
    private $verify;

    public function __construct( Captcha_Shield_Settings $settings, Captcha_Shield_Frontend $frontend, Captcha_Shield_Verify $verify ) {
        $this->settings = $settings;
        $this->frontend = $frontend;
        $this->verify   = $verify;

        $this->init_login_hooks();
        $this->init_register_hooks();
        $this->init_comment_hooks();
    }

    private function init_login_hooks() {
        add_action( 'login_form', array( $this, 'add_login_captcha' ) );
        add_filter( 'wp_authenticate_user', array( $this, 'verify_login_captcha' ), 10, 1 );
    }

    private function init_register_hooks() {
        add_action( 'register_form', array( $this, 'add_register_captcha' ) );
        add_filter( 'registration_errors', array( $this, 'verify_register_captcha' ), 10, 3 );
    }

    private function init_comment_hooks() {
        add_action( 'comment_form_submit_field', array( $this, 'add_comment_captcha' ) );
        add_filter( 'pre_comment_on_post', array( $this, 'verify_comment_captcha' ), 10, 1 );
    }

    private function should_enable( $option_key ) {
        $options = $this->settings->get_options();
        return ! empty( $options[ $option_key ] ) && ! empty( $options['app_key'] );
    }

    public function add_login_captcha() {
        if ( ! $this->should_enable( 'enable_login' ) ) {
            return;
        }
        echo $this->frontend->render_captcha_widget();
    }

    public function verify_login_captcha( $user ) {
        if ( ! $this->should_enable( 'enable_login' ) ) {
            return $user;
        }

        $lot_number = isset( $_POST['captcha_shield_lot_number'] ) ? sanitize_text_field( $_POST['captcha_shield_lot_number'] ) : '';
        $sign_token = isset( $_POST['captcha_shield_sign_token'] ) ? sanitize_text_field( $_POST['captcha_shield_sign_token'] ) : '';

        if ( empty( $lot_number ) || empty( $sign_token ) ) {
            return new WP_Error( 'captcha_shield_missing', __( '请先完成验证码验证', 'captcha-shield' ) );
        }

        if ( ! $this->verify->verify_request( $lot_number, $sign_token ) ) {
            return new WP_Error( 'captcha_shield_failed', __( '验证码验证失败，请重试', 'captcha-shield' ) );
        }

        return $user;
    }

    public function add_register_captcha() {
        if ( ! $this->should_enable( 'enable_register' ) ) {
            return;
        }
        echo $this->frontend->render_captcha_widget();
    }

    public function verify_register_captcha( $errors, $sanitized_user_login, $user_email ) {
        if ( ! $this->should_enable( 'enable_register' ) ) {
            return $errors;
        }

        $lot_number = isset( $_POST['captcha_shield_lot_number'] ) ? sanitize_text_field( $_POST['captcha_shield_lot_number'] ) : '';
        $sign_token = isset( $_POST['captcha_shield_sign_token'] ) ? sanitize_text_field( $_POST['captcha_shield_sign_token'] ) : '';

        if ( empty( $lot_number ) || empty( $sign_token ) ) {
            $errors->add( 'captcha_shield_missing', __( '请先完成验证码验证', 'captcha-shield' ) );
            return $errors;
        }

        if ( ! $this->verify->verify_request( $lot_number, $sign_token ) ) {
            $errors->add( 'captcha_shield_failed', __( '验证码验证失败，请重试', 'captcha-shield' ) );
        }

        return $errors;
    }

    public function add_comment_captcha( $submit_field ) {
        if ( ! $this->should_enable( 'enable_comment' ) ) {
            return $submit_field;
        }

        $captcha_html = $this->frontend->render_captcha_widget();

        return $captcha_html . $submit_field;
    }

    public function verify_comment_captcha( $comment_post_id ) {
        if ( ! $this->should_enable( 'enable_comment' ) ) {
            return $comment_post_id;
        }

        $lot_number = isset( $_POST['captcha_shield_lot_number'] ) ? sanitize_text_field( $_POST['captcha_shield_lot_number'] ) : '';
        $sign_token = isset( $_POST['captcha_shield_sign_token'] ) ? sanitize_text_field( $_POST['captcha_shield_sign_token'] ) : '';

        if ( empty( $lot_number ) || empty( $sign_token ) ) {
            wp_die( esc_html__( '请先完成验证码验证', 'captcha-shield' ) );
        }

        if ( ! $this->verify->verify_request( $lot_number, $sign_token ) ) {
            wp_die( esc_html__( '验证码验证失败，请重试', 'captcha-shield' ) );
        }

        return $comment_post_id;
    }
}
