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
						'mid_size' => 2,  // ← GIẢM XUỐNG 1 để dễ xuất hiện dots
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

/**
 * Shortcode: [madara_vitaminkoo_ranking]
 * Bảng xếp hạng manga theo Daily/Weekly/Monthly
 */
function madara_vitaminkoo_ranking_shortcode()
{
	ob_start(); ?>

	<div class="widget col-12 col-md-12 default no-icon heading-style-1 c-popular manga-widget widget-manga-tab">
		<div class="widget__inner c-popular manga-widget widget-manga-tab__inner c-widget-wrap">
			<div class="widget-content">

				<div class="img_heading">
					<img src="https://vitaminkoo.com/wp-content/themes/gdcv/img/BXH.png">
				</div>

				<div class="manga-tab">
					<ul class="nav nav-tabs">
						<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#today_rank">Hôm nay</a>
						</li>
						<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#week_rank">Tuần Này</a></li>
						<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#month_rank">Tháng Này</a></li>
					</ul>

					<div class="tab-content">

						<?php
						$tabs = [
							'today_rank' => 'day',
							'week_rank' => 'week',
							'month_rank' => 'month'
						];

						foreach ($tabs as $tab_id => $time_key):
							?>

							<div id="<?php echo $tab_id; ?>"
								class="tab-pane <?php echo $time_key === 'day' ? 'active' : ''; ?>">
								<div class="c-widget-content style-1">

									<?php
									// Primary query: time-specific views
									$args = [
										'post_type' => 'wp-manga',
										'posts_per_page' => 5,
										'meta_key' => "_wp_manga_{$time_key}_views_value",
										'orderby' => 'meta_value_num',
										'order' => 'DESC'
									];

									$query = new WP_Query($args);

									// Fallback: if no results, use total views
									if (!$query->have_posts()) {
										$args['meta_key'] = '_wp_manga_views';
										$query = new WP_Query($args);
									}

									// Fallback 2: if still no results, order by modified date
									if (!$query->have_posts()) {
										unset($args['meta_key']);
										$args['orderby'] = 'modified';
										$query = new WP_Query($args);
									}
									$rank = 1;

									while ($query->have_posts()):
										$query->the_post();
										$thumb = get_the_post_thumbnail_url(get_the_ID(), 'manga_wg_post_1');
										$genres = get_the_term_list(get_the_ID(), 'wp-manga-genre', '', ', ');
										?>
										<div class="popular-item-wrap">
											<div class="ctr"><?php echo $rank; ?></div>

											<div class="popular-img widget-thumbnail c-image-hover">
												<a href="<?php the_permalink(); ?>">
													<img src="<?php echo esc_url($thumb); ?>">
												</a>
											</div>

											<div class="popular-content">
												<h5 class="widget-title">
													<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
												</h5>

												<span class="text">
													<strong>Thể loại:</strong> <?php echo $genres; ?>
												</span>
											</div>
										</div>

										<?php $rank++; endwhile;
									wp_reset_postdata(); ?>

								</div>
							</div>

						<?php endforeach; ?>

					</div>
				</div>

			</div>
		</div>
	</div>

	<?php
	return ob_get_clean();
}
add_shortcode('madara_vitaminkoo_ranking', 'madara_vitaminkoo_ranking_shortcode');

/**
 * Shortcode: [madara_vitaminkoo_completed]
 * Hiển thị truyện đã hoàn thành (không phân trang, có nút xem thêm)
 */
add_shortcode('madara_vitaminkoo_completed', function ($atts) {

	if (!class_exists('WP_MANGA'))
		return;

	global $wp_manga_template;

	$atts = shortcode_atts([
		'title' => 'TRUYỆN ĐÃ HOÀN THÀNH',
		'posts_per_page' => 12,
		'columns' => 6,
		'style' => 'style-1',
		'show_badges' => 1,
		'show_chapter_count' => 1,
		'show_update_date' => 1,
		'button_text' => 'Xem thêm',
		'button_url' => 'https://vitaminkoo.com/hoan-thanh'
	], $atts);

	// Query Completed Manga
	$q = new WP_Query([
		'post_type' => 'wp-manga',
		'post_status' => 'publish',
		'posts_per_page' => (int) $atts['posts_per_page'],
		'orderby' => 'modified', // Or 'date'
		'order' => 'DESC',
		'meta_query' => [
			[
				'key' => '_wp_manga_status',
				'value' => ['end', 'completed', 'complete'], // Support variations
				'compare' => 'IN'
			]
		]
	]);

	// Column Class
	$col_class = 'col-md-2 col-sm-4 col-xs-6'; // Default 6 cols
	if ($atts['columns'] == 4)
		$col_class = 'col-md-3 col-sm-6 col-xs-6';
	if ($atts['columns'] == 3)
		$col_class = 'col-md-4 col-sm-6 col-xs-12';
	if ($atts['columns'] == 2)
		$col_class = 'col-md-6 col-sm-6 col-xs-12';

	ob_start();
	?>

	<div class="widget madara-child-completed-manga">

		<?php if (!empty($atts['title'])): ?>
			<h2 class="widget-title heading-style-1">
				<span><?php echo esc_html($atts['title']); ?></span>
			</h2>
		<?php endif; ?>

		<div class="img_heading">
			<img decoding="async" src="https://vitaminkoo.com/wp-content/themes/gdcv/img/HT.png">
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
									$template = ($atts['style'] === 'style-1') ? 'widgets/recent-manga/content-completed' : 'widgets/recent-manga/content-2';
									$wp_manga_template->load_template($template, false);
									?>
								</div>
							</div>

						<?php endwhile; ?>

					</div>
				</div>
			</div>

			<?php if (!empty($atts['button_text'])): ?>
				<div class="view-more">
					<a href="<?php echo esc_url($atts['button_url']); ?>">
						<?php echo esc_html($atts['button_text']); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

		<?php else: ?>
			<p>Không có truyện hoàn thành nào.</p>
		<?php endif; ?>

	</div>

	<?php
	return ob_get_clean();
});

/*************************************************
 * CUSTOM FOOTER BLOCK ADMIN + MEDIA PICKER
 *************************************************/

add_action('admin_menu', function () {
	add_theme_page(
		'Custom Footer Block',
		'Custom Footer Block',
		'manage_options',
		'custom-footer-block',
		'render_custom_footer_admin'
	);
});

add_action('admin_init', function () {
	register_setting('custom_footer_settings', 'custom_footer_image');
	register_setting('custom_footer_settings', 'custom_footer_text');
});

add_action('admin_enqueue_scripts', function ($hook) {
	if ($hook !== 'appearance_page_custom-footer-block')
		return;
	wp_enqueue_media();
});

function render_custom_footer_admin()
{
	$image = get_option('custom_footer_image');
	$text = get_option('custom_footer_text');
	?>
	<div class="wrap">
		<h1>Custom Footer Block</h1>

		<form method="post" action="options.php">
			<?php settings_fields('custom_footer_settings'); ?>

			<table class="form-table">

				<tr>
					<th>Footer Image URL</th>
					<td>
						<input type="hidden" name="custom_footer_image" id="custom_footer_image"
							value="<?php echo esc_attr($image); ?>">

						<button type="button" class="button" id="upload_footer_image">
							Select Image
						</button>

						<div style="margin-top:12px;">
							<img id="footer_image_preview" src="<?php echo esc_url($image); ?>"
								style="max-width:240px;border-radius:12px;">
						</div>
					</td>
				</tr>

				<tr>
					<th>Footer Content</th>
					<td>
						<textarea name="custom_footer_text" rows="7" style="width:100%;"><?php
						echo esc_textarea($text);
						?></textarea>
						<p class="description">HTML allowed</p>
					</td>
				</tr>

			</table>

			<?php submit_button(); ?>
		</form>
	</div>

	<script>
		jQuery(document).ready(function ($) {
			$('#upload_footer_image').on('click', function (e) {
				e.preventDefault();

				const frame = wp.media({
					title: 'Select Footer Image',
					button: { text: 'Use this image' },
					multiple: false
				});

				frame.on('select', function () {
					const attachment = frame.state().get('selection').first().toJSON();
					$('#custom_footer_image').val(attachment.url);
					$('#footer_image_preview').attr('src', attachment.url);
				});

				frame.open();
			});
		});
	</script>
	<?php
}

// 5️⃣ Hide default Madara footer visually
add_action('wp_head', function () {
	echo '<style>.site-footer { display:none !important; }</style>';
});

add_action('get_footer', function () {

	if (is_404())
		return;

	$image = get_option('custom_footer_image') ?: 'https://vitaminkoo.com/wp-content/themes/gdcv/img/footer.png';

	echo '
	<div class="custom-footer-image-block">
		<div class="container">
			<img src="' . esc_url($image) . '" loading="lazy" class="footer-top-image">
		</div>
	</div>';
});


add_action('wp_footer', function () {

	if (is_404())
		return;

	$text = get_option('custom_footer_text') ?: '
		Các thông tin và hình ảnh được đăng tải trên website đều được sưu tầm từ Internet, bao gồm quyền sử dụng phi thương mại và có phí. 
		Chúng tôi không sở hữu hay chịu trách nhiệm đối với bất kỳ nội dung hoặc hình ảnh nào trên trang web. 
		Nếu có nội dung nào ảnh hưởng đến cá nhân hay tổ chức, vui lòng liên hệ Telegram 
		<strong>@gdcvvitaminkoo</strong>.
	';

	echo '
	<footer class="custom-footer-text-block">
		<div class="container">
			<div class="note text-justify">' . wp_kses_post($text) . '</div>
		</div>
	</footer>';
});
