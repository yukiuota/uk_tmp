<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ----------------------------------------------------- //
// テーマの基本設定
// ----------------------------------------------------- //

// WordPressのバージョン情報を削除
remove_action( 'wp_head', 'wp_generator' );

/* テキストエディタの絵文字に対応する為の各種出力を排除する */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/* wlwmanifestの出力を排除する */
remove_action( 'wp_head', 'wlwmanifest_link' );

/* 外部ブログツールからの投稿を行う為の出力を排除する */
remove_action( 'wp_head', 'rsd_link' );

/* DNS Prefetchingの出力を排除する */
remove_action( 'wp_head', 'wp_resource_hints', 2 );

/* RSSフィードの出力を排除する */
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );

add_theme_support( 'automatic-feed-links' );

// 投稿の自動整形を無効化
remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );

add_theme_support( 'title-tag' );

// アクセシビリティ対応のテーマサポート
add_theme_support( 'post-thumbnails' );
add_theme_support( 'html5', array(
    'search-form',
    'comment-form',
    'comment-list',
    'gallery',
    'caption',
) );

// ナビゲーションメニューの登録
register_nav_menus( array(
    'primary' => __( 'Primary Menu', 'uk_tmp' ),
    'footer'  => __( 'Footer Menu', 'uk_tmp' ),
) );


// -----------------------------------------------------
// bodyにスラッグをクラスとして追加
// -----------------------------------------------------
function uk_tmp_add_slug_to_body_class($classes) {
    global $post;
    
    if (isset($post)) {
        $classes[] = $post->post_name;
    }
    
    return $classes;
}
add_filter('body_class', 'uk_tmp_add_slug_to_body_class');


// -----------------------------------------------------
// ファビコンの設定（WordPress標準機能を使用）
// -----------------------------------------------------
function uk_tmp_setup_favicon_support() {
  $favicon_url = get_template_directory_uri() . '/favicon.ico';

    // サイトアイコン機能を有効化
    add_theme_support('custom-logo');
    
    // カスタムファビコンがない場合のフォールバック
    if ( ! has_site_icon() ) {
        add_action( 'wp_head', 'uk_tmp_add_default_favicon' );
    }
}
add_action( 'after_setup_theme', 'uk_tmp_setup_favicon_support' );

// -----------------------------------------------------
// ファビコンの設定
// -----------------------------------------------------
function uk_tmp_add_default_favicon() {
    // WordPressのサイトアイコンが設定されていない場合のみ実行
    if ( ! has_site_icon() ) {
        echo '<!-- ファビコン -->' . "\n";
        
        // .icoファビコン
        $favicon_ico_path = get_template_directory() . '/favicon.ico';
        if ( file_exists( $favicon_ico_path ) ) {
            echo '<link rel="icon" href="' . esc_url( get_template_directory_uri() . '/favicon.ico' ) . '">' . "\n";
        }
        
        echo '<!-- スマホ用アイコン -->' . "\n";
        
        // Apple Touch Icon
        $apple_touch_icon_path = get_template_directory() . '/apple-touch-icon.png';
        if (file_exists($apple_touch_icon_path)) {
            echo '<link rel="apple-touch-icon" href="' . esc_url(get_template_directory_uri() . '/apple-touch-icon.png') . '">' . "\n";
        }
        
        // Android Chrome用アイコン
        $android_chrome_path = get_template_directory() . '/android-chrome.png';
        if (file_exists($android_chrome_path)) {
            echo '<link rel="icon" type="image/png" href="' . esc_url(get_template_directory_uri() . '/android-chrome.png') . '">' . "\n";
        }
    }
}
add_action('wp_head', 'uk_tmp_add_default_favicon');



// -----------------------------------------------------
// CSS、JSの読み込み
// -----------------------------------------------------
class ThemeScriptsStyles
{
  public function __construct()
  {
    add_action('wp_enqueue_scripts', [$this, 'add_files']);
    add_action('wp_footer', [$this, 'custom_print_scripts']);
  }

  public function add_files()
  {
    // サイト共通のCSSの読み込み  
    $css_file_path = get_template_directory() . '/public/common/css/common.css';
    if (file_exists($css_file_path)) {
      $css_file_ver = filemtime($css_file_path);
      wp_enqueue_style('main02', get_template_directory_uri() . '/public/common/css/common.css', array(), $css_file_ver);
    }

    // body_classの値に基づくCSSファイルの読み込み
    $body_classes = get_body_class();
    if (!empty($body_classes)) {
      foreach ($body_classes as $class_name) {
        $css_file_path = get_template_directory() . '/public/common/css/' . $class_name . '.css';
        if (file_exists($css_file_path)) {
          $css_file_ver = filemtime($css_file_path);
          wp_enqueue_style('body-class-' . $class_name, get_template_directory_uri() . '/public/common/css/' . $class_name . '.css', array(), $css_file_ver);
        }
      }
    }

    // if ((is_home() || is_front_page())) { // TOP
    //     $css_file_path = get_template_directory() . '/common/css/index.css';
    //     if (file_exists($css_file_path)) {
    //       $css_file_ver = filemtime($css_file_path);
    //       wp_enqueue_style('main', get_template_directory_uri() . '/common/css/index.css', array(), $css_file_ver);
    //     }
    // } elseif ((get_post_type() === 'news')) { // カスタム投稿
    //     $css_file_path = get_template_directory() . '/common/css/news.css';
    //     if (file_exists($css_file_path)) {
    //       $css_file_ver = filemtime($css_file_path);
    //       wp_enqueue_style('sub', get_template_directory_uri() . '/common/css/news.css', array(), $css_file_ver);
    //     }
    // } elseif (is_page('59')) { // 固定ページ
    //     $css_file_path = get_template_directory() . '/common/css/contact.css';
    //     if (file_exists($css_file_path)) {
    //       $css_file_ver = filemtime($css_file_path);
    //       wp_enqueue_style('sub02', get_template_directory_uri() . '/common/css/contact.css', array(), $css_file_ver);
    //     }
    // }

    wp_enqueue_script("jquery");
  }

  public function custom_print_scripts()
  {
    $js_file_path = get_template_directory() . '/public/common/js/script.js';
    if (file_exists($js_file_path)) {
      wp_enqueue_script('script02', get_template_directory_uri() . '/public/common/js/script.js', array('jquery'), filemtime($js_file_path), true);
    }
  }

}

new ThemeScriptsStyles();





// -----------------------------------------------------
// テンプレートのimgパスを取得
// -----------------------------------------------------
function tmp_img($path) {
  // imgディレクトリが存在するかチェック
  $img_dir = get_template_directory() . '/public/img/';
  if (!is_dir($img_dir)) {
    // imgディレクトリが存在しない場合は何もしない
    return '';
  }
  
  $full_path = $img_dir . $path;
  if (!file_exists($full_path)) {
    return '';
  }
  
  return esc_url(get_template_directory_uri() . '/public/img/' . $path);
}


// -----------------------------------------------------
// テンプレートのimgの幅・高さを取得
// -----------------------------------------------------
function tmp_img_wh($path) {
  // imgディレクトリが存在するかチェック
  $img_dir = get_template_directory() . '/public/img/';
  if (!is_dir($img_dir)) {
    // imgディレクトリが存在しない場合は何もしない
    return;
  }
  
  $image_url = get_template_directory() . '/public/img/' . $path;

  // ファイルが存在し、かつ画像ファイルかチェック
  if (file_exists($image_url) && is_file($image_url)) {
      // ファイル拡張子を取得
      $file_extension = strtolower(pathinfo($image_url, PATHINFO_EXTENSION));
      
      if ($file_extension === 'svg') {
          // SVGファイルの場合
          $svg_dimensions = tmp_get_svg_dimensions($image_url);
          if ($svg_dimensions) {
              echo 'width="' . intval($svg_dimensions['width']) . '" height="' . intval($svg_dimensions['height']) . '"';
          }
      } else {
          // 通常の画像ファイルの場合
          $dimensions = getimagesize($image_url);
          if ($dimensions && is_array($dimensions) && isset($dimensions[0], $dimensions[1])) {
              echo 'width="' . intval($dimensions[0]) . '" height="' . intval($dimensions[1]) . '"';
          }
      }
  }
}

// -----------------------------------------------------
// SVGの幅・高さを取得
// -----------------------------------------------------
function tmp_get_svg_dimensions($svg_file_path) {
    if (!file_exists($svg_file_path)) {
        return false;
    }
    
    // SVGファイルの内容を読み込み
    $svg_content = file_get_contents($svg_file_path);
    if (!$svg_content) {
        return false;
    }
    
    // XMLとして解析
    $previous_value = libxml_use_internal_errors(true);
    $svg = simplexml_load_string($svg_content);
    libxml_use_internal_errors($previous_value);
    
    if (!$svg) {
        return false;
    }
    
    $width = null;
    $height = null;
    
    // width属性とheight属性を取得
    $attributes = $svg->attributes();
    if (isset($attributes['width'])) {
        $width = (string) $attributes['width'];
    }
    if (isset($attributes['height'])) {
        $height = (string) $attributes['height'];
    }
    
    // viewBox属性からサイズを取得（width/heightが設定されていない場合）
    if ((!$width || !$height) && isset($attributes['viewBox'])) {
        $viewBox = (string) $attributes['viewBox'];
        $viewBoxValues = preg_split('/[\s,]+/', trim($viewBox));
        if (count($viewBoxValues) >= 4) {
            if (!$width) {
                $width = $viewBoxValues[2]; // viewBoxの3番目の値が幅
            }
            if (!$height) {
                $height = $viewBoxValues[3]; // viewBoxの4番目の値が高さ
            }
        }
    }
    
    // 単位を除去して数値のみを取得
    if ($width && $height) {
        $width = preg_replace('/[^0-9.]/', '', $width);
        $height = preg_replace('/[^0-9.]/', '', $height);
        
        if (is_numeric($width) && is_numeric($height)) {
            return [
                'width' => (float) $width,
                'height' => (float) $height
            ];
        }
    }
    
    return false;
}