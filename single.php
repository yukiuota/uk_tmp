<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$page = get_post( get_the_ID() );
$template = locate_template( 'single/' . $page->post_type . '.php' );

if ( $template ) {
    get_template_part( 'public/single/' . $page->post_type );
} else {
    get_template_part( 'public/single/single-base' );
}

get_footer();
?>