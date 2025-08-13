<?php
if ( !defined( 'ABSPATH' ) ) exit;

// ----------------------------------------------------- //
// キャッシュ最適化
// ----------------------------------------------------- //

// -----------------------------------------------------
// ブラウザキャッシュの最適化
// -----------------------------------------------------
function optimize_browser_caching() {
    if (!is_admin()) {
        // 静的ファイルのキャッシュヘッダー設定
        add_action('wp_head', function() {
            echo '<meta http-equiv="Cache-Control" content="public, max-age=31536000">' . "\n";
        }, 1);
        
        // ETAGの最適化
        add_action('template_redirect', 'optimize_etags');
    }
}
add_action('init', 'optimize_browser_caching');

function optimize_etags() {
    // 静的ファイルのETag最適化
    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|webp|svg|woff|woff2)$/i', $_SERVER['REQUEST_URI'])) {
        $file_path = ABSPATH . ltrim($_SERVER['REQUEST_URI'], '/');
        if (file_exists($file_path)) {
            $etag = md5_file($file_path);
            header('ETag: "' . $etag . '"');
            
            // If-None-Matchヘッダーをチェック
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
                http_response_code(304);
                exit;
            }
        }
    }
}


// -----------------------------------------------------
// オブジェクトキャッシュの活用
// -----------------------------------------------------
class ObjectCacheOptimization {
    private $cache_group = 'uk_temp_cache';
    private $cache_expiration = 3600; // 1時間
    
    // キャッシュを無効にするページ・条件
    private $no_cache_conditions = [
        'contact',      // お問い合わせページ
        'form',         // フォームページ
        'inquiry',      // 問い合わせページ
        'mail',         // メールフォーム
        'login',        // ログインページ
        'register',     // 登録ページ
        'user',         // ユーザーページ
        'account',      // アカウントページ
        'checkout',     // チェックアウトページ
        'cart',         // カートページ
        'payment',      // 決済ページ
    ];
    
    public function __construct() {
        add_action('init', [$this, 'setup_cache']);
    }
    
    public function setup_cache() {
        // 重いクエリ結果をキャッシュ
        add_filter('posts_pre_query', [$this, 'maybe_serve_cached_posts'], 10, 2);
    }
    
    public function maybe_serve_cached_posts($posts, $query) {
        // メインクエリ以外はキャッシュしない
        if (!$query->is_main_query() || is_admin()) {
            return $posts;
        }
        
        // キャッシュを無効にする条件をチェック
        if ($this->should_skip_cache()) {
            return $posts;
        }
        
        // キャッシュキーを生成
        $cache_key = $this->generate_cache_key($query);
        
        // キャッシュから取得を試行
        $cached_posts = wp_cache_get($cache_key, $this->cache_group);
        
        if ($cached_posts !== false) {
            return $cached_posts;
        }
        
        return $posts; // キャッシュがない場合は通常のクエリを実行
    }
    
    /**
     * キャッシュをスキップすべきかチェック
     */
    private function should_skip_cache() {
        // ログインユーザーはキャッシュしない
        if (is_user_logged_in()) {
            return true;
        }
        
        // POSTリクエストはキャッシュしない
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return true;
        }
        
        // クエリパラメータがある場合はキャッシュしない
        if (!empty($_GET)) {
            return true;
        }
        
        // 現在のページ・投稿をチェック
        global $post;
        
        // スラッグによるチェック
        if ($post && $post->post_name) {
            foreach ($this->no_cache_conditions as $condition) {
                if (strpos($post->post_name, $condition) !== false) {
                    return true;
                }
            }
        }
        
        // ページテンプレートによるチェック
        if (is_page()) {
            $template = get_page_template_slug();
            if ($template) {
                foreach ($this->no_cache_conditions as $condition) {
                    if (strpos($template, $condition) !== false) {
                        return true;
                    }
                }
            }
        }
        
        // Contact Form 7のフォームページをチェック
        if (function_exists('wpcf7_get_current_contact_form') && wpcf7_get_current_contact_form()) {
            return true;
        }
        
        // カスタムフィルターでの除外設定
        return apply_filters('uk_temp_skip_cache', false);
    }
    
    /**
     * キャッシュ除外条件を追加
     */
    public function add_no_cache_condition($condition) {
        if (!in_array($condition, $this->no_cache_conditions)) {
            $this->no_cache_conditions[] = $condition;
        }
    }
    
    /**
     * キャッシュ除外条件を取得
     */
    public function get_no_cache_conditions() {
        return $this->no_cache_conditions;
    }
    
    public function cache_query_results($posts, $query) {
        if (!$query->is_main_query() || is_admin()) {
            return $posts;
        }
        
        // キャッシュを無効にする条件をチェック
        if ($this->should_skip_cache()) {
            return $posts;
        }
        
        $cache_key = $this->generate_cache_key($query);
        wp_cache_set($cache_key, $posts, $this->cache_group, $this->cache_expiration);
        
        return $posts;
    }
    
    private function generate_cache_key($query) {
        $key_data = array(
            'is_home' => $query->is_home(),
            'is_archive' => $query->is_archive(),
            'is_search' => $query->is_search(),
            'post_type' => $query->get('post_type'),
            'posts_per_page' => $query->get('posts_per_page'),
            'paged' => $query->get('paged'),
            's' => $query->get('s'),
        );
        
        return 'query_' . md5(serialize($key_data));
    }
}

// オブジェクトキャッシュ最適化のインスタンスをグローバル変数に保存（ヘルパー関数用）
global $wp_object_cache_optimization;
$wp_object_cache_optimization = new ObjectCacheOptimization();

// -----------------------------------------------------
// キャッシュクリア機能
// -----------------------------------------------------
function clear_theme_cache() {
    // 投稿更新時にキャッシュをクリア
    wp_cache_flush();
    
    // オブジェクトキャッシュもクリア
    wp_cache_flush_group('uk_temp_cache');
    
    // OPcacheもクリア（利用可能な場合）
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
}

// 投稿の更新・削除時
add_action('save_post', 'clear_theme_cache');
add_action('delete_post', 'clear_theme_cache');

// テーマファイルの変更検知とキャッシュクリア
class ThemeFileWatcher {
    private $theme_path;
    private $cache_key = 'theme_files_hash';
    
    public function __construct() {
        $this->theme_path = get_template_directory();
        add_action('init', [$this, 'check_theme_files']);
        add_action('wp_loaded', [$this, 'check_theme_files']);
        add_action('admin_init', [$this, 'check_theme_files']);
    }
    
    public function check_theme_files() {
        // 管理画面以外では頻繁にチェックしない
        if (!is_admin() && !wp_doing_ajax()) {
            return;
        }
        
        $current_hash = $this->get_theme_files_hash();
        $stored_hash = get_option($this->cache_key);
        
        if ($stored_hash && $stored_hash !== $current_hash) {
            // ファイルが変更されている場合はキャッシュをクリア
            clear_theme_cache();
            
            // 管理者に通知（デバッグ用）
            if (current_user_can('manage_options') && is_admin()) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-info is-dismissible">';
                    echo '<p>テーマファイルが変更されたため、キャッシュをクリアしました。</p>';
                    echo '</div>';
                });
            }
        }
        
        // ハッシュを更新
        update_option($this->cache_key, $current_hash);
    }
    
    private function get_theme_files_hash() {
        $files = $this->get_theme_files();
        $hash_data = [];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $hash_data[] = $file . ':' . filemtime($file);
            }
        }
        
        return md5(serialize($hash_data));
    }
    
    private function get_theme_files() {
        $files = [];
        $extensions = ['php', 'css', 'js', 'scss'];
        
        // 重要なテーマファイルをリストアップ
        $important_files = [
            $this->theme_path . '/style.css',
            $this->theme_path . '/functions.php',
            $this->theme_path . '/index.php',
            $this->theme_path . '/header.php',
            $this->theme_path . '/footer.php',
            $this->theme_path . '/single.php',
            $this->theme_path . '/archive.php',
            $this->theme_path . '/theme.json',
        ];
        
        // functions/ ディレクトリの全ファイル
        $functions_dir = $this->theme_path . '/public/functions/';
        if (is_dir($functions_dir)) {
            foreach (glob($functions_dir . '*.php') as $file) {
                $important_files[] = $file;
            }
        }
        
        // common/ ディレクトリのCSSとJSファイル
        $common_dir = $this->theme_path . '/public/common/';
        if (is_dir($common_dir)) {
            foreach ($extensions as $ext) {
                foreach (glob($common_dir . '**/*.' . $ext, GLOB_BRACE) as $file) {
                    $important_files[] = $file;
                }
            }
        }
        
        // pages/ ディレクトリの全PHPファイル
        $pages_dir = $this->theme_path . '/public/pages/';
        if (is_dir($pages_dir)) {
            foreach (glob($pages_dir . '*.php') as $file) {
                $important_files[] = $file;
            }
        }
        
        return array_unique($important_files);
    }
}

// テーマファイル監視を開始
new ThemeFileWatcher();

// 手動キャッシュクリア用のWP-CLI コマンド（WP-CLI利用時）
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('theme cache-clear', function() {
        clear_theme_cache();
        WP_CLI::success('テーマキャッシュをクリアしました。');
    });
}

// -----------------------------------------------------
// キャッシュ除外設定のヘルパー関数
// -----------------------------------------------------

/**
 * キャッシュ除外条件を追加する関数
 * 
 * @param string|array $conditions 除外するページのスラッグやキーワード
 */
function add_no_cache_conditions($conditions) {
    global $wp_object_cache_optimization;
    
    if (is_string($conditions)) {
        $conditions = [$conditions];
    }
    
    if ($wp_object_cache_optimization && is_array($conditions)) {
        foreach ($conditions as $condition) {
            $wp_object_cache_optimization->add_no_cache_condition($condition);
        }
    }
}

/**
 * 特定のページでキャッシュを無効にする
 * 
 * 使用例:
 * disable_cache_for_pages(['contact', 'form', 'custom-form']);
 */
function disable_cache_for_pages($page_slugs) {
    add_no_cache_conditions($page_slugs);
}

/**
 * フィルターを使用してキャッシュを条件付きで無効にする
 * 
 * 使用例:
 * add_filter('uk_temp_skip_cache', function($skip) {
 *     // 特定の条件でキャッシュをスキップ
 *     if (is_page('special-page') || isset($_COOKIE['user_preference'])) {
 *         return true;
 *     }
 *     return $skip;
 * });
 */

new ObjectCacheOptimization();

// -----------------------------------------------------
// 使用例・設定例
// -----------------------------------------------------

// カスタムフォームページを除外する例
// add_no_cache_conditions(['custom-contact', 'survey', 'booking']);

// 特定の条件でキャッシュを無効にする例
// add_filter('uk_temp_skip_cache', function($skip) {
//     // ECサイトのカート・チェックアウトページ
//     if (function_exists('is_cart') && (is_cart() || is_checkout())) {
//         return true;
//     }
//     
//     // ユーザーダッシュボード
//     if (is_page('dashboard') || is_page('my-account')) {
//         return true;
//     }
//     
//     return $skip;
// });