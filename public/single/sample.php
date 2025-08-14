<?php
if ( !defined( 'ABSPATH' ) ) exit;
// newsデータ読み込み
require_once get_template_directory() . '/app/controller/news.php';
?>

<?php while ( have_posts() ) : the_post(); ?>
<main id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php the_title(); ?>
    <?php the_content(); ?>
    <?php if ( $custom_text ) : ?>
        <div class="custom-text">
            <?php echo '<p>' . esc_html( $custom_text ) . '</p>'; ?>
        </div>
    <?php endif; ?>
    <?php display_prev_next_post_links(); ?>
    <?php endwhile; ?>
</main>