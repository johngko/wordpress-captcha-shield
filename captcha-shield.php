<?php
/*
 * Plugin Name:       数字盾验验证码
 * Plugin URI:        https://docs.255705.com/
 * Description:       集成数字盾验智能验证码服务，保护您的网站免受自动化攻击。支持登录、注册、评论表单验证码集成。
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Captcha Shield
 * Author URI:        https://255705.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       captcha-shield
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CAPTCHA_SHIELD_VERSION', '1.0.0' );
define( 'CAPTCHA_SHIELD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAPTCHA_SHIELD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CAPTCHA_SHIELD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once CAPTCHA_SHIELD_PLUGIN_DIR . 'includes/class-captcha-shield.php';

function captcha_shield_init() {
    return Captcha_Shield::get_instance();
}

captcha_shield_init();

// 在插件列表页面添加设置链接
add_filter( 'plugin_action_links_' . CAPTCHA_SHIELD_PLUGIN_BASENAME, 'captcha_shield_add_settings_link' );

function captcha_shield_add_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=captcha-shield' ) . '">' . __( '设置', 'captcha-shield' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
