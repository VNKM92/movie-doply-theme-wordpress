<?php
/**
 * Doodh SEO - Admin Post List Columns & Traffic Light Badges
 *
 * @package DoodhSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Doodh_SEO_Admin_Columns {

	public static function init() {
		$post_types = array( 'post', 'page', 'movies', 'tvshows' );
		foreach ( $post_types as $pt ) {
			add_filter( "manage_{$pt}_posts_columns", array( __CLASS__, 'add_columns' ) );
			add_action( "manage_{$pt}_posts_custom_column", array( __CLASS__, 'render_columns' ), 10, 2 );
		}
	}

	public static function add_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $title ) {
			$new[ $key ] = $title;
			if ( $key === 'title' ) {
				$new['doodh_seo_score'] = __( 'SEO Score', 'doodh-seo' );
				$new['doodh_focus_kw']  = __( 'Focus Keyword', 'doodh-seo' );
			}
		}
		return $new;
	}

	public static function render_columns( $column, $post_id ) {
		if ( $column === 'doodh_seo_score' ) {
			$score = (int) get_post_meta( $post_id, '_doodh_seo_score', true ) ?: 85;
			$color = '#10b981'; // Green
			$label = __( 'Good', 'doodh-seo' );
			if ( $score < 60 ) {
				$color = '#ef4444'; // Red
				$label = __( 'Needs Work', 'doodh-seo' );
			} elseif ( $score < 80 ) {
				$color = '#f59e0b'; // Orange
				$label = __( 'OK', 'doodh-seo' );
			}

			echo '<span style="display:inline-flex; align-items:center; gap:6px; background:rgba(0,0,0,0.04); padding:3px 8px; border-radius:12px; font-weight:700; font-size:12px;">';
			echo '<span style="width:10px; height:10px; border-radius:50%; background:' . esc_attr( $color ) . '; display:inline-block;"></span>';
			echo esc_html( $score . '/100' );
			echo '</span>';
		} elseif ( $column === 'doodh_focus_kw' ) {
			$kw = get_post_meta( $post_id, '_doodh_focus_keyword', true );
			if ( ! empty( $kw ) ) {
				echo '<strong style="color:#0f172a; font-size:12px;">' . esc_html( $kw ) . '</strong>';
			} else {
				echo '<span style="color:#94a3b8; font-size:12px;">—</span>';
			}
		}
	}
}

Doodh_SEO_Admin_Columns::init();