<?php
	add_action( 'wp_enqueue_scripts', 'madara_scripts_styles_child_theme' );
	function madara_scripts_styles_child_theme() {
		$theme = wp_get_theme();
		wp_enqueue_style( 'madara-css', get_template_directory_uri() . '/style.css', 
		array(
			'bootstrap',
			'slick',
			'slick-theme'
		), 
			$theme->parent()->get('Version')
		);

		if ( is_rtl() ) {
			wp_enqueue_style( 'madara-rtl', get_template_directory_uri() . '/rtl.css' );
		}
		
		wp_enqueue_style( 'madara-css-child', get_stylesheet_directory_uri() . '/style.css', array(
			'madara-css'
		), '1.0.4' );
	}
	
	/* Disable VC auto-update */
	add_action( 'admin_init', 'madara_vc_disable_update', 9 );
	function madara_vc_disable_update() {
		if ( function_exists( 'vc_license' ) && function_exists( 'vc_updater' ) && ! vc_license()->isActivated() ) {

			remove_filter( 'upgrader_pre_download', array( vc_updater(), 'preUpgradeFilter' ), 10 );
			remove_filter( 'pre_set_site_transient_update_plugins', array(
				vc_updater()->updateManager(),
				'check_update'
			) );

		}
	}
    
    /**
     * does not support Widgets Block Editor yet
     **/
    function madara_theme_support() {
        remove_theme_support( 'widgets-block-editor' );
    }
    add_action( 'after_setup_theme', 'madara_theme_support' );
/**
 * FIX: Force Madara shortcodes to render on Homepage
 * Prevent [madara_manga] from being printed as raw text
 */
add_filter( 'the_content', function ( $content ) {

    // Do nothing in admin
    if ( is_admin() ) {
        return $content;
    }

    // Only process if Madara shortcode exists
    if ( has_shortcode( $content, 'madara_manga' ) ) {
        $content = do_shortcode( $content );
    }

    return $content;

}, 20 );

/**
 * Enable shortcodes in widgets (Madara sidebars safety)
 */
add_filter( 'widget_text', 'do_shortcode' );
add_filter( 'widget_text_content', 'do_shortcode' );
