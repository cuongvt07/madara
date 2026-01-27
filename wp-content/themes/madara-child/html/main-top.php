<?php

if (!is_404()) {

    $custom_settings = get_post_meta(get_the_ID(), 'custom_sidebar_settings', true);

    if (class_exists('WP_MANGA') && is_manga()) {
        $manga_main_top_sidebar_container_class = madara_output_sidebar_container_classes('manga_main_top_sidebar_container', 'container', 0, $custom_settings !== 'off');
        $manga_main_top_sidebar_background = madara_output_background_options('manga_main_top_sidebar_background', 0, $custom_settings !== 'off');
        $manga_main_top_sidebar_spacing = madara_output_spacing_options('manga_main_top_sidebar_spacing', '', 0, $custom_settings !== 'off');

        $manga_main_top_second_sidebar_container_class = madara_output_sidebar_container_classes('manga_main_top_second_sidebar_container', 'container', 0, $custom_settings !== 'off');
        $manga_main_top_second_sidebar_background = madara_output_background_options('manga_main_top_second_sidebar_background', 0, $custom_settings !== 'off');

        $manga_main_top_second_sidebar_spacing = madara_output_spacing_options('manga_main_top_second_sidebar_spacing', '', 0, $custom_settings !== 'off');

        ?>

        <?php if (is_active_sidebar('manga_main_top_sidebar')) { ?>
            <div class="c-sidebar c-top-sidebar wp-manga"
                style="<?php echo esc_attr($manga_main_top_sidebar_background != '' || $manga_main_top_sidebar_spacing != '' ? $manga_main_top_sidebar_background . $manga_main_top_sidebar_spacing : ''); ?>">
                <div class="<?php echo esc_attr($manga_main_top_sidebar_container_class); ?>">
                    <div class="row c-row">
                        <?php dynamic_sidebar('manga_main_top_sidebar'); ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if (is_active_sidebar('manga_main_top_second_sidebar')) { ?>
            <div class="c-sidebar c-top-second-sidebar wp-manga"
                style="<?php echo esc_attr($manga_main_top_second_sidebar_background != '' || $manga_main_top_second_sidebar_spacing != '' ? $manga_main_top_second_sidebar_background . $manga_main_top_second_sidebar_spacing : ''); ?>">
                <div class="<?php echo esc_attr($manga_main_top_second_sidebar_container_class); ?>">
                    <div class="row c-row">
                        <?php dynamic_sidebar('manga_main_top_second_sidebar'); ?>
                    </div>
                </div>
            </div>
        <?php } ?>

    <?php } else {
        $main_top_sidebar_container_class = madara_output_sidebar_container_classes('main_top_sidebar_container', 'container', 0, $custom_settings !== 'off');
        $main_top_sidebar_background = madara_output_background_options('main_top_sidebar_background', 0, $custom_settings !== 'off');
        $main_top_sidebar_spacing = madara_output_spacing_options('main_top_sidebar_spacing', '', 0, $custom_settings !== 'off');

        $main_top_second_sidebar_container_class = madara_output_sidebar_container_classes('main_top_second_sidebar_container', 'container', 0, $custom_settings !== 'off');
        $main_top_second_sidebar_background = madara_output_background_options('main_top_second_sidebar_background', 0, $custom_settings !== 'off');
        $main_top_second_sidebar_spacing = madara_output_spacing_options('main_top_second_sidebar_spacing', '', 0, $custom_settings !== 'off');

        ?>

        <?php if (is_active_sidebar('top_sidebar')) { ?>
            <!-- Vertical Swiper Carousel with Coverflow Effect -->
            <section class="mad-carousel-section"
                style="<?php echo esc_attr($main_top_sidebar_background != '' || $main_top_sidebar_spacing != '' ? $main_top_sidebar_background . $main_top_sidebar_spacing : ''); ?>">
                <!-- Container rộng bằng header -->
                <div class="container">
                    <!-- Hidden widget output for extraction (CRITICAL: Must be completely hidden) -->
                    <div class="mad-widget-source"
                        style="display:none !important; visibility:hidden !important; position:absolute !important; left:-9999px !important;">
                        <?php dynamic_sidebar('top_sidebar'); ?>
                    </div>

                    <!-- Swiper Carousel Container (CRITICAL: Proper structure) -->
                    <div class="swiper mad-vertical-carousel">
                        <div class="swiper-wrapper">
                            <!-- Slides will be populated by JavaScript -->
                        </div>

                        <!-- Navigation Buttons (CRITICAL: Proper selectors) -->
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            </section>
        <?php } ?>


        <?php if (is_active_sidebar('top_second_sidebar')) { ?>
            <div class="c-sidebar c-top-second-sidebar"
                style="<?php echo esc_attr($main_top_second_sidebar_background != '' || $main_top_second_sidebar_spacing != '' ? $main_top_second_sidebar_background . $main_top_second_sidebar_spacing : ''); ?>">
                <div class="<?php echo esc_attr($main_top_second_sidebar_container_class); ?>">
                    <div class="row c-row">
                        <?php dynamic_sidebar('top_second_sidebar'); ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php

    }
}