<?php
/**
 * The Header for our child theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package madara
 */

use App\Madara;

$madara_header_style = apply_filters('madara_header_style', Madara::getOption('header_style', 1));
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php
    if (function_exists('wp_body_open')) {
        wp_body_open();
    } else {
        do_action('wp_body_open');
    }

    if (!is_404()) {

        do_action('madara_before_body');

        $minimal_reading_page = Madara::getOption('minimal_reading_page', 'off');
        $madara_ajax_search = Madara::getOption('madara_ajax_search', 'on');
        ?>

        <div class="wrap">
            <div class="body-wrap">
                <?php if (!(function_exists('is_manga_reading_page') && is_manga_reading_page()) || $minimal_reading_page == 'off') { ?>
                    <header class="site-header mad-custom-header">

                        <!-- Block 1: Orange Top Bar -->
                        <div class="mad-header-block-1">
                            <div class="container-fluid">
                                <div class="mad-header-row">
                                    <!-- Left: Mobile Toggle & Search -->
                                    <div class="mad-header-left">
                                        <div class="c-togle__menu">
                                            <button aria-label="open" type="button" class="menu_icon__open">
                                                <span></span> <span></span> <span></span>
                                            </button>
                                        </div>
                                        <div class="search-navigation search-sidebar mad-custom-search-wrap">
                                            <div class="search-navigation__wrap">
                                                <ul class="main-menu-search nav-menu">
                                                    <li class="menu-search">
                                                        <a href="javascript:;" class="open-search-main-menu">
                                                            <i class="icon ion-ios-search"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Center: Logo -->
                                    <div class="mad-header-center">
                                        <div class="wrap_branding">
                                            <a class="logo" href="<?php echo esc_url(home_url('/')); ?>"
                                                title="<?php echo esc_attr(get_bloginfo('name')); ?>">
                                                <?php
                                                $logo = Madara::getOption('logo_image', '');
                                                $logo = !empty($logo) ? esc_url($logo) : esc_url(get_parent_theme_file_uri() . '/images/logo.png');
                                                ?>
                                                <img class="img-responsive" src="<?php echo esc_url($logo); ?>"
                                                    alt="<?php echo esc_attr(get_bloginfo('name')); ?>" />
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Right: User Section -->
                                    <div class="mad-header-right">
                                        <div class="c-sub-header-nav no-subnav">
                                            <div class="c-sub-nav_wrap">
                                                <?php
                                                $header_login_buttons = Madara::getOption('header_disable_login_buttons', 'on');
                                                $user_enabled = ($header_login_buttons == 'on') && !is_user_logged_in() && get_option('users_can_register');
                                                $user_manga_logged = is_user_logged_in() && class_exists('WP_MANGA');
                                                global $wp_manga_user_actions;
                                                ?>

                                                <?php if ($user_manga_logged) { ?>
                                                    <div class="mad-user-section c-modal_item">
                                                        <i class="icon ion-ios-bell"></i>
                                                        <?php
                                                        if (defined('WP_MANGA_VER') && WP_MANGA_VER >= 1.6) {
                                                            $wp_manga_user_actions->get_user_section(50, true);
                                                        } else {
                                                            echo wp_kses_post($wp_manga_user_actions->get_user_section());
                                                        }
                                                        ?>
                                                    </div>
                                                <?php } elseif ($user_enabled) { ?>
                                                    <div class="mad-user-section c-modal_item">
                                                        <i class="icon ion-ios-bell"></i>
                                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#form-login"
                                                            class="btn-active-modal btn-login"><?php echo esc_html__('Đăng nhập', 'madara'); ?></a>
                                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#form-sign-up"
                                                            class="btn-active-modal btn-register"><?php echo esc_html__('Đăng ký', 'madara'); ?></a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Block 2: White Bottom Bar (Navigation) -->
                        <div class="mad-header-block-2">
                            <div
                                class="container-fluid <?php echo esc_attr($madara_header_style == '2' ? 'custom-width' : ''); ?>">
                                <div class="main-navigation style-1">
                                    <div class="main-navigation_wrap">
                                        <div class="main-menu">
                                            <?php
                                            if (has_nav_menu('primary_menu')) {
                                                wp_nav_menu(array(
                                                    'theme_location' => 'primary_menu',
                                                    'container' => false,
                                                    'menu_class' => 'main-nav nav-menu list-inline',
                                                    'walker' => new App\Plugins\Walker_Nav_Menu\Custom_Walker_Nav_Menu()
                                                ));
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Navigation -->
                        <?php get_template_part('html/header/mobile-navigation'); ?>

                        <!-- Hidden Search Form Container (Maintained for JS) -->
                        <div class="c-header__top" style="display:none;">
                            <ul class="search-main-menu">
                                <li>
                                    <form id="blog-post-search"
                                        class="<?php echo (esc_html($madara_ajax_search) == 'on' ? 'ajax' : ''); ?>"
                                        action="<?php echo esc_url(home_url('/')); ?>" method="get">
                                        <input type="text" placeholder="<?php echo esc_html__('Search...', 'madara'); ?>"
                                            name="s" value="">
                                        <input type="submit" value="<?php esc_html_e('Search', 'madara'); ?>">
                                        <div class="loader-inner line-scale">
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                    </form>
                                </li>
                            </ul>
                        </div>

                    </header>

                    <?php get_template_part('html/main-top'); ?>
                <?php } ?>

                <div class="site-content">
                    <?php do_action('madara_before_body_content'); ?>
                <?php } ?>