<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

if ( is_home() || is_front_page() ) :
    // ホームページ・フロントページ
    get_template_part( 'public/pages/top' );
elseif ( is_single() ) :
    // 単一投稿ページ
    $page = get_post( get_the_ID() );
    $template = locate_template( 'public/single/' . $page->post_type . '.php' );
    
    if ( $template ) {
        get_template_part( 'public/single/' . $page->post_type );
    } else {
        get_template_part( 'public/single/single-base' );
    }
elseif ( is_page() ) :
    // 固定ページ
    global $post;
    $slug = basename( get_permalink( $post->ID ) );
    $template_part = 'public/pages/' . $slug;
    if ( ! locate_template( $template_part . '.php' ) ) {
        $template_part = 'public/pages/page-base';
    }
    get_template_part( $template_part );
elseif ( is_archive() || is_category() || is_tag() || is_tax() || is_author() || is_date() ) :
    // アーカイブページ（カテゴリ、タグ、カスタムタクソノミー、投稿者、日付アーカイブを含む）
    $post_type = get_post_type();

    // カスタム投稿タイプが取得できない場合
    if (!$post_type) {
        $queried_object = get_queried_object();
        
        if (isset($queried_object->name) && $queried_object instanceof WP_Post_Type) {
            // カスタム投稿タイプのアーカイブの場合
            $post_type = $queried_object->name;
        } elseif (isset($queried_object->taxonomy) && $queried_object instanceof WP_Term) {
            // タクソノミーアーカイブの場合
            $post_type = 'taxonomy';
        } else {
            // デフォルト
            $post_type = 'post';
        }
    }

    // テンプレートが存在するか確認
    if (locate_template('public/archives/' . $post_type . '.php')) {
        get_template_part('public/archives/' . $post_type);
    } else {
        // デフォルトテンプレート
        if (locate_template('public/archives/archive-base.php')) {
            get_template_part('public/archives/archive-base');
        } else {
            echo '<div class="container"><p>テンプレートが見つかりませんでした。</p></div>';
        }
    }
else :
    // その他の場合のフォールバック
    get_template_part( 'public/archives/archive-base' );
endif;

get_footer();
?>