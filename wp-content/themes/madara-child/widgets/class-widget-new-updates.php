<?php

class Madara_Child_Widget_New_Updates extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'madara_child_new_updates_widget', // Base ID
            esc_html__('WP Manga: New Updates Grid', 'madara'), // Name
            array('description' => esc_html__('High-performance grid of latest updated manga.', 'madara'), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     */
    public function widget($args, $instance)
    {
        // Default settings
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $posts_per_page = isset($instance['posts_per_page']) ? absint($instance['posts_per_page']) : 10;
        $columns = isset($instance['columns']) ? absint($instance['columns']) : 3;
        $show_chapter_count = isset($instance['show_chapter_count']) ? (bool) $instance['show_chapter_count'] : true;
        $show_update_date = isset($instance['show_update_date']) ? (bool) $instance['show_update_date'] : true;
        $show_badges = isset($instance['show_badges']) ? (bool) $instance['show_badges'] : true;
        $enable_pagination = isset($instance['enable_pagination']) ? (bool) $instance['enable_pagination'] : false;
        $style = isset($instance['style']) ? $instance['style'] : 'style-1';
        $paged = 1;

        if ($enable_pagination && get_query_var('paged')) {
            $paged = get_query_var('paged');
        } else if ($enable_pagination && get_query_var('page')) {
            $paged = get_query_var('page');
        }

        // Enforce max 50
        if ($posts_per_page > 50)
            $posts_per_page = 50;
        if ($posts_per_page < 1)
            $posts_per_page = 10;

        // Cache Key
        $cache_key = 'madara_new_upd_' . md5(serialize($instance) . $paged);
        $cached_output = get_transient($cache_key);

        echo $args['before_widget'];

        if (true) {
            echo '<div class="img_heading"><img decoding="async" src="https://vitaminkoo.com/wp-content/themes/gdcv/img/MCN.png"></div>';
        }

        if (false !== $cached_output && !is_user_logged_in() && !$this->is_debug() && false) { // Cache disabled for testing
            echo $cached_output;
            echo $args['after_widget'];
            return;
        }

        // Query Args
        $query_args = array(
            'post_type' => 'wp-manga',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'orderby' => 'meta_value_num',
            'meta_key' => '_latest_update',
            'order' => 'DESC',
            'ignore_sticky_posts' => true,
            'no_found_rows' => !$enable_pagination, // Optimize count
            'update_post_term_cache' => false, // Disable if not using terms, but might break badget filters? keeping false for perf unless needed
            'update_post_meta_cache' => true,
        );

        // Category Filter
        if (!empty($instance['manga_genres'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'wp-manga-genre',
                'field' => 'slug',
                'terms' => $instance['manga_genres'],
            );
            $query_args['update_post_term_cache'] = true;
        }

        $q = new WP_Query($query_args);

        ob_start();

        if ($q->have_posts()) {
            // Preload standard meta if needed, but WP_Query handles main meta.
            // Precompute column class
            $col_class = 'col-6 col-md-4 col-lg-' . (12 / $columns);
            if ($columns == 5)
                $col_class = 'col-6 col-md-4 col-lg-2'; // approximate or use custom 20%

            echo '<div class="c-blog-listing mad-new-updates-widget-grid"><div class="c-blog-grid"><div class="row">';

            while ($q->have_posts()) {
                $q->the_post();
                global $post;

                // Pass widget settings to template via global or set_query_var
                set_query_var('madara_widget_settings', [
                    'show_chapter_count' => $show_chapter_count,
                    'show_update_date' => $show_update_date,
                    'show_badges' => $show_badges,
                    'col_class' => $col_class,
                    'style' => $style
                ]);

                get_template_part('template-parts/wp-manga/item-new-update');
            }

            echo '</div></div></div>';

            // Pagination
            if ($enable_pagination) {
                $this->render_pagination($q, $paged);
            }

            wp_reset_postdata();
        } else {
            echo '<p>' . esc_html__('No updates found.', 'madara') . '</p>';
        }

        $output = ob_get_clean();

        // Set Transient (15 mins)
        set_transient($cache_key, $output, 15 * MINUTE_IN_SECONDS);

        echo $output;
        echo $args['after_widget'];
    }

    /**
     * Backend Widget Form
     */
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : esc_html__('New Updates', 'madara');
        $posts_per_page = isset($instance['posts_per_page']) ? absint($instance['posts_per_page']) : 10;
        $columns = isset($instance['columns']) ? absint($instance['columns']) : 3;
        $show_chapter_count = isset($instance['show_chapter_count']) ? (bool) $instance['show_chapter_count'] : true;
        $show_update_date = isset($instance['show_update_date']) ? (bool) $instance['show_update_date'] : true;
        $show_badges = isset($instance['show_badges']) ? (bool) $instance['show_badges'] : true;
        $enable_pagination = isset($instance['enable_pagination']) ? (bool) $instance['enable_pagination'] : false;
        $style = isset($instance['style']) ? $instance['style'] : 'style-1';
        $manga_genres = isset($instance['manga_genres']) ? $instance['manga_genres'] : array();

        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'madara'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>">
                <?php esc_html_e('Posts per page (max 50):', 'madara'); ?>
            </label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>"
                name="<?php echo esc_attr($this->get_field_name('posts_per_page')); ?>" type="number" step="1" min="1" max="50"
                value="<?php echo esc_attr($posts_per_page); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('columns')); ?>">
                <?php esc_html_e('Columns:', 'madara'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('columns')); ?>"
                name="<?php echo esc_attr($this->get_field_name('columns')); ?>">
                <?php for ($i = 2; $i <= 6; $i++): ?>
                    <option value="<?php echo esc_attr($i); ?>" <?php selected($columns, $i); ?>>
                        <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('style')); ?>">
                <?php esc_html_e('Item Style:', 'madara'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('style')); ?>"
                name="<?php echo esc_attr($this->get_field_name('style')); ?>">
                <option value="style-1" <?php selected($style, 'style-1'); ?>>Style 1 (Thumbnail + Title + Chapters)</option>
                <option value="style-2" <?php selected($style, 'style-2'); ?>>Style 2 (Thumbnail + Title + Date)</option>
            </select>
        </p>
        <p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_badges); ?>
                id="<?php echo esc_attr($this->get_field_id('show_badges')); ?>"
                name="<?php echo esc_attr($this->get_field_name('show_badges')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_badges')); ?>">
                <?php esc_html_e('Show HOT/NEW Badge', 'madara'); ?>
            </label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_chapter_count); ?>
                id="<?php echo esc_attr($this->get_field_id('show_chapter_count')); ?>"
                name="<?php echo esc_attr($this->get_field_name('show_chapter_count')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_chapter_count')); ?>">
                <?php esc_html_e('Show Chapter Count', 'madara'); ?>
            </label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_update_date); ?>
                id="<?php echo esc_attr($this->get_field_id('show_update_date')); ?>"
                name="<?php echo esc_attr($this->get_field_name('show_update_date')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_update_date')); ?>">
                <?php esc_html_e('Show Update Date', 'madara'); ?>
            </label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($enable_pagination); ?>
                id="<?php echo esc_attr($this->get_field_id('enable_pagination')); ?>"
                name="<?php echo esc_attr($this->get_field_name('enable_pagination')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('enable_pagination')); ?>">
                <?php esc_html_e('Enable Pagination', 'madara'); ?>
            </label>
        </p>
        <!-- Add Genre/Taxonomy Selector if needed, simplifying for now -->
        <?php
    }

    /**
     * Update Widget settings
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['posts_per_page'] = (!empty($new_instance['posts_per_page'])) ? absint($new_instance['posts_per_page']) : 10;
        $instance['columns'] = (!empty($new_instance['columns'])) ? absint($new_instance['columns']) : 3;
        $instance['show_chapter_count'] = isset($new_instance['show_chapter_count']) ? (bool) $new_instance['show_chapter_count'] : false;
        $instance['show_update_date'] = isset($new_instance['show_update_date']) ? (bool) $new_instance['show_update_date'] : false;
        $instance['show_badges'] = isset($new_instance['show_badges']) ? (bool) $new_instance['show_badges'] : false;
        $instance['enable_pagination'] = isset($new_instance['enable_pagination']) ? (bool) $new_instance['enable_pagination'] : false;
        $instance['style'] = isset($new_instance['style']) ? $new_instance['style'] : 'style-1';
        $instance['manga_genres'] = isset($new_instance['manga_genres']) ? array_map('sanitize_text_field', $new_instance['manga_genres']) : array();

        // Flush cache on update
        // We use a hashed key, so changing settings automatically invalidates old keys conceptually, 
        // but explicit flushing of related transients could be good. 
        // Since keys are dynamic based on args, we don't know them easily. 
        // Standard TTL expiration is fine.

        return $instance;
    }

    private function render_pagination($query, $paged)
    {
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) {
            $current_page = $paged;
            echo '<div class="mad-pagination text-right">';
            echo paginate_links([
                'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'format' => '?paged=%#%',
                'current' => max(1, $paged),
                'total' => $total_pages,
                'mid_size' => 2,    // ← Số trang hiển thị bên trái/phải trang hiện tại
                'end_size' => 1,    // ← Số trang hiển thị ở đầu và cuối
                'prev_text' => '<i class="icon ion-ios-arrow-back"></i>',
                'next_text' => '<i class="icon ion-ios-arrow-forward"></i>',
            ]);
            echo '</div>';
        }
    }

    private function is_debug()
    {
        return (defined('WP_DEBUG') && WP_DEBUG) || isset($_GET['debug_widget']);
    }
}
