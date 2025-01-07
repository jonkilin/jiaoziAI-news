<?php
/**
 * Plugin Name: JiaoziAI News
 * Plugin URI: https://jiaoziAI.com
 * Description: 一个由JiaoziAI提供的智能快讯插件
 * Version: 1.0.0
 * Requires at least: 5.6
 * Requires PHP: 7.2
 * Author: JiaoziAI
 * Author URI: https://jiaoziAI.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jiaoziAI-news
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

class JiaoziAI_News {
    private static $instance = null;
    private $version = '1.0.0';
    private $settings = [];

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->load_settings();
        $this->includes();
        $this->init_hooks();
    }

    private function define_constants() {
        define('JIAOZIAI_NEWS_VERSION', $this->version);
        define('JIAOZIAI_NEWS_FILE', __FILE__);
        define('JIAOZIAI_NEWS_PATH', plugin_dir_path(__FILE__));
        define('JIAOZIAI_NEWS_URL', plugin_dir_url(__FILE__));
    }

    private function load_settings() {
        $defaults = [
            'news_per_page' => 10,
            'excerpt_length' => 55,
            'show_author' => true,
            'show_date' => true,
            'show_category' => true,
            'show_tags' => true,
            'enable_social_share' => true,
            'enable_scroll_load' => false,
            'default_thumbnail' => '',
            'seo_title_format' => '%title% - %site_name%',
            'enable_breadcrumbs' => true
        ];

        $this->settings = wp_parse_args(
            get_option('jiaoziAI_news_settings', []),
            $defaults
        );
    }

    private function includes() {
        require_once JIAOZIAI_NEWS_PATH . 'includes/class-news-post-type.php';
        require_once JIAOZIAI_NEWS_PATH . 'includes/class-news-widget.php';
    }

    private function init_hooks() {
        register_activation_hook(JIAOZIAI_NEWS_FILE, [$this, 'activate']);
        register_deactivation_hook(JIAOZIAI_NEWS_FILE, [$this, 'deactivate']);

        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'init']);
        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        add_filter('template_include', [$this, 'template_loader']);
        add_filter('excerpt_length', [$this, 'custom_excerpt_length']);
        add_filter('excerpt_more', [$this, 'custom_excerpt_more']);

        add_action('wp_ajax_load_more_news', [$this, 'ajax_load_more_news']);
        add_action('wp_ajax_nopriv_load_more_news', [$this, 'ajax_load_more_news']);

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 
                  [$this, 'add_plugin_links']);

        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('post_type_link', [$this, 'custom_news_permalink'], 10, 2);
    }

    public function activate() {
        if (!get_option('jiaoziAI_news_installed')) {
            $this->create_tables();
            $this->set_default_options();
            update_option('jiaoziAI_news_installed', true);
            update_option('jiaoziAI_news_version', $this->version);
        }
        
        $this->add_rewrite_rules();
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jiaoziAI_news_stats (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            views int(11) DEFAULT 0,
            shares int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function set_default_options() {
        foreach ($this->settings as $key => $value) {
            update_option("jiaoziAI_news_{$key}", $value);
        }
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'jiaoziAI-news',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    public function init() {
    }

    public function admin_init() {
        register_setting('jiaoziAI_news_options', 'jiaoziAI_news_settings');
        
        add_settings_section(
            'jiaoziAI_news_general',
            __('常规设置', 'jiaoziAI-news'),
            [$this, 'render_section_general'],
            'jiaoziAI_news_settings'
        );

        $this->add_settings_fields();
    }

    public function add_admin_menu() {
        add_menu_page(
            __('快讯设置', 'jiaoziAI-news'),
            __('快讯设置', 'jiaoziAI-news'),
            'manage_options',
            'jiaoziAI-news-settings',
            [$this, 'render_settings_page'],
            'dashicons-megaphone',
            30
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'jiaoziAI_news_messages',
                'jiaoziAI_news_message',
                __('设置已保存。', 'jiaoziAI-news'),
                'updated'
            );
        }

        settings_errors('jiaoziAI_news_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('jiaoziAI_news_options');
                do_settings_sections('jiaoziAI_news_settings');
                submit_button(__('保存设置', 'jiaoziAI-news'));
                ?>
            </form>
        </div>
        <?php
    }

    public function render_section_general() {
        echo '<p>' . __('配置快讯插件的基本设置。', 'jiaoziAI-news') . '</p>';
    }

    private function add_settings_fields() {
        add_settings_field(
            'news_per_page',
            __('每页显示数量', 'jiaoziAI-news'),
            [$this, 'render_number_field'],
            'jiaoziAI_news_settings',
            'jiaoziAI_news_general',
            [
                'label_for' => 'news_per_page',
                'default' => 10,
                'min' => 1,
                'max' => 50
            ]
        );

        add_settings_field(
            'excerpt_length',
            __('摘要长度', 'jiaoziAI-news'),
            [$this, 'render_number_field'],
            'jiaoziAI_news_settings',
            'jiaoziAI_news_general',
            [
                'label_for' => 'excerpt_length',
                'default' => 55,
                'min' => 10,
                'max' => 300
            ]
        );

        $display_options = [
            'show_author' => __('显示作者', 'jiaoziAI-news'),
            'show_date' => __('显示日期', 'jiaoziAI-news'),
            'show_category' => __('显示分类', 'jiaoziAI-news'),
            'show_tags' => __('显示标签', 'jiaoziAI-news'),
            'enable_social_share' => __('启用社交分享', 'jiaoziAI-news'),
            'enable_scroll_load' => __('启用滚动加载', 'jiaoziAI-news'),
            'enable_breadcrumbs' => __('启用面包屑导航', 'jiaoziAI-news')
        ];

        foreach ($display_options as $key => $label) {
            add_settings_field(
                $key,
                $label,
                [$this, 'render_checkbox_field'],
                'jiaoziAI_news_settings',
                'jiaoziAI_news_general',
                [
                    'label_for' => $key,
                    'default' => true
                ]
            );
        }
    }

    public function render_number_field($args) {
        $option = get_option('jiaoziAI_news_settings');
        $value = isset($option[$args['label_for']]) ? 
                $option[$args['label_for']] : 
                $args['default'];
        ?>
        <input type="number" 
               id="<?php echo esc_attr($args['label_for']); ?>"
               name="jiaoziAI_news_settings[<?php echo esc_attr($args['label_for']); ?>]"
               value="<?php echo esc_attr($value); ?>"
               min="<?php echo esc_attr($args['min']); ?>"
               max="<?php echo esc_attr($args['max']); ?>"
               class="small-text">
        <?php
    }

    public function render_checkbox_field($args) {
        $option = get_option('jiaoziAI_news_settings');
        $value = isset($option[$args['label_for']]) ? 
                $option[$args['label_for']] : 
                $args['default'];
        ?>
        <input type="checkbox" 
               id="<?php echo esc_attr($args['label_for']); ?>"
               name="jiaoziAI_news_settings[<?php echo esc_attr($args['label_for']); ?>]"
               value="1"
               <?php checked($value, 1); ?>>
        <?php
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'jiaoziAI-news',
            JIAOZIAI_NEWS_URL . 'assets/css/style.css',
            [],
            $this->version
        );

        wp_enqueue_script(
            'jiaoziAI-news',
            JIAOZIAI_NEWS_URL . 'assets/js/script.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_localize_script('jiaoziAI-news', 'jiaoziAINews', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jiaoziAI-news-nonce'),
            'loading' => __('加载中...', 'jiaoziAI-news'),
            'loadMore' => __('加载更多', 'jiaoziAI-news'),
            'noMore' => __('没有更多内容', 'jiaoziAI-news'),
            'error' => __('加载失败，请重试', 'jiaoziAI-news'),
            'enableScrollLoad' => $this->get_option('enable_scroll_load'),
            'scanToShare' => __('扫描二维码分享到微信', 'jiaoziAI-news')
        ]);
    }

    public function admin_enqueue_scripts($hook) {
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        
        wp_enqueue_style(
            'jiaoziAI-news-admin',
            JIAOZIAI_NEWS_URL . 'assets/css/admin.css',
            ['wp-color-picker'],
            $this->version
        );

        wp_enqueue_script(
            'jiaoziAI-news-admin',
            JIAOZIAI_NEWS_URL . 'assets/js/admin.js',
            ['jquery', 'wp-color-picker'],
            $this->version,
            true
        );

        wp_localize_script('jiaoziAI-news-admin', 'jiaoziAINews', [
            'defaultTitle' => __('文章标题', 'jiaoziAI-news'),
            'defaultColor' => '#333333',
            'remainingChars' => __('还可输入：', 'jiaoziAI-news'),
            'characters' => __('字', 'jiaoziAI-news'),
            'enterSourceUrl' => __('请填写来源链接', 'jiaoziAI-news'),
            'enterSourceName' => __('请填写来源名称', 'jiaoziAI-news'),
            'nonce' => wp_create_nonce('jiaoziAI-news-admin')
        ]);
    }

    public function template_loader($template) {
        if (is_post_type_archive('jiaoziAI-news') || 
            is_tax(['news_category', 'news_tag']) || 
            is_singular('jiaoziAI-news')) {
            
            $file = '';
            
            if (is_singular('jiaoziAI-news')) {
                $file = 'single-news.php';
            } elseif (is_post_type_archive('jiaoziAI-news')) {
                $file = 'archive-news.php';
            } elseif (is_tax(['news_category', 'news_tag'])) {
                $file = 'taxonomy-news.php';
            }
            
            if ($file && $overridden_template = locate_template($file)) {
                return $overridden_template;
            }
            
            return JIAOZIAI_NEWS_PATH . 'templates/' . $file;
        }
        
        return $template;
    }

    public function custom_excerpt_length($length) {
        if (get_post_type() === 'jiaoziAI-news') {
            return $this->get_option('excerpt_length', 55);
        }
        return $length;
    }

    public function custom_excerpt_more($more) {
        if (get_post_type() === 'jiaoziAI-news') {
            return '...';
        }
        return $more;
    }

    public function ajax_load_more_news() {
        check_ajax_referer('jiaoziAI-news-nonce', 'nonce');

        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $args = [
            'post_type' => 'jiaoziAI-news',
            'posts_per_page' => $this->get_option('news_per_page', 10),
            'paged' => $page,
            'post_status' => 'publish'
        ];

        $query = new WP_Query($args);
        $response = [
            'html' => '',
            'hasMore' => false
        ];

        if ($query->have_posts()) {
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                include JIAOZIAI_NEWS_PATH . 'templates/content-news.php';
            }
            $response['html'] = ob_get_clean();
            $response['hasMore'] = $page < $query->max_num_pages;
        }

        wp_reset_postdata();
        wp_send_json_success($response);
    }

    public function add_plugin_links($links) {
        $plugin_links = [
            '<a href="' . admin_url('admin.php?page=jiaoziAI-news-settings') . '">' . 
            __('设置', 'jiaoziAI-news') . '</a>'
        ];
        return array_merge($plugin_links, $links);
    }

    public function add_rewrite_rules() {
        global $wp_rewrite;
        
        $permalink_structure = get_option('permalink_structure');
        
        if (empty($permalink_structure)) {
            return;
        }

        add_rewrite_tag('%jiaoziAI-news%', '([^/]+)');
        
        $news_structure = str_replace(
            ['%year%', '%monthnum%', '%day%', '%postname%'],
            ['([0-9]{4})', '([0-9]{1,2})', '([0-9]{1,2})', '([^/]+)'],
            $permalink_structure
        );
        
        $news_structure = ltrim($news_structure, '/');
        
        add_permastruct('jiaoziAI-news', $news_structure, [
            'with_front' => true,
            'ep_mask' => EP_PERMALINK
        ]);
    }

    public function custom_news_permalink($permalink, $post) {
        if ($post->post_type !== 'jiaoziAI-news') {
            return $permalink;
        }
        
        if ($permalink === get_option('home') . '/?jiaoziAI-news=' . $post->post_name) {
            return $permalink;
        }
        
        $date = explode(" ", $post->post_date);
        $date = explode("-", $date[0]);
        
        $permalink = str_replace(
            ['%year%', '%monthnum%', '%day%', '%postname%'],
            [$date[0], $date[1], $date[2], $post->post_name],
            $permalink
        );
        
        return $permalink;
    }

    public function get_option($key, $default = false) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }

    public function update_option($key, $value) {
        $this->settings[$key] = $value;
        return update_option('jiaoziAI_news_settings', $this->settings);
    }

    private function __clone() {}
    private function __wakeup() {}
}

function jiaoziAI_news() {
    return JiaoziAI_News::instance();
}

jiaoziAI_news();