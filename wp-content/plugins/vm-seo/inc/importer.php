<?php
/**
 * VM SEO - 1-Click Yoast SEO & Third-Party SEO Importer & Migrator
 *
 * @package VMSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VM_SEO_Importer {

	public static function init() {
		add_action( 'wp_ajax_vm_seo_import_yoast', array( __CLASS__, 'ajax_import_yoast_data' ) );
		add_action( 'wp_ajax_vm_seo_import_rankmath', array( __CLASS__, 'ajax_import_rankmath_data' ) );
	}

	/**
	 * Detect if Yoast SEO or Rank Math data exists in database
	 */
	public static function detect_sources() {
		global $wpdb;

		$yoast_count = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} 
			 WHERE meta_key IN ('_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_yoast_wpseo_focuskw', '_yoast_wpseo_canonical')"
		);

		$rankmath_count = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} 
			 WHERE meta_key IN ('rank_math_title', 'rank_math_description', 'rank_math_focus_keyword', 'rank_math_canonical_url')"
		);

		return array(
			'yoast_count'    => $yoast_count,
			'rankmath_count' => $rankmath_count,
		);
	}

	/**
	 * AJAX Handler: Import all Yoast SEO post meta into VM SEO
	 */
	public static function ajax_import_yoast_data() {
		check_ajax_referer( 'vm_seo_import_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'vm-seo' ) );
		}

		global $wpdb;

		// Get all posts with Yoast SEO data
		$post_ids = $wpdb->get_col(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
			 WHERE meta_key IN ('_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_yoast_wpseo_focuskw', '_yoast_wpseo_canonical', '_yoast_wpseo_meta-robots-noindex', '_yoast_wpseo_meta-robots-nofollow', '_yoast_wpseo_opengraph-title', '_yoast_wpseo_opengraph-description', '_yoast_wpseo_opengraph-image')"
		);

		if ( empty( $post_ids ) ) {
			wp_send_json_error( __( 'No Yoast SEO data found in database to import.', 'vm-seo' ) );
		}

		$imported = 0;

		foreach ( $post_ids as $pid ) {
			$yoast_title      = get_post_meta( $pid, '_yoast_wpseo_title', true );
			$yoast_desc       = get_post_meta( $pid, '_yoast_wpseo_metadesc', true );
			$yoast_kw         = get_post_meta( $pid, '_yoast_wpseo_focuskw', true );
			$yoast_canonical  = get_post_meta( $pid, '_yoast_wpseo_canonical', true );
			$yoast_noindex    = get_post_meta( $pid, '_yoast_wpseo_meta-robots-noindex', true );
			$yoast_nofollow   = get_post_meta( $pid, '_yoast_wpseo_meta-robots-nofollow', true );
			$yoast_og_title   = get_post_meta( $pid, '_yoast_wpseo_opengraph-title', true );
			$yoast_og_desc    = get_post_meta( $pid, '_yoast_wpseo_opengraph-description', true );
			$yoast_og_image   = get_post_meta( $pid, '_yoast_wpseo_opengraph-image', true );

			if ( ! empty( $yoast_title ) ) {
				update_post_meta( $pid, '_vm_seo_title', sanitize_text_field( $yoast_title ) );
			}
			if ( ! empty( $yoast_desc ) ) {
				update_post_meta( $pid, '_vm_seo_desc', sanitize_textarea_field( $yoast_desc ) );
			}
			if ( ! empty( $yoast_kw ) ) {
				update_post_meta( $pid, '_vm_focus_keyword', sanitize_text_field( $yoast_kw ) );
			}
			if ( ! empty( $yoast_canonical ) ) {
				update_post_meta( $pid, '_vm_seo_canonical', esc_url_raw( $yoast_canonical ) );
			}
			if ( $yoast_noindex === '1' || $yoast_noindex === 'yes' ) {
				update_post_meta( $pid, '_vm_seo_noindex', 'yes' );
			}
			if ( $yoast_nofollow === '1' || $yoast_nofollow === 'yes' ) {
				update_post_meta( $pid, '_vm_seo_nofollow', 'yes' );
			}
			if ( ! empty( $yoast_og_title ) ) {
				update_post_meta( $pid, '_vm_og_title', sanitize_text_field( $yoast_og_title ) );
			}
			if ( ! empty( $yoast_og_desc ) ) {
				update_post_meta( $pid, '_vm_og_desc', sanitize_textarea_field( $yoast_og_desc ) );
			}
			if ( ! empty( $yoast_og_image ) ) {
				update_post_meta( $pid, '_vm_og_image', esc_url_raw( $yoast_og_image ) );
			}

			// Compute initial score
			$score = 50;
			if ( ! empty( $yoast_kw ) ) $score += 20;
			if ( ! empty( $yoast_title ) ) $score += 15;
			if ( ! empty( $yoast_desc ) ) $score += 15;
			update_post_meta( $pid, '_vm_seo_score', min( 100, $score ) );

			$imported++;
		}

		wp_send_json_success( array(
			'message'  => sprintf( __( 'Successfully migrated SEO data from Yoast SEO for %d titles!', 'vm-seo' ), $imported ),
			'imported' => $imported,
		) );
	}

	/**
	 * AJAX Handler: Import Rank Math data
	 */
	public static function ajax_import_rankmath_data() {
		check_ajax_referer( 'vm_seo_import_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'vm-seo' ) );
		}

		global $wpdb;

		$post_ids = $wpdb->get_col(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
			 WHERE meta_key IN ('rank_math_title', 'rank_math_description', 'rank_math_focus_keyword', 'rank_math_canonical_url')"
		);

		if ( empty( $post_ids ) ) {
			wp_send_json_error( __( 'No Rank Math data found in database to import.', 'vm-seo' ) );
		}

		$imported = 0;

		foreach ( $post_ids as $pid ) {
			$title     = get_post_meta( $pid, 'rank_math_title', true );
			$desc      = get_post_meta( $pid, 'rank_math_description', true );
			$focus_kw  = get_post_meta( $pid, 'rank_math_focus_keyword', true );
			$canonical = get_post_meta( $pid, 'rank_math_canonical_url', true );

			if ( ! empty( $title ) ) {
				$title = preg_replace( '/%([a-zA-Z0-9_-]+)%/', '%%$1%%', $title );
				update_post_meta( $pid, '_vm_seo_title', sanitize_text_field( $title ) );
			}
			if ( ! empty( $desc ) ) {
				$desc = preg_replace( '/%([a-zA-Z0-9_-]+)%/', '%%$1%%', $desc );
				update_post_meta( $pid, '_vm_seo_desc', sanitize_textarea_field( $desc ) );
			}
			if ( ! empty( $focus_kw ) ) {
				update_post_meta( $pid, '_vm_focus_keyword', sanitize_text_field( $focus_kw ) );
			}
			if ( ! empty( $canonical ) ) {
				update_post_meta( $pid, '_vm_seo_canonical', esc_url_raw( $canonical ) );
			}

			$score = 50;
			if ( ! empty( $focus_kw ) ) $score += 20;
			if ( ! empty( $title ) ) $score += 15;
			if ( ! empty( $desc ) ) $score += 15;
			update_post_meta( $pid, '_vm_seo_score', min( 100, $score ) );

			$imported++;
		}

		wp_send_json_success( array(
			'message'  => sprintf( __( 'Successfully migrated SEO data from Rank Math for %d titles!', 'vm-seo' ), $imported ),
			'imported' => $imported,
		) );
	}
}

VM_SEO_Importer::init();
