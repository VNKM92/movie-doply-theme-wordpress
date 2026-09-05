<?php
/**
 * Customizer and Theme Options for DoodhTheme
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Customizer Settings
 */
function doodhtheme_customize_register( $wp_customize ) {
	// Section: Theme Appearance & Colors
	$wp_customize->add_section( 'doodhtheme_design_section', array(
		'title'       => __( 'DoodhTheme Styling & Colors', 'doodhtheme' ),
		'priority'    => 30,
		'description' => __( 'Customize theme branding and accent colors.', 'doodhtheme' ),
	) );

	// Accent Color
	$wp_customize->add_setting( 'doodh_accent_color', array(
		'default'           => '#e50914',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'doodh_accent_color', array(
		'label'    => __( 'Primary Accent Color', 'doodhtheme' ),
		'section'  => 'doodhtheme_design_section',
		'settings' => 'doodh_accent_color',
	) ) );

	// Section: Homepage Hero & Slider
	$wp_customize->add_section( 'doodhtheme_hero_section', array(
		'title'       => __( 'Homepage Hero Banner', 'doodhtheme' ),
		'priority'    => 35,
		'description' => __( 'Configure the top featured slider / hero banner.', 'doodhtheme' ),
	) );

	// Hero Title
	$wp_customize->add_setting( 'doodh_hero_title', array(
		'default'           => 'Unlimited Movies, TV Shows, & More',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'doodh_hero_title', array(
		'label'    => __( 'Hero Main Title', 'doodhtheme' ),
		'section'  => 'doodhtheme_hero_section',
		'type'     => 'text',
	) );

	// Hero Subtitle
	$wp_customize->add_setting( 'doodh_hero_subtitle', array(
		'default'           => 'Watch anywhere. Stream full HD movies and seasons online for free.',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'doodh_hero_subtitle', array(
		'label'    => __( 'Hero Subtitle', 'doodhtheme' ),
		'section'  => 'doodhtheme_hero_section',
		'type'     => 'textarea',
	) );

	// Section: Footer & DMCA Disclaimer
	$wp_customize->add_section( 'doodhtheme_footer_section', array(
		'title'       => __( 'Footer & Legal Disclaimer', 'doodhtheme' ),
		'priority'    => 40,
	) );

	// DMCA Notice
	$wp_customize->add_setting( 'doodh_dmca_text', array(
		'default'           => 'Disclaimer: This site does not store any files on its server. All contents are provided by non-affiliated third parties.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'doodh_dmca_text', array(
		'label'    => __( 'DMCA / Legal Notice', 'doodhtheme' ),
		'section'  => 'doodhtheme_footer_section',
		'type'     => 'textarea',
	) );

	// Copyright Text
	$wp_customize->add_setting( 'doodh_copyright_text', array(
		'default'           => '© 2026 DoodhTheme. All rights reserved.',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'doodh_copyright_text', array(
		'label'    => __( 'Copyright Text', 'doodhtheme' ),
		'section'  => 'doodhtheme_footer_section',
		'type'     => 'text',
	) );
}
add_action( 'customize_register', 'doodhtheme_customize_register' );
