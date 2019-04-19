<?php /* Template Name: Sensor Template */ ?>
<?php

// wp_enqueue_style( 'sensors', get_stylesheet_directory_uri() . '/sensors.css');
wp_enqueue_style( 'chartist', "http://cdn.jsdelivr.net/chartist.js/latest/chartist.min.css" );
wp_enqueue_script( 'moment', "https://cdn.jsdelivr.net/npm/moment@2.24.0/moment.min.js");
wp_enqueue_style( 'leaflet', "https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" );
wp_enqueue_script( 'chartist', "http://cdn.jsdelivr.net/chartist.js/latest/chartist.min.js");
wp_enqueue_script( 'chartist-axis', "https://cdn.jsdelivr.net/npm/chartist-plugin-axistitle@0.0.4/dist/chartist-plugin-axistitle.min.js");
wp_enqueue_script( 'leaflet', "https://unpkg.com/leaflet@1.3.4/dist/leaflet.js");

function get_sensor_data($station, $sensor)
{
    global $wpdb;
    $query_str = "SELECT * FROM farmurban.$sensor WHERE station=$station";
    $query = $wpdb->prepare($query_str, 'foo');
    $rows = $wpdb->get_results($query);

    // Define the table columns, i.e. what the x and y data actually are.
    $gdata = array();
    $gdata['labels'] = array();
    $gdata['series'] = array();
    $gdata['series'][] = array();
    $time_as_int = 0;
    $i=1;
    foreach ($rows as $obj) :
    {
            if ($time_as_int == 1) {
                $gdata['labels'][] = $i;
            } else {
                $gdata['labels'][] = strtotime($obj->time);
            }
            $gdata['series'][0][] = (float)$obj->reading;
            $i++;
    }
    endforeach;
    return  json_encode($gdata);
}
?>
<?php get_header(); ?>
<?php do_action('spacious_before_body_content'); ?>

	<div id="primary">
		<div id="content" class="clearfix">
			<?php while (have_posts()) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					        <?php do_action('spacious_before_post_content'); ?>
					        <div class="entry-content clearfix">
					                <?php
					                if ((spacious_options('spacious_featured_image_single_page', 0) == 1) && has_post_thumbnail()) {
					                    the_post_thumbnail('featured-blog-large');
					                }
													// Content from editor
					                the_content();
													// Add graphs after main content
													$station = 2;
													$sensor = 'ambient_light_0';
													$light_data = get_sensor_data($station, $sensor);
													$sensor = 'humidity_temperature';
													$temp_data = get_sensor_data($station, $sensor);
													echo "<script>var light_data=$light_data;\nvar temp_data=$temp_data;</script>";
													include(get_stylesheet_directory() . '/sensors_insert.html');

					                wp_link_pages(array(
					                        'before'      => '<div style="clear: both;"></div><div class="pagination clearfix">' . __('Pages:', 'spacious'),
					                        'after'       => '</div>',
					                        'link_before' => '<span>',
					                        'link_after'  => '</span>'
					                ));
					                ?>
					        </div>
					        <footer class="entry-meta-bar clearfix">
					                <div class="entry-meta clearfix">
					                        <?php edit_post_link(__('Edit', 'spacious'), '<span class="edit-link">', '</span>'); ?>
					                </div>
					        </footer>
					        <?php
					        do_action('spacious_after_post_content');
					        ?>
					</article>

				<?php
                    do_action('spacious_before_comments_template');
                    // If comments are open or we have at least one comment, load up the comment template
                    if (comments_open() || '0' != get_comments_number()) {
                        comments_template();
                    }
                  do_action('spacious_after_comments_template');
                ?>

			<?php endwhile; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

	<?php spacious_sidebar_select(); ?>

	<?php do_action('spacious_after_body_content'); ?>

<?php get_footer(); ?>
