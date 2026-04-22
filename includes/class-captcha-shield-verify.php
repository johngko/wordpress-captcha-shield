<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Captcha_Shield_Verify {

    private $settings;

    public function __construct( Captcha_Shield_Settings $settings = null ) {
        $this->settings = $settings;
        add_action( 'wp_ajax_captcha_shield_verify', array( $this, 'ajax_verify' ) );
        add_action( 'wp_ajax_nopriv_captcha_shield_verify', array( $this, 'ajax_verify' ) );
    }

    public function ajax_verify() {
        check_ajax_referer( 'captcha_shield_verify', 'nonce' );

        $lot_number = isset( $_POST['lot_number'] ) ? sanitize_text_field( $_POST['lot_number'] ) : '';
        $sign_token = isset( $_POST['sign_token'] ) ? sanitize_text_field( $_POST['sign_token'] ) : '';

        if ( empty( $lot_number ) || empty( $sign_token ) ) {
            wp_send_json_error( array(
                'message' => __( '缺少验证参数', 'captcha-shield' ),
            ) );
        }

        $result = $this->verify_signature( $lot_number, $sign_token );

        if ( $result ) {
            wp_send_json_success( array(
                'message' => __( '验证成功', 'captcha-shield' ),
            ) );
        } else {
            wp_send_json_error( array(
                'message' => __( '验证码验证失败', 'captcha-shield' ),
            ) );
        }
    }

    public function verify_signature( $lot_number, $sign_token ) {
        if ( null === $this->settings ) {
            return false;
        }

        $app_secret = $this->settings->get_option( 'app_secret' );

        if ( empty( $app_secret ) ) {
            return false;
        }

        $expected = hash_hmac( 'sha256', $lot_number, $app_secret );

        return hash_equals( $expected, $sign_token );
    }

    public function verify_request( $lot_number, $sign_token ) {
        return $this->verify_signature( $lot_number, $sign_token );
    }
}
