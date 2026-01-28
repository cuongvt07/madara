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
	), '1.0.5');

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
