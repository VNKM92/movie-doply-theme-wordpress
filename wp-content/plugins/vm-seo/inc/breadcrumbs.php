<?php
/**
 * VM SEO - Breadcrumbs Generator with Schema.org Microdata & JSON-LD
 *
 * @package VMSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VM_SEO_Breadcrumbs {

	public static function render( $custom_sep = null ) {
		$options = VM_SEO_Master::get_instance()->options;
		$sep = $custom_sep ?: ( $options['breadcrumbs_separator'] ?? '&rsaquo;' );
		$home_text = $options['breadcrumbs_home_text'] ?? __( 'Home', 'vm-seo' );

		if ( is_front_page() || is_home() ) {
			return '';
		}

		$crumbs = array();
		$crumbs[] = array( 'title' => $home_text, 'url' => home_url( '/' ) );

		if ( is_singular( 'movies' ) ) {
			$archive_link = get_post_type_archive_link( 'movies' ) ?: home_url( '/movies/' );
			$crumbs[] = array( 'title' => __( 'Movies', 'vm-seo' ), 'url' => $archive_link );

			$terms = get_the_terms( get_the_ID(), 'genres' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$crumbs[] = array( 'title' => $terms[0]->name, 'url' => get_term_link( $terms[0] ) );
			}
			$crumbs[] = array( 'title' => get_the_title(), 'url' => '' );
		} elseif ( is_singular( 'tvshows' ) ) {
			$archive_link = get_post_type_archive_link( 'tvshows' ) ?: home_url( '/tvshows/' );
			$crumbs[] = array( 'title' => __( 'TV Shows', 'vm-seo' ), 'url' => $archive_link );

			$terms = get_the_terms( get_the_ID(), 'genres' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$crumbs[] = array( 'title' => $terms[0]->name, 'url' => get_term_link( $terms[0] ) );
			}
			$crumbs[] = array( 'title' => get_the_title(), 'url' => '' );
		} elseif ( is_singular( 'episodes' ) ) {
			$crumbs[] = array( 'title' => __( 'TV Shows', 'vm-seo' ), 'url' => home_url( '/tvshows/' ) );
			$tv_id = get_post_meta( get_the_ID(), '_vm_tv_id', true );
			if ( $tv_id ) {
				$crumbs[] = array( 'title' => get_the_title( $tv_id ), 'url' => get_permalink( $tv_id ) );
			}
			$crumbs[] = array( 'title' => get_the_title(), 'url' => '' );
		} elseif ( is_singular( 'post' ) ) {
			$cats = get_the_category();
			if ( ! empty( $cats ) ) {
				$crumbs[] = array( 'title' => $cats[0]->name, 'url' => get_category_link( $cats[0] ) );
			}
			$crumbs[] = array( 'title' => get_the_title(), 'url' => '' );
		} elseif ( is_page() ) {
			$ancestors = get_post_ancestors( get_the_ID() );
			if ( ! empty( $ancestors ) ) {
				$ancestors = array_reverse( $ancestors );
				foreach ( $ancestors as $anc_id ) {
					$crumbs[] = array( 'title' => get_the_title( $anc_id ), 'url' => get_permalink( $anc_id ) );
				}
			}
			$crumbs[] = array( 'title' => get_the_title(), 'url' => '' );
		} elseif ( is_category() || is_tax() || is_tag() ) {
			$term = get_queried_object();
			if ( $term ) {
				$crumbs[] = array( 'title' => $term->name, 'url' => '' );
			}
		} elseif ( is_search() ) {
			$crumbs[] = array( 'title' => sprintf( __( 'Search for "%s"', 'vm-seo' ), get_search_query() ), 'url' => '' );
		} elseif ( is_404() ) {
			$crumbs[] = array( 'title' => __( 'Error 404 - Not Found', 'vm-seo' ), 'url' => '' );
		}

		$output = '<nav class="vm-seo-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'vm-seo' ) . '">';
		$output .= '<ol itemscope itemtype="https://schema.org/BreadcrumbList" style="display:flex; flex-wrap:wrap; list-style:none; padding:0; margin:0; font-size:13px; gap:6px; align-items:center;">';

		foreach ( $crumbs as $idx => $crumb ) {
			$pos = $idx + 1;
			$is_last = ( $idx === count( $crumbs ) - 1 || empty( $crumb['url'] ) );

			$output .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" style="display:inline-flex; align-items:center; gap:6px;">';
			if ( ! $is_last ) {
				$output .= '<a itemprop="item" href="' . esc_url( $crumb['url'] ) . '" style="color:#64748b; text-decoration:none;"><span itemprop="name">' . esc_html( $crumb['title'] ) . '</span></a>';
				$output .= '<span class="sep" style="color:#94a3b8;">' . $sep . '</span>';
			} else {
				$output .= '<span itemprop="name" style="color:#0f172a; font-weight:600;">' . esc_html( $crumb['title'] ) . '</span>';
			}
			$output .= '<meta itemprop="position" content="' . esc_attr( $pos ) . '" />';
			$output .= '</li>';
		}

		$output .= '</ol>';
		$output .= '</nav>';

		return $output;
	}
}

function vm_seo_breadcrumbs( $sep = null ) {
	echo VM_SEO_Breadcrumbs::render( $sep );
}
