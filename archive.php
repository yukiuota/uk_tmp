<?php
if ( !defined( 'ABSPATH' ) ) exit;

get_header();

$post_type = get_post_type();

// カスタム投稿タイプが取得できない場合（アーカイブページなど）
if (!$post_type) {
    $queried_object = get_queried_object();
    
    if (isset($queried_object->name) && $queried_object instanceof WP_Post_Type) {
        // カスタム投稿タイプのアーカイブの場合
        $post_type = $queried_object->name;
    } elseif (isset($queried_object->taxonomy) && $queried_object instanceof WP_Term) {
        // タクソノミーアーカイブの場合
        $post_type = 'taxonomy';
        // 必要に応じてタクソノミー名を使用: $taxonomy = $queried_object->taxonomy;
    } else {
        // デフォルト
        $post_type = 'post';
    }
}

// テンプレートが存在するか確認
if (locate_template('public/archives/' . $post_type . '.php')) {
    // テンプレートが見つかった場合、get_template_part()でインクルード
    get_template_part('public/archives/' . $post_type);
} else {
    // テンプレートが見つからなかった場合、デフォルトテンプレートをインクルード
    if (locate_template('public/archives/archive-base.php')) {
        get_template_part('public/archives/archive-base');
    } else {
        // デフォルトテンプレートも見つからない場合の処理
        echo '<div class="container"><p>テンプレートが見つかりませんでした。</p></div>';
    }
}

get_footer();
?>