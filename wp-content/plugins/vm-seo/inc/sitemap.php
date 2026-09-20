<?php
/**
 * VM SEO - High-Performance XML Sitemap Engine
 *
 * Generates Yoast-grade XML sitemaps for posts, pages, movies, tvshows, seasons, episodes, and taxonomies
 * with image tags, changefreq, priority, and XSL stylesheet.
 *
 * @package VMSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VM_SEO_Sitemap {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'render_sitemap' ) );
	}

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^sitemap\.xml$', 'index.php?vm_sitemap=index', 'top' );
		add_rewrite_rule( '^sitemap_index\.xml$', 'index.php?vm_sitemap=index', 'top' );
		add_rewrite_rule( '^sitemap-([a-zA-Z0-9_-]+)\.xml$', 'index.php?vm_sitemap=$matches[1]', 'top' );
		add_rewrite_rule( '^sitemap-xsl\.xsl$', 'index.php?vm_sitemap=xsl', 'top' );
	}

	public static function add_query_vars( $vars ) {
		$vars[] = 'vm_sitemap';
		return $vars;
	}

	public static function render_sitemap() {
		$sitemap = get_query_var( 'vm_sitemap' );
		if ( empty( $sitemap ) ) {
			return;
		}

		if ( $sitemap === 'xsl' ) {
			self::render_xsl_stylesheet();
			exit;
		}

		$options = VM_SEO_Master::get_instance()->options;
		if ( ( $options['enable_sitemaps'] ?? 'yes' ) !== 'yes' ) {
			status_header( 404 );
			nocache_headers();
			return;
		}

		// Disable caching / set XML headers
		header( 'Content-Type: text/xml; charset=utf-8' );
		header( 'X-Robots-Tag: noindex, follow', true );

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<?xml-stylesheet type="text/xsl" href="' . esc_url( home_url( '/sitemap-xsl.xsl' ) ) . '"?>' . "\n";

		if ( $sitemap === 'index' ) {
			self::render_index_sitemap();
		} else {
			self::render_single_sitemap( $sitemap );
		}
		exit;
	}

	private static function render_index_sitemap() {
		$options = VM_SEO_Master::get_instance()->options;
		$enabled_pts  = (array) ( $options['sitemap_post_types'] ?? array( 'movies', 'tvshows', 'episodes', 'post', 'page' ) );
		$enabled_taxs = (array) ( $options['sitemap_taxonomies'] ?? array( 'genres', 'release-year', 'category', 'post_tag' ) );

		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		foreach ( $enabled_pts as $pt ) {
			if ( ! post_type_exists( $pt ) ) {
				continue;
			}
			$count = wp_count_posts( $pt );
			if ( ! empty( $count->publish ) && (int) $count->publish > 0 ) {
				$last_post = get_posts( array(
					'post_type'      => $pt,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'orderby'        => 'modified',
					'order'          => 'DESC',
				) );
				$last_mod = ! empty( $last_post ) ? mysql2date( 'Y-m-d\TH:i:s+00:00', $last_post[0]->post_modified_gmt, false ) : date( 'c' );

				echo "\t<sitemap>\n";
				echo "\t\t<loc>" . esc_url( home_url( "/sitemap-{$pt}.xml" ) ) . "</loc>\n";
				echo "\t\t<lastmod>" . esc_html( $last_mod ) . "</lastmod>\n";
				echo "\t</sitemap>\n";
			}
		}

		foreach ( $enabled_taxs as $tax ) {
			if ( taxonomy_exists( $tax ) ) {
				$terms_count = wp_count_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) );
				if ( (int) $terms_count > 0 ) {
					echo "\t<sitemap>\n";
					echo "\t\t<loc>" . esc_url( home_url( "/sitemap-{$tax}.xml" ) ) . "</loc>\n";
					echo "\t\t<lastmod>" . esc_html( date( 'c' ) ) . "</lastmod>\n";
					echo "\t</sitemap>\n";
				}
			}
		}

		echo '</sitemapindex>';
	}

	private static function render_single_sitemap( $type ) {
		$options = VM_SEO_Master::get_instance()->options;
		$enabled_pts    = (array) ( $options['sitemap_post_types'] ?? array( 'movies', 'tvshows', 'episodes', 'post', 'page' ) );
		$enabled_taxs   = (array) ( $options['sitemap_taxonomies'] ?? array( 'genres', 'release-year', 'category', 'post_tag' ) );
		$include_images = ( $options['sitemap_include_images'] ?? 'yes' ) === 'yes';
		$max_entries    = max( 10, (int) ( $options['sitemap_max_entries'] ?? 1000 ) );

		// Parse excluded IDs
		$exclude_raw = $options['sitemap_exclude_ids'] ?? '';
		$exclude_ids = array_filter( array_map( 'intval', explode( ',', $exclude_raw ) ) );

		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

		if ( taxonomy_exists( $type ) && in_array( $type, $enabled_taxs, true ) ) {
			$terms = get_terms( array(
				'taxonomy'   => $type,
				'hide_empty' => true,
				'number'     => $max_entries,
			) );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					echo "\t<url>\n";
					echo "\t\t<loc>" . esc_url( get_term_link( $t ) ) . "</loc>\n";
					echo "\t\t<changefreq>weekly</changefreq>\n";
					echo "\t\t<priority>0.6</priority>\n";
					echo "\t</url>\n";
				}
			}
		} elseif ( post_type_exists( $type ) && in_array( $type, $enabled_pts, true ) ) {
			$args = array(
				'post_type'      => $type,
				'post_status'    => 'publish',
				'posts_per_page' => $max_entries,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			);
			if ( ! empty( $exclude_ids ) ) {
				$args['post__not_in'] = $exclude_ids;
			}

			$posts = get_posts( $args );

			foreach ( $posts as $p ) {
				$noindex = get_post_meta( $p->ID, '_vm_seo_noindex', true );
				if ( $noindex === 'yes' ) {
					continue;
				}

				$lastmod = mysql2date( 'Y-m-d\TH:i:s+00:00', $p->post_modified_gmt, false );
				$poster  = function_exists( 'vmtheme_get_poster_url' ) ? vmtheme_get_poster_url( $p->ID ) : get_post_meta( $p->ID, '_vm_poster_url', true );
				$priority = in_array( $type, array( 'movies', 'tvshows' ), true ) ? '0.9' : '0.8';

				echo "\t<url>\n";
				echo "\t\t<loc>" . esc_url( get_permalink( $p->ID ) ) . "</loc>\n";
				echo "\t\t<lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
				echo "\t\t<changefreq>daily</changefreq>\n";
				echo "\t\t<priority>" . esc_html( $priority ) . "</priority>\n";

				if ( $include_images && ! empty( $poster ) && strpos( $poster, 'placeholder' ) === false ) {
					echo "\t\t<image:image>\n";
					echo "\t\t\t<image:loc>" . esc_url( $poster ) . "</image:loc>\n";
					echo "\t\t\t<image:title>" . esc_html( $p->post_title ) . "</image:title>\n";
					echo "\t\t</image:image>\n";
				}

				echo "\t</url>\n";
			}
		}

		echo '</urlset>';
	}

	private static function render_xsl_stylesheet() {
		header( 'Content-Type: text/xsl; charset=utf-8' );
		?>
		<xsl:stylesheet version="2.0" 
			xmlns:html="http://www.w3.org/TR/REC-html40"
			xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
			xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
			xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
		<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
		<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<title>XML Sitemap - VM SEO Master</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<style type="text/css">
				body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; color: #334155; background: #f8fafc; margin: 0; padding: 30px; font-size: 14px; }
				.sitemap-box { max-width: 1000px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 25px 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
				h1 { margin-top: 0; color: #0f172a; font-size: 22px; display: flex; align-items: center; gap: 8px; }
				p { color: #64748b; margin-bottom: 20px; line-height: 1.5; }
				table { width: 100%; border-collapse: collapse; margin-top: 15px; }
				th { background: #f1f5f9; color: #475569; text-align: left; padding: 10px 14px; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #cbd5e1; }
				td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
				tr:hover td { background: #f8fafc; }
				a { color: #2563eb; text-decoration: none; font-weight: 500; }
				a:hover { text-decoration: underline; }
				.badge { background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
			</style>
		</head>
		<body>
			<div class="sitemap-box">
				<h1>⚡ XML Sitemap <span class="badge">VM SEO Suite</span></h1>
				<p>This XML Sitemap is generated by <strong>VM SEO Master</strong> for Google, Bing, Yandex, and other search engines to crawl and index your movies, TV shows, and content faster.</p>
				<xsl:if test="count(sitemap:sitemapindex/sitemap:sitemap) &gt; 0">
					<table>
						<thead>
							<tr>
								<th>Sitemap Index URL</th>
								<th>Last Modified</th>
							</tr>
						</thead>
						<tbody>
							<xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
								<tr>
									<td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
									<td><xsl:value-of select="sitemap:lastmod"/></td>
								</tr>
							</xsl:for-each>
						</tbody>
					</table>
				</xsl:if>
				<xsl:if test="count(sitemap:urlset/sitemap:url) &gt; 0">
					<table>
						<thead>
							<tr>
								<th>URL</th>
								<th>Images</th>
								<th>Priority</th>
								<th>Change Frequency</th>
								<th>Last Modified</th>
							</tr>
						</thead>
						<tbody>
							<xsl:for-each select="sitemap:urlset/sitemap:url">
								<tr>
									<td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
									<td><xsl:value-of select="count(image:image)"/></td>
									<td><xsl:value-of select="sitemap:priority"/></td>
									<td><xsl:value-of select="sitemap:changefreq"/></td>
									<td><xsl:value-of select="sitemap:lastmod"/></td>
								</tr>
							</xsl:for-each>
						</tbody>
					</table>
				</xsl:if>
			</div>
		</body>
		</html>
		</xsl:template>
		</xsl:stylesheet>
		<?php
	}
}

VM_SEO_Sitemap::init();
