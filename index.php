<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

if ( is_home() || is_front_page() ) :
    get_template_part( 'public/pages/top' );
elseif ( is_page() ) :
    global $post;
    $slug = basename( get_permalink( $post->ID ) );
    $template_part = 'public/pages/' . $slug;
    if ( ! locate_template( $template_part . '.php' ) ) {
        $template_part = 'public/pages/page-base';
    }
    get_template_part( $template_part );
else :
    get_template_part( 'public/archives/archive-base' );
endif;

get_footer();
?>