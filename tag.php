<?php
if ( !defined( 'ABSPATH' ) ) exit;

get_header();

$page = get_post(get_the_ID());
$template = locate_template('archives/' . $page->post_type . '.php');

if ($template) {
    get_template_part('archives/' . $page->post_type);
} else {
    get_template_part('archives/archive-base');
}

get_footer();
?>