<?php
/**
 * WP Template Theme Functions
 *
 * @package uk_tmp
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * テーマのセットアップ
 * 
 * @since 1.0.0
 */
function uk_tmp_setup() {
    // 国際化対応
    load_theme_textdomain( 'uk_tmp', get_template_directory() . '/app/languages' );
}
add_action( 'after_setup_theme', 'uk_tmp_setup' );

$includes = array(
    'app/functions/settings.php', // デフォルト設定
    'app/functions/seo.php', // SEO設定
    'app/functions/admin.php', // 管理画面カスタマイズ
    'app/functions/posts.php', // 投稿・カスタム投稿カスタマイズ
    'app/functions/global_links.php', // サイト全体で共通するリンク
    'app/functions/cache.php', // キャッシュ関連
    // 以降必要なければ削除
    'app/functions/post_gallery.php', // 投稿・カスタム投稿ギャラリー
    'app/functions/single_gallery.php', // オリジナルページギャラリー
    'app/functions/widget.php', // ウィジェット関連
    'app/functions/custom_block.php', // ブロックカスタマイズ
    'app/functions/plugins.php', // プラグインカスタマイズ
    'app/functions/ajax_more.php', // ajaxでのカスタム投稿more
    'app/functions/ajax_search.php', // ajaxでのカスタム投稿絞り込み
    'app/functions/ajax_pagination.php', // ajaxでのページネーション
    'app/functions/comment.php', // コメント関連

    'app/functions/test.php', // テスト関連コード
);

foreach ( $includes as $file ) {
    if ( file_exists( get_template_directory() . '/' . $file ) ) {
        include_once( get_template_directory() . '/' . $file );
    }
}