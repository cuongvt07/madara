<?php
add_action('wp_enqueue_scripts', 'madara_scripts_styles_child_theme');

// Register Custom Widgets
add_action('widgets_init', function () {
	require_once get_stylesheet_directory() . '/widgets/class-widget-new-updates.php';
	register_widget('Madara_Child_Widget_New_Updates');
});

function madara_scripts_styles_child_theme()
{
	$theme = wp_get_theme();
	wp_enqueue_style(
		'madara-css',
		get_template_directory_uri() . '/style.css',
		array(
			'bootstrap',
			'slick',
			'slick-theme'
		),
		$theme->parent()->get('Version')
	);

	if (is_rtl()) {
		wp_enqueue_style('madara-rtl', get_template_directory_uri() . '/rtl.css');
	}

	wp_enqueue_style('madara-css-child', get_stylesheet_directory_uri() . '/style.css', array(
		'madara-css'
	), '1.0.7');

	// Enqueue Swiper.js for vertical carousel
	wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0');
	wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true);
	wp_enqueue_script('mad-carousel-init', get_stylesheet_directory_uri() . '/js/slider-init.js', array('jquery', 'swiper-js'), '1.0.5', true);
}

/* Disable VC auto-update */
add_action('admin_init', 'madara_vc_disable_update', 9);
function madara_vc_disable_update()
{
	if (function_exists('vc_license') && function_exists('vc_updater') && !vc_license()->isActivated()) {

		remove_filter('upgrader_pre_download', array(vc_updater(), 'preUpgradeFilter'), 10);
		remove_filter('pre_set_site_transient_update_plugins', array(
			vc_updater()->updateManager(),
			'check_update'
		));

	}
}

/**
 * does not support Widgets Block Editor yet
 **/
function madara_theme_support()
{
	remove_theme_support('widgets-block-editor');
}
add_action('after_setup_theme', 'madara_theme_support');
/**
 * FIX: Force Madara shortcodes to render on Homepage
 * Prevent [madara_manga] from being printed as raw text
 */
add_filter('the_content', function ($content) {

	// Do nothing in admin
	if (is_admin()) {
		return $content;
	}

	// Only process if Madara shortcode exists
	if (has_shortcode($content, 'madara_manga')) {
		$content = do_shortcode($content);
	}

	return $content;

}, 20);

/**
 * Enable shortcodes in widgets (Madara sidebars safety)
 */
add_filter('widget_text', 'do_shortcode');
add_filter('widget_text_content', 'do_shortcode');

/**
 * Filter to force d/m/Y date format in manga chapters (New Updates / Widget)
 */
add_filter('madara_archive_chapter_date', function ($time_diff, $chapter_id, $date, $link) {
	if (!empty($date)) {
		// Calculate time difference in seconds
		$diff = time() - strtotime($date);
		// If less than 3 days (259200 seconds), show NEW badge
		if ($diff < 259200) {
			$time_ago = human_time_diff(strtotime($date), current_time('timestamp')) . ' ago';
			// Return exact HTML structure requested by user for NEW items
			return sprintf(
				'<a href="%s" title="%s" class="c-new-tag"><!-- --></a>',
				esc_url($link),
				esc_attr($time_ago)
			);
		}
		// Otherwise show d/m/Y date
		return date('d/m/Y', strtotime($date));
	}
	return $time_diff;
}, 20, 4);


/**
 * Shortcode: [manga_new_updates]
 * Thay thế widget Madara New Updates
 */

add_shortcode('manga_new_updates', function ($atts) {

	if (!class_exists('WP_MANGA')) {
		return '<p><strong>WP Manga plugin chưa được kích hoạt.</strong></p>';
	}

	global $wp_manga_template;

	/* ======================
	 * SHORTCODE ATTRS
	 * ====================== */
	$atts = shortcode_atts([
		'title' => '',
		'posts_per_page' => 24,
		'columns' => 6,
		'style' => 'style-1', // style-1 | style-2
		'show_badges' => 1,
		'show_chapter_count' => 1,
		'show_update_date' => 1,
		'pagination' => 1,
	], $atts);

	/* ======================
	 * PAGINATION
	 * ====================== */
	$paged = max(
		1,
		get_query_var('paged') ?: get_query_var('page')
	);

	/* ======================
	 * QUERY
	 * ====================== */
	$q = new WP_Query([
		'post_type' => 'wp-manga',
		'post_status' => 'publish',
		'posts_per_page' => (int) $atts['posts_per_page'],
		'paged' => $paged,
		'orderby' => 'modified',
		'order' => 'DESC',
	]);

	/* ======================
	 * COLUMN CLASS
	 * ====================== */
	$col = max(1, min(6, (int) $atts['columns']));
	$col_class = 'col-md-' . floor(12 / $col) . ' col-sm-4 col-xs-6';

	ob_start();
	?>

	<div class="widget madara-child-new-updates">

		<?php if (!empty($atts['title'])): ?>
			<h2 class="widget-title heading-style-1">
				<?php echo esc_html($atts['title']); ?>
			</h2>
		<?php endif; ?>

		<!-- GIỮ NGUYÊN HEADING ẢNH CỦA WIDGET -->
		<div class="img_heading">
			<img decoding="async" src="https://vitaminkoo.com/wp-content/themes/gdcv/img/MCN.png">
		</div>

		<?php if ($q->have_posts()): ?>

			<div class="c-blog-listing mad-new-updates-widget-grid">
				<div class="c-blog-grid">
					<div class="row">

						<?php
						while ($q->have_posts()):
							$q->the_post();

							set_query_var('madara_widget_settings', [
								'show_badges' => (bool) $atts['show_badges'],
								'show_chapter_count' => (bool) $atts['show_chapter_count'],
								'show_update_date' => (bool) $atts['show_update_date'],
								'col_class' => $col_class,
								'style' => $atts['style'],
							]);
							?>

							<div class="<?php echo esc_attr($col_class); ?> page-listing-item internal-widget">
								<div class="popular-item-wrap">

									<?php
									if ($atts['style'] === 'style-1') {
										$wp_manga_template->load_template(
											'widgets/recent-manga/content-1',
											false
										);
									} else {
										$wp_manga_template->load_template(
											'widgets/recent-manga/content-2',
											false
										);
									}
									?>

								</div>
							</div>

						<?php endwhile; ?>

					</div>
				</div>
			</div>

			<?php if ($atts['pagination'] && $q->max_num_pages > 1): ?>
				<div class="mad-pagination text-right">
					<?php
					echo paginate_links([
						'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
						'format' => '?paged=%#%',
						'current' => max(1, $paged),
						'total' => $q->max_num_pages,
						'mid_size' => 1,  // ← GIẢM XUỐNG 1 để dễ xuất hiện dots
						'end_size' => 1,  // ← Giữ nguyên 1
						'prev_text' => '<i class="icon ion-ios-arrow-back"></i>',
						'next_text' => '<i class="icon ion-ios-arrow-forward"></i>',
					]);
					?>
				</div>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

		<?php else: ?>
			<p>Không có manga nào.</p>
		<?php endif; ?>

	</div>

	<?php
	return ob_get_clean();
});