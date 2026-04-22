<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Captcha_Shield_Settings {

    private $option_name = 'captcha_shield_options';

    private $default_options = array(
        'app_key'          => '',
        'app_secret'       => '',
        'api_base'         => 'https://api.255705.com',
        'lang'             => 'zh-CN',
        'enable_login'     => false,
        'enable_register'  => false,
        'enable_comment'   => false,
    );

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function get_options() {
        $options = get_option( $this->option_name, array() );
        return wp_parse_args( $options, $this->default_options );
    }

    public function get_option( $key ) {
        $options = $this->get_options();
        return isset( $options[ $key ] ) ? $options[ $key ] : null;
    }

    public function add_settings_page() {
        add_options_page(
            __( '数字盾验设置', 'captcha-shield' ),
            __( '数字盾验', 'captcha-shield' ),
            'manage_options',
            'captcha-shield',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'captcha_shield_group',
            $this->option_name,
            array( $this, 'sanitize_options' )
        );

        add_settings_section(
            'captcha_shield_basic',
            __( '基本设置', 'captcha-shield' ),
            array( $this, 'render_basic_section' ),
            'captcha-shield'
        );

        add_settings_field(
            'app_key',
            __( 'App Key', 'captcha-shield' ),
            array( $this, 'render_app_key_field' ),
            'captcha-shield',
            'captcha_shield_basic'
        );

        add_settings_field(
            'app_secret',
            __( 'App Secret', 'captcha-shield' ),
            array( $this, 'render_app_secret_field' ),
            'captcha-shield',
            'captcha_shield_basic'
        );

        add_settings_field(
            'api_base',
            __( 'API 服务器地址', 'captcha-shield' ),
            array( $this, 'render_api_base_field' ),
            'captcha-shield',
            'captcha_shield_basic'
        );

        add_settings_field(
            'lang',
            __( '语言', 'captcha-shield' ),
            array( $this, 'render_lang_field' ),
            'captcha-shield',
            'captcha_shield_basic'
        );

        add_settings_section(
            'captcha_shield_forms',
            __( '表单集成', 'captcha-shield' ),
            array( $this, 'render_forms_section' ),
            'captcha-shield'
        );

        add_settings_field(
            'enable_login',
            __( '登录表单', 'captcha-shield' ),
            array( $this, 'render_enable_login_field' ),
            'captcha-shield',
            'captcha_shield_forms'
        );

        add_settings_field(
            'enable_register',
            __( '注册表单', 'captcha-shield' ),
            array( $this, 'render_enable_register_field' ),
            'captcha-shield',
            'captcha_shield_forms'
        );

        add_settings_field(
            'enable_comment',
            __( '评论表单', 'captcha-shield' ),
            array( $this, 'render_enable_comment_field' ),
            'captcha-shield',
            'captcha_shield_forms'
        );
    }

    public function sanitize_options( $input ) {
        $sanitized = array();

        $sanitized['app_key']    = sanitize_text_field( $input['app_key'] );
        $sanitized['app_secret'] = sanitize_text_field( $input['app_secret'] );
        $sanitized['api_base']   = esc_url_raw( $input['api_base'] );
        $sanitized['lang']       = sanitize_text_field( $input['lang'] );

        $sanitized['enable_login']    = ! empty( $input['enable_login'] );
        $sanitized['enable_register'] = ! empty( $input['enable_register'] );
        $sanitized['enable_comment']  = ! empty( $input['enable_comment'] );

        if ( empty( $sanitized['api_base'] ) ) {
            $sanitized['api_base'] = 'https://api.255705.com';
        }

        if ( ! in_array( $sanitized['lang'], array( 'zh-CN', 'en-US' ), true ) ) {
            $sanitized['lang'] = 'zh-CN';
        }

        return $sanitized;
    }

    public function render_basic_section() {
        echo '<p>' . esc_html__( '配置数字盾验验证码服务的基本参数。请在控制台获取 App Key 和 App Secret。', 'captcha-shield' ) . '</p>';
        echo '<p>';
        echo '<a href="https://255705.com/console" target="_blank" class="button button-secondary" style="margin-right: 10px;">';
        echo '<span class="dashicons dashicons-admin-site" style="margin-top: 3px; margin-right: 5px;"></span>';
        echo esc_html__( '打开控制台', 'captcha-shield' );
        echo '</a>';
        echo '<a href="https://docs.255705.com/start/quickstart" target="_blank" class="button button-secondary">';
        echo '<span class="dashicons dashicons-book" style="margin-top: 3px; margin-right: 5px;"></span>';
        echo esc_html__( '查看接入文档', 'captcha-shield' );
        echo '</a>';
        echo '</p>';
    }

    public function render_forms_section() {
        echo '<p>' . esc_html__( '选择需要集成验证码的 WordPress 表单。', 'captcha-shield' ) . '</p>';
    }

    public function render_app_key_field() {
        $options = $this->get_options();
        $value   = $options['app_key'];
        echo '<input type="text" id="captcha_shield_app_key" name="captcha_shield_options[app_key]" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="ak_your_app_key" />';
        echo '<p class="description">' . esc_html__( '从数字盾验控制台获取的应用 App Key。', 'captcha-shield' ) . '</p>';
    }

    public function render_app_secret_field() {
        $options = $this->get_options();
        $value   = $options['app_secret'];
        echo '<input type="password" id="captcha_shield_app_secret" name="captcha_shield_options[app_secret]" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="' . esc_attr__( '输入 App Secret', 'captcha-shield' ) . '" />';
        echo '<p class="description">' . esc_html__( '从数字盾验控制台获取的应用 App Secret。仅用于服务端签名验证，不会暴露到前端。', 'captcha-shield' ) . '</p>';
    }

    public function render_api_base_field() {
        $options = $this->get_options();
        $value   = $options['api_base'];
        echo '<input type="url" id="captcha_shield_api_base" name="captcha_shield_options[api_base]" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( '数字盾验 API 服务器地址，默认为 https://api.255705.com', 'captcha-shield' ) . '</p>';
    }

    public function render_lang_field() {
        $options = $this->get_options();
        $value   = $options['lang'];
        echo '<select id="captcha_shield_lang" name="captcha_shield_options[lang]">';
        echo '<option value="zh-CN"' . selected( $value, 'zh-CN', false ) . '>' . esc_html__( '简体中文', 'captcha-shield' ) . '</option>';
        echo '<option value="en-US"' . selected( $value, 'en-US', false ) . '>' . esc_html__( 'English', 'captcha-shield' ) . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__( '验证码组件显示语言。', 'captcha-shield' ) . '</p>';
    }

    public function render_enable_login_field() {
        $options = $this->get_options();
        $checked = $options['enable_login'];
        echo '<label><input type="checkbox" name="captcha_shield_options[enable_login]" value="1"' . checked( $checked, true, false ) . ' />' . esc_html__( '在 WordPress 登录表单中启用验证码', 'captcha-shield' ) . '</label>';
    }

    public function render_enable_register_field() {
        $options = $this->get_options();
        $checked = $options['enable_register'];
        echo '<label><input type="checkbox" name="captcha_shield_options[enable_register]" value="1"' . checked( $checked, true, false ) . ' />' . esc_html__( '在 WordPress 注册表单中启用验证码', 'captcha-shield' ) . '</label>';
    }

    public function render_enable_comment_field() {
        $options = $this->get_options();
        $checked = $options['enable_comment'];
        echo '<label><input type="checkbox" name="captcha_shield_options[enable_comment]" value="1"' . checked( $checked, true, false ) . ' />' . esc_html__( '在 WordPress 评论表单中启用验证码', 'captcha-shield' ) . '</label>';
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="captcha-shield-quickstart">
                <h2><?php esc_html_e( '快速入门指南', 'captcha-shield' ); ?></h2>
                <p class="captcha-shield-intro"><?php esc_html_e( '只需 3 步，即可完成数字盾验验证码的接入：', 'captcha-shield' ); ?></p>
                
                <div class="captcha-shield-steps">
                    <div class="captcha-shield-step">
                        <span class="captcha-shield-step-number">1</span>
                        <div class="captcha-shield-step-content">
                            <h3><?php esc_html_e( '注册账号并创建应用', 'captcha-shield' ); ?></h3>
                            <p><?php esc_html_e( '访问数字盾验控制台，注册账号后创建一个新应用。', 'captcha-shield' ); ?></p>
                            <a href="https://255705.com/console" target="_blank" class="button button-primary">
                                <?php esc_html_e( '前往控制台', 'captcha-shield' ); ?>
                                <span class="dashicons dashicons-external" style="margin-top: 3px; margin-left: 5px;"></span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="captcha-shield-step">
                        <span class="captcha-shield-step-number">2</span>
                        <div class="captcha-shield-step-content">
                            <h3><?php esc_html_e( '获取 App Key 和 App Secret', 'captcha-shield' ); ?></h3>
                            <p><?php esc_html_e( '在控制台的「应用设置」中，复制 App Key 和 App Secret 到下方表单。', 'captcha-shield' ); ?></p>
                        </div>
                    </div>
                    
                    <div class="captcha-shield-step">
                        <span class="captcha-shield-step-number">3</span>
                        <div class="captcha-shield-step-content">
                            <h3><?php esc_html_e( '启用验证码保护', 'captcha-shield' ); ?></h3>
                            <p><?php esc_html_e( '在下方「表单集成」区域勾选需要保护的表单，保存设置即可生效。', 'captcha-shield' ); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="captcha-shield-help-links">
                    <strong><?php esc_html_e( '相关资源：', 'captcha-shield' ); ?></strong>
                    <a href="https://docs.255705.com/" target="_blank"><?php esc_html_e( '官方文档', 'captcha-shield' ); ?></a> |
                    <a href="https://docs.255705.com/start/quickstart" target="_blank"><?php esc_html_e( '快速接入指南', 'captcha-shield' ); ?></a> |
                    <a href="https://docs.255705.com/deploy/php" target="_blank"><?php esc_html_e( 'PHP 部署文档', 'captcha-shield' ); ?></a>
                </div>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields( 'captcha_shield_group' );
                do_settings_sections( 'captcha-shield' );
                submit_button( __( '保存设置', 'captcha-shield' ) );
                ?>
            </form>
            
            <div class="captcha-shield-usage">
                <h2><?php esc_html_e( '使用说明', 'captcha-shield' ); ?></h2>
                <h3><?php esc_html_e( '短代码使用', 'captcha-shield' ); ?></h3>
                <p><?php esc_html_e( '在任意文章或页面中添加以下短代码即可显示验证码：', 'captcha-shield' ); ?></p>
                <code>[captcha_shield]</code>
                <p><?php esc_html_e( '支持以下属性：', 'captcha-shield' ); ?></p>
                <ul>
                    <li><code>lang</code> - <?php esc_html_e( '语言设置，可选 zh-CN（默认）或 en-US', 'captcha-shield' ); ?></li>
                    <li><code>visible</code> - <?php esc_html_e( '是否可见，可选 true（默认）或 false', 'captcha-shield' ); ?></li>
                </ul>
                <p><?php esc_html_e( '示例：', 'captcha-shield' ); ?></p>
                <code>[captcha_shield lang="en-US" visible="true"]</code>
                
                <h3><?php esc_html_e( '表单集成', 'captcha-shield' ); ?></h3>
                <p><?php esc_html_e( '启用表单集成后，验证码将自动添加到对应的 WordPress 表单中，用户必须完成验证才能提交。', 'captcha-shield' ); ?></p>
                <ul>
                    <li><strong><?php esc_html_e( '登录表单', 'captcha-shield' ); ?></strong> - <?php esc_html_e( '保护 WordPress 后台登录', 'captcha-shield' ); ?></li>
                    <li><strong><?php esc_html_e( '注册表单', 'captcha-shield' ); ?></strong> - <?php esc_html_e( '保护 WordPress 用户注册', 'captcha-shield' ); ?></li>
                    <li><strong><?php esc_html_e( '评论表单', 'captcha-shield' ); ?></strong> - <?php esc_html_e( '保护 WordPress 文章评论', 'captcha-shield' ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_captcha-shield' !== $hook ) {
            return;
        }
        wp_enqueue_style(
            'captcha-shield-admin',
            CAPTCHA_SHIELD_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CAPTCHA_SHIELD_VERSION
        );
    }
}
