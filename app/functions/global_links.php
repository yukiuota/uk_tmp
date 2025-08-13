<?php
if ( !defined( 'ABSPATH' ) ) exit;

// ----------------------------------------------------- //
// サイト内共通で使用するショートコード（リンク設定）
// ----------------------------------------------------- //

// 全ページ共通でリンクを出力する関数
function output_link_sample() {
  // リンクのHTMLを出力
  echo 'https://xxx.com/'; // リンクのURLを指定
}
// ショートコードを登録
function output_link_sample_shortcode() {
  ob_start();
  output_link_sample();
  return ob_get_clean();
}
add_shortcode('common_link_sample', 'output_link_sample_shortcode');

// <?php echo do_shortcode('[common_link_sample]');