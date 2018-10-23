<?php
/**
 * Theme Page Section for our theme.
 *
 * @package ThemeGrill
 * @subpackage Spacious
 * @since Spacious 1.0
 */
get_header(); ?>

	<?php do_action( 'spacious_before_body_content' ); ?>

	<div id="primary">
		<div id="content" class="clearfix">
		        <?php woocommerce_content(); ?>
		</div><!-- #content -->
	</div><!-- #primary -->

        <!-- We need to force the left sidebar to display in all woocommerce pages -->
	<?php //spacious_sidebar_select(); ?>
	<?php get_sidebar('left'); ?>

	<?php do_action( 'spacious_after_body_content' ); ?>

<?php get_footer(); ?>
