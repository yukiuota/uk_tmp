<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// テーマ基本設定
// =============================================================================

/**
 * WordPressコア機能のクリーンアップ
 */
// WordPressバージョン情報を削除
remove_action( 'wp_head', 'wp_generator' );

// 絵文字検出スクリプトとスタイルを削除
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

// Windows Live Writerマニフェストを削除
remove_action( 'wp_head', 'wlwmanifest_link' );

// Really Simple Discoveryリンクを削除
remove_action( 'wp_head', 'rsd_link' );

// DNS プリフェッチを削除
remove_action( 'wp_head', 'wp_resource_hints', 2 );

// RSSフィードリンクを削除
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );

/**
 * テーマサポート設定
 */
add_theme_support( 'automatic-feed-links' );

// 自動段落整形を無効化
remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );

// 動的ドキュメントタイトルサポートを有効化
add_theme_support( 'title-tag' );

// アクセシビリティとHTML5テーマサポート
add_theme_support( 'post-thumbnails' );
add_theme_support( 'html5', array(
    'search-form',
    'comment-form',
    'comment-list',
    'gallery',
    'caption',
) );

// ブロックスタイルサポート
add_theme_support( 'wp-block-styles' );

// ナビゲーションメニューの登録
register_nav_menus( array(
    'primary' => __( 'Primary Menu', 'uk_tmp' ),
    'footer'  => __( 'Footer Menu', 'uk_tmp' ),
) );

// =============================================================================
// bodyクラスのカスタマイズ
// =============================================================================

/**
 * 投稿スラッグをbodyクラスに追加
 *
 * @param array $classes 既存のbodyクラス
 * @return array 変更されたbodyクラス
 */
function uk_tmp_add_slug_to_body_class( $classes ) {
    global $post;
    
    if ( isset( $post ) ) {
        $classes[] = $post->post_name;
    }
    
    return $classes;
}
add_filter( 'body_class', 'uk_tmp_add_slug_to_body_class' );

// =============================================================================
// ファビコン設定
// =============================================================================

/**
 * ファビコンサポートクラス
 */
class UK_Tmp_Favicon_Support {
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'setup_favicon_support' ) );
        add_action( 'wp_head', array( $this, 'add_default_favicon' ) );
    }
    
    /**
     * ファビコンサポートのセットアップ
     */
    public function setup_favicon_support() {
        // カスタムロゴ機能を有効化
        add_theme_support( 'custom-logo' );
    }
    
    /**
     * WordPressサイトアイコンが未設定の場合にデフォルトファビコンを追加
     */
    public function add_default_favicon() {
        // WordPressサイトアイコンが設定されている場合は実行しない
        if ( has_site_icon() ) {
            return;
        }
        
        echo '<!-- デフォルトファビコン -->' . "\n";
        
        // .ico ファビコン
        $favicon_ico_path = get_template_directory() . '/favicon.ico';
        if ( file_exists( $favicon_ico_path ) ) {
            echo '<link rel="icon" href="' . esc_url( get_template_directory_uri() . '/favicon.ico' ) . '">' . "\n";
        }
        
        echo '<!-- モバイル端末用アイコン -->' . "\n";
        
        // Apple Touch Icon
        $apple_touch_icon_path = get_template_directory() . '/apple-touch-icon.png';
        if ( file_exists( $apple_touch_icon_path ) ) {
            echo '<link rel="apple-touch-icon" href="' . esc_url( get_template_directory_uri() . '/apple-touch-icon.png' ) . '">' . "\n";
        }
        
        // Android Chrome用アイコン
        $android_chrome_path = get_template_directory() . '/android-chrome.png';
        if ( file_exists( $android_chrome_path ) ) {
            echo '<link rel="icon" type="image/png" href="' . esc_url( get_template_directory_uri() . '/android-chrome.png' ) . '">' . "\n";
        }
    }
}

// ファビコンサポートを初期化
new UK_Tmp_Favicon_Support();


// =============================================================================
// スクリプト・スタイル管理
// =============================================================================

/**
 * テーマスクリプト・スタイル管理クラス
 */
class UK_Tmp_Scripts_Styles {
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }
    
    /**
     * テーマスタイルの読み込み
     */
    public function enqueue_styles() {
        // メインテーマスタイルシート
        $main_css_path = get_template_directory() . '/public/common/css/common.css';
        if ( file_exists( $main_css_path ) ) {
            $main_css_version = filemtime( $main_css_path );
            wp_enqueue_style( 
                'uk-tmp-main', 
                get_template_directory_uri() . '/public/common/css/common.css', 
                array(), 
                $main_css_version 
            );
        }
        
        // bodyクラス固有のスタイルシート
        $this->enqueue_body_class_styles();
    }
    
    /**
     * bodyクラス固有のスタイルの読み込み
     */
    private function enqueue_body_class_styles() {
        $body_classes = get_body_class();
        
        if ( empty( $body_classes ) ) {
            return;
        }
        
        foreach ( $body_classes as $class_name ) {
            $css_file_path = get_template_directory() . '/public/common/css/' . sanitize_file_name( $class_name ) . '.css';
            
            if ( file_exists( $css_file_path ) ) {
                $css_file_version = filemtime( $css_file_path );
                wp_enqueue_style( 
                    'uk-tmp-body-class-' . sanitize_html_class( $class_name ), 
                    get_template_directory_uri() . '/public/common/css/' . sanitize_file_name( $class_name ) . '.css', 
                    array( 'uk-tmp-main' ), 
                    $css_file_version 
                );
            }
        }
    }
    
    /**
     * テーマスクリプトの読み込み
     */
    public function enqueue_scripts() {
        // jQuery（WordPress標準）
        wp_enqueue_script( 'jquery' );
        
        // メインテーマスクリプト
        $main_js_path = get_template_directory() . '/public/common/js/script.js';
        if ( file_exists( $main_js_path ) ) {
            $main_js_version = filemtime( $main_js_path );
            wp_enqueue_script( 
                'uk-tmp-main-script', 
                get_template_directory_uri() . '/public/common/js/script.js', 
                array( 'jquery' ), 
                $main_js_version, 
                true // フッターで読み込み
            );
        }
    }
}

// スクリプト・スタイル管理を初期化
new UK_Tmp_Scripts_Styles();