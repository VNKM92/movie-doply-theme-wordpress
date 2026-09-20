<?php
/**
 * SEO: Dynamic XML Sitemap, robots.txt, and Rich Breadcrumbs
 *
 * Fully dynamic XML Sitemaps with Google Image Extensions, strict de-duplication,
 * and high-performance search engine indexing.
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Sitemap Rewrite Rules
 */
function doodhtheme_sitemap_rewrites() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?doodh_sitemap=index', 'top' );
	add_rewrite_rule( '^sitemap-([a-z0-9_-]+)\.xml$', 'index.php?doodh_sitemap=$matches[1]', 'top' );
}
add_action( 'init', 'doodhtheme_sitemap_rewrites' );

/**
 * Register query vars
 */
function doodhtheme_sitemap_query_vars( $vars ) {
	$vars[] = 'doodh_sitemap';
	return $vars;
}
add_filter( 'query_vars', 'doodhtheme_sitemap_query_vars' );

/**
 * Render Dynamic XML Sitemap with strict uniqueness and Image Extensions
 */
function doodhtheme_render_sitemap() {
	$sitemap = get_query_var( 'doodh_sitemap' );
	if ( empty( $sitemap ) ) {
		return;
	}

	global $wpdb;

	header( 'Content-Type: application/xml; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, follow', true );

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

	if ( $sitemap === 'index' ) {
		// Master Sitemap Index
		$latest_movie = $wpdb->get_var( "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_type = 'movies' AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1" );
		$latest_tv    = $wpdb->get_var( "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_type = 'tvshows' AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1" );
		$latest_ep    = $wpdb->get_var( "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_type = 'episodes' AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1" );
		$latest_post  = $wpdb->get_var( "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1" );

		$movie_date = $latest_movie ? date( 'c', strtotime( $latest_movie ) ) : date( 'c' );
		$tv_date    = $latest_tv ? date( 'c', strtotime( $latest_tv ) ) : date( 'c' );
		$ep_date    = $latest_ep ? date( 'c', strtotime( $latest_ep ) ) : date( 'c' );
		$post_date  = $latest_post ? date( 'c', strtotime( $latest_post ) ) : date( 'c' );
		$now_date   = date( 'c' );
		?>
		<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
			<sitemap>
				<loc><?php echo esc_url( home_url( '/sitemap-movies.xml' ) ); ?></loc>
				<lastmod><?php echo esc_html( $movie_date ); ?></lastmod>
			</sitemap>
			<sitemap>
				<loc><?php echo esc_url( home_url( '/sitemap-tvshows.xml' ) ); ?></loc>
				<lastmod><?php echo esc_html( $tv_date ); ?></lastmod>
			</sitemap>
			<sitemap>
				<loc><?php echo esc_url( home_url( '/sitemap-episodes.xml' ) ); ?></loc>
				<lastmod><?php echo esc_html( $ep_date ); ?></lastmod>
			</sitemap>
			<sitemap>
				<loc><?php echo esc_url( home_url( '/sitemap-posts.xml' ) ); ?></loc>
				<lastmod><?php echo esc_html( $post_date ); ?></lastmod>
			</sitemap>
			<sitemap>
				<loc><?php echo esc_url( home_url( '/sitemap-taxonomies.xml' ) ); ?></loc>
				<lastmod><?php echo esc_html( $now_date ); ?></lastmod>
			</sitemap>
			<sitemap>
				<loc><?php echo esc_url( home_url( '/sitemap-pages.xml' ) ); ?></loc>
				<lastmod><?php echo esc_html( $now_date ); ?></lastmod>
			</sitemap>
		</sitemapindex>
		<?php
		exit;
	}

	// Specific Sub-Sitemaps with image extensions and uniqueness hash map
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

	$emitted_urls = array();

	if ( in_array( $sitemap, array( 'movies', 'tvshows', 'episodes', 'pages', 'posts' ) ) ) {
		$post_type = ( $sitemap === 'pages' ) ? 'page' : ( ( $sitemap === 'posts' ) ? 'post' : $sitemap );
		
		// Query strictly unique published posts
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT ID, post_title, post_name, post_type, post_modified_gmt 
			 FROM {$wpdb->posts} 
			 WHERE post_type = %s 
			   AND post_status = 'publish' 
			 ORDER BY post_modified_gmt DESC",
			$post_type
		) );

		foreach ( $results as $p ) {
			$permalink = get_permalink( $p->ID );
			if ( empty( $permalink ) || isset( $emitted_urls[ $permalink ] ) ) {
				continue;
			}
			$emitted_urls[ $permalink ] = true;

			$lastmod = ! empty( $p->post_modified_gmt ) ? date( 'c', strtotime( $p->post_modified_gmt ) ) : date( 'c' );
			$priority = ( $post_type === 'movies' || $post_type === 'tvshows' ) ? '0.9' : ( $post_type === 'page' ? '0.8' : '0.7' );

			// Get poster/image for rich image sitemap
			$image_url = '';
			if ( $post_type === 'movies' || $post_type === 'tvshows' ) {
				$image_url = get_post_meta( $p->ID, '_doodh_poster_url', true ) ?: get_post_meta( $p->ID, '_doodh_backdrop_url', true );
			} elseif ( $post_type === 'episodes' ) {
				$image_url = get_post_meta( $p->ID, '_doodh_still_url', true );
			}
			?>
			<url>
				<loc><?php echo esc_url( $permalink ); ?></loc>
				<lastmod><?php echo esc_html( $lastmod ); ?></lastmod>
				<changefreq>daily</changefreq>
				<priority><?php echo esc_html( $priority ); ?></priority>
				<?php if ( ! empty( $image_url ) ) : ?>
					<image:image>
						<image:loc><?php echo esc_url( $image_url ); ?></image:loc>
						<image:title><?php echo esc_html( $p->post_title ); ?></image:title>
					</image:image>
				<?php endif; ?>
			</url>
			<?php
		}
	} elseif ( $sitemap === 'taxonomies' ) {
		// All taxonomy terms (Cast, Director, Genres, Release Year)
		$taxonomies = array( 'genres', 'release-year', 'dtcast', 'dtdirector' );
		foreach ( $taxonomies as $tax ) {
			$terms = get_terms( array(
				'taxonomy'   => $tax,
				'hide_empty' => true,
			) );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$link = get_term_link( $term );
					if ( is_wp_error( $link ) || isset( $emitted_urls[ $link ] ) ) {
						continue;
					}
					$emitted_urls[ $link ] = true;

					// Check if actor/director photo exists
					$photo = get_term_meta( $term->term_id, '_dt_actor_photo', true ) ?: get_term_meta( $term->term_id, '_dt_director_photo', true );
					?>
					<url>
						<loc><?php echo esc_url( $link ); ?></loc>
						<changefreq>weekly</changefreq>
						<priority>0.6</priority>
						<?php if ( ! empty( $photo ) ) : ?>
							<image:image>
								<image:loc><?php echo esc_url( $photo ); ?></image:loc>
								<image:title><?php echo esc_html( $term->name ); ?></image:title>
							</image:image>
						<?php endif; ?>
					</url>
					<?php
				}
			}
		}
	}

	echo '</urlset>';
	exit;
}
add_action( 'template_redirect', 'doodhtheme_render_sitemap' );

/**
 * Dynamic robots.txt output preventing indexation of duplicates and query parameters
 */
function doodhtheme_custom_robots_txt( $output, $public ) {
	$sitemap_url = home_url( '/sitemap.xml' );
	$custom  = "User-agent: *\n";
	$custom .= "Allow: /\n";
	$custom .= "Disallow: /wp-admin/\n";
	$custom .= "Allow: /wp-admin/admin-ajax.php\n";
	$custom .= "Disallow: /wp-includes/\n";
	$custom .= "Disallow: /trackback/\n";
	$custom .= "Disallow: /xmlrpc.php\n";
	$custom .= "Disallow: /feed/\n";
	$custom .= "Disallow: /?s=\n";
	$custom .= "Disallow: /search/\n";
	$custom .= "Disallow: /*?filter=*\n";
	$custom .= "Disallow: /*&filter=*\n";
	$custom .= "\nSitemap: {$sitemap_url}\n";

	return $custom;
}
add_filter( 'robots_txt', 'doodhtheme_custom_robots_txt', 99, 2 );

/**
 * Output Rich Breadcrumbs
 */
function doodhtheme_render_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	echo '<nav class="doodh-breadcrumbs" aria-label="Breadcrumb">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '"><i class="fas fa-home"></i> ' . esc_html__( 'Home', 'vmtheme' ) . '</a>';
	echo '<span class="doodh-sep">/</span>';

	if ( is_singular( 'movies' ) ) {
		echo '<a href="' . esc_url( get_post_type_archive_link( 'movies' ) ) . '">' . esc_html__( 'Movies', 'vmtheme' ) . '</a>';
		echo '<span class="doodh-sep">/</span>';
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular( 'tvshows' ) ) {
		echo '<a href="' . esc_url( get_post_type_archive_link( 'tvshows' ) ) . '">' . esc_html__( 'TV Shows', 'vmtheme' ) . '</a>';
		echo '<span class="doodh-sep">/</span>';
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular( 'episodes' ) ) {
		$tv_id = get_post_meta( get_the_ID(), '_doodh_tv_id', true );
		if ( $tv_id ) {
			echo '<a href="' . esc_url( get_permalink( $tv_id ) ) . '">' . esc_html( get_the_title( $tv_id ) ) . '</a>';
			echo '<span class="doodh-sep">/</span>';
		}
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_tax() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->name ) ) {
			echo '<span>' . esc_html( $term->name ) . '</span>';
		}
	} elseif ( is_page() ) {
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
	}

	echo '</nav>';
}
