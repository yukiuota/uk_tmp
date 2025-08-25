<?php
if ( !defined( 'ABSPATH' ) ) exit;

// ----------------------------------------------------- //
// プラグインカスタマイズ
// ----------------------------------------------------- //

// ----------------------------------------------------- //
// Contact Form7
// ----------------------------------------------------- //
// Contact Form7のCSS・JS
function enqueue_custom_assets_for_specific_page() {
    if (is_page('contact')) { // 固定ページのスラッグ指定
        // CSSファイルの読み込み
        wp_enqueue_style('custom-page-style', get_template_directory_uri() . '/app/plugins/p_cf7/style.css', array(), '1.0.0');
        
        // JavaScriptファイルの読み込み
        wp_enqueue_script('custom-page-script', get_template_directory_uri() . '/app/plugins/p_cf7/form.js', array('jquery'), '1.0.0', true);
        
        // Contact Form 7のデフォルトバリデーションメッセージを非表示にするCSS
        wp_add_inline_style('custom-page-style', '
            .wpcf7-not-valid-tip {
                display: none !important;
            }
            .wpcf7-validation-errors {
                display: none !important;
            }
        ');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_assets_for_specific_page');



// Contact Form 7で自動挿入されるPタグ、brタグを削除
add_filter('wpcf7_autop_or_not', 'wpcf7_autop_return_false');
function wpcf7_autop_return_false() {
  return false;
}