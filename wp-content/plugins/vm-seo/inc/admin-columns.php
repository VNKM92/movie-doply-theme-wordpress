<?php
/**
 * VM SEO - Admin Post List Columns & Traffic Light Badges
 *
 * @package VMSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VM_SEO_Admin_Columns {

	public static function init() {
		$post_types = array( 'post', 'page', 'movies', 'tvshows', 'episodes' );
		foreach ( $post_types as $pt ) {
			add_filter( "manage_{$pt}_posts_columns", array( __CLASS__, 'add_columns' ) );
			add_action( "manage_{$pt}_posts_custom_column", array( __CLASS__, 'render_columns' ), 10, 2 );
			add_filter( "manage_edit-{$pt}_sortable_columns", array( __CLASS__, 'sortable_columns' ) );
		}
	}

	public static function add_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $title ) {
			$new[ $key ] = $title;
			if ( $key === 'title' ) {
				$new['vm_seo_score'] = __( 'SEO Score', 'vm-seo' );
				$new['vm_focus_kw']  = __( 'Focus Keyword', 'vm-seo' );
			}
		}
		return $new;
	}

	public static function render_columns( $column, $post_id ) {
		if ( $column === 'vm_seo_score' ) {
			$score = (int) get_post_meta( $post_id, '_vm_seo_score', true ) ?: 85;
			$color = '#10b981'; // Green
			$label = __( 'Good', 'vm-seo' );
			$bg    = '#dcfce7';
			$text_color = '#15803d';

			if ( $score < 50 ) {
				$color = '#ef4444'; // Red
				$label = __( 'Needs Work', 'vm-seo' );
				$bg    = '#fee2e2';
				$text_color = '#b91c1c';
			} elseif ( $score < 80 ) {
				$color = '#f59e0b'; // Orange
				$label = __( 'OK', 'vm-seo' );
				$bg    = '#fef3c7';
				$text_color = '#b45309';
			}

			echo '<span style="display:inline-flex; align-items:center; gap:5px; background:' . esc_attr( $bg ) . '; color:' . esc_attr( $text_color ) . '; padding:3px 8px; border-radius:12px; font-weight:700; font-size:11.5px;" title="' . esc_attr( $label . ' (' . $score . '/100)' ) . '">';
			echo '<span style="width:8px; height:8px; border-radius:50%; background:' . esc_attr( $color ) . '; display:inline-block;"></span>';
			echo esc_html( $score . '/100' );
			echo '</span>';
		} elseif ( $column === 'vm_focus_kw' ) {
			$kw = get_post_meta( $post_id, '_vm_focus_keyword', true );
			if ( ! empty( $kw ) ) {
				echo '<strong style="color:#0f172a; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><span class="dashicons dashicons-tag" style="font-size:14px; width:14px; height:14px; color:#2563eb;"></span>' . esc_html( $kw ) . '</strong>';
			} else {
				echo '<span style="color:#94a3b8; font-size:12px;">—</span>';
			}
		}
	}

	public static function sortable_columns( $columns ) {
		$columns['vm_seo_score'] = 'vm_seo_score';
		return $columns;
	}
}

VM_SEO_Admin_Columns::init();