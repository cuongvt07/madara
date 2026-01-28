<?php
/**
 * Template part for displaying a single manga item in New Updates Widget
 * Layout: Grid Item (page-item-detail)
 */

$settings = get_query_var('madara_widget_settings');
$col_class = isset($settings['col_class']) ? $settings['col_class'] : 'col-4';
$show_badges = isset($settings['show_badges']) ? $settings['show_badges'] : true;
$show_chapter_count = isset($settings['show_chapter_count']) ? $settings['show_chapter_count'] : true;
$show_update_date = isset($settings['show_update_date']) ? $settings['show_update_date'] : true;
$style = isset($settings['style']) ? $settings['style'] : 'style-1';

$post_id = get_the_ID();
$link = get_the_permalink();
$title = get_the_title();
$post_title = $title;
$post_url = $link;
$wp_manga_functions = madara_get_global_wp_manga_functions();

// Ensure number of chapters is set
global $widget_setting_number_chapters;
if (!isset($widget_setting_number_chapters)) {
    $widget_setting_number_chapters = 2;
}
?>

<div class="<?php echo esc_attr($col_class); ?> page-listing-item internal-widget">
    <div class="popular-item-wrap">

        <?php
        global $wp_manga_template;
        if ($style == 'style-1') {
            $wp_manga_template->load_template('widgets/recent-manga/content-1', false);
        } else {
            $wp_manga_template->load_template('widgets/recent-manga/content-2', false);
        } ?>

    </div>
</div>