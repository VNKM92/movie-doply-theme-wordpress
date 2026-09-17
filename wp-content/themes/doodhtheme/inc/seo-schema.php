<?php
/**
 * Advanced SEO, Schema.org JSON-LD Structured Data, and Social Meta
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output JSON-LD Schema in WP Head
 */
function vmtheme_output_seo_schema() {
	if ( ! is_singular() ) {
		$brand_name = function_exists( 'vmtheme_get_brand_name' ) ? vmtheme_get_brand_name() : get_bloginfo( 'name' );
		// Output WebSite & Organization schema for homepage/archives
		$site_schema = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array(
					'@type' => 'Organization',
					'name'  => $brand_name,
					'url'   => home_url( '/' ),
					'logo'  => function_exists( 'vmtheme_get_fallback_backdrop_url' ) ? vmtheme_get_fallback_backdrop_url() : '',
				),
				array(
					'@type'           => 'WebSite',
					'name'            => $brand_name,
					'url'             => home_url( '/' ),
					'potentialAction' => array(
						'@type'       => 'SearchAction',
						'target'      => home_url( '/?s={search_term_string}' ),
						'query-input' => 'required name=search_term_string',
					),
				),
			),
		);
		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $site_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
		return;
	}

	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	// Output BreadcrumbList Schema for Google Search Rich Results
	$breadcrumbs = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => __( 'Home', 'vmtheme' ),
				'item'     => home_url( '/' ),
			),
		),
	);

	if ( $post_type === 'movies' ) {
		$breadcrumbs['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => __( 'Movies', 'vmtheme' ),
			'item'     => get_post_type_archive_link( 'movies' ),
		);
		$breadcrumbs['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => get_the_title( $post_id ),
			'item'     => get_permalink( $post_id ),
		);
	} elseif ( $post_type === 'tvshows' ) {
		$breadcrumbs['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => __( 'TV Shows', 'vmtheme' ),
			'item'     => get_post_type_archive_link( 'tvshows' ),
		);
		$breadcrumbs['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => get_the_title( $post_id ),
			'item'     => get_permalink( $post_id ),
		);
	} elseif ( $post_type === 'episodes' ) {
		$tv_id   = (int) get_post_meta( $post_id, '_doodh_tv_id', true );
		$tv_name = $tv_id ? get_the_title( $tv_id ) : __( 'TV Show', 'vmtheme' );
		$tv_url  = $tv_id ? get_permalink( $tv_id ) : home_url( '/tvshows/' );
		$breadcrumbs['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => $tv_name,
			'item'     => $tv_url,
		);
		$breadcrumbs['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => get_the_title( $post_id ),
			'item'     => get_permalink( $post_id ),
		);
	} elseif ( $post_type === 'post' ) {
		$blog_page_id = get_option( 'page_for_posts' );
		$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/blog/' );
		$breadcrumbs['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => __( 'Blog & Articles', 'vmtheme' ),
			'item'     => $blog_url,
		);
		$cats = get_the_category( $post_id );
		if ( ! empty( $cats ) ) {
			$breadcrumbs['itemListElement'][] = array(
				'@type'    => 'ListItem',
				'position' => 3,
				'name'     => $cats[0]->name,
				'item'     => get_category_link( $cats[0]->term_id ),
			);
			$breadcrumbs['itemListElement'][] = array(
				'@type'    => 'ListItem',
				'position' => 4,
				'name'     => get_the_title( $post_id ),
				'item'     => get_permalink( $post_id ),
			);
		} else {
			$breadcrumbs['itemListElement'][] = array(
				'@type'    => 'ListItem',
				'position' => 3,
				'name'     => get_the_title( $post_id ),
				'item'     => get_permalink( $post_id ),
			);
		}
	}

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";


	if ( $post_type === 'movies' ) {
		$title        = get_the_title( $post_id );
		$description  = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
		$poster       = doodhtheme_get_poster_url( $post_id, 'full' );
		$release_date = get_post_meta( $post_id, '_doodh_release_date', true );
		$runtime      = (int) get_post_meta( $post_id, '_doodh_runtime', true );
		$trailer      = get_post_meta( $post_id, '_doodh_trailer_url', true );

		$metrics = function_exists( 'doodhtheme_get_reviews_metrics' ) ? doodhtheme_get_reviews_metrics( $post_id ) : array();
		$rating  = ! empty( $metrics['avg_rating'] ) ? (float) $metrics['avg_rating'] : (float) doodhtheme_get_rating( $post_id );
		$votes   = ! empty( $metrics['total_count'] ) ? (int) $metrics['total_count'] : (int) doodhtheme_get_votes( $post_id );

		// Actors
		$cast_terms = get_the_terms( $post_id, 'dtcast' );
		$actors     = array();
		if ( ! is_wp_error( $cast_terms ) && ! empty( $cast_terms ) ) {
			foreach ( $cast_terms as $actor ) {
				$actors[] = array(
					'@type' => 'Person',
					'name'  => $actor->name,
				);
			}
		}

		// Directors
		$dir_terms = get_the_terms( $post_id, 'dtdirector' );
		$directors = array();
		if ( ! is_wp_error( $dir_terms ) && ! empty( $dir_terms ) ) {
			foreach ( $dir_terms as $dir ) {
				$directors[] = array(
					'@type' => 'Person',
					'name'  => $dir->name,
				);
			}
		}

		// Genres
		$genre_terms = get_the_terms( $post_id, 'genres' );
		$genres      = array();
		if ( ! is_wp_error( $genre_terms ) && ! empty( $genre_terms ) ) {
			foreach ( $genre_terms as $g ) {
				$genres[] = $g->name;
			}
		}

		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Movie',
			'name'          => $title,
			'url'           => get_permalink( $post_id ),
			'image'         => $poster,
			'description'   => $description,
			'datePublished' => $release_date ?: get_the_date( 'Y-m-d', $post_id ),
			'genre'         => $genres,
		);

		if ( $runtime > 0 ) {
			$schema['duration'] = 'PT' . $runtime . 'M';
		}

		if ( ! empty( $actors ) ) {
			$schema['actor'] = $actors;
		}

		if ( ! empty( $directors ) ) {
			$schema['director'] = $directors;
		}

		if ( $rating > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (string) number_format( $rating, 1 ),
				'bestRating'  => '10',
				'worstRating' => '1',
				'ratingCount' => (string) ( $votes > 0 ? $votes : 1 ),
			);
		}

		// Reviews Dataset
		if ( ! empty( $metrics['reviews'] ) ) {
			$schema_reviews = array();
			foreach ( $metrics['reviews'] as $rev ) {
				$schema_reviews[] = array(
					'@type'         => 'Review',
					'name'          => ! empty( $rev['title'] ) ? $rev['title'] : ( $title . ' Review' ),
					'reviewBody'    => wp_strip_all_tags( $rev['content'] ),
					'datePublished' => ! empty( $rev['date'] ) ? $rev['date'] : get_the_date( 'Y-m-d', $post_id ),
					'author'        => array(
						'@type' => 'Person',
						'name'  => $rev['author'],
					),
					'reviewRating'  => array(
						'@type'       => 'Rating',
						'ratingValue' => (string) $rev['rating'],
						'bestRating'  => '10',
						'worstRating' => '1',
					),
				);
			}
			$schema['review'] = $schema_reviews;
		}

		if ( ! empty( $trailer ) ) {
			$schema['trailer'] = array(
				'@type'        => 'VideoObject',
				'name'         => $title . ' - Official Trailer',
				'embedUrl'     => doodhtheme_format_youtube_embed( $trailer ),
				'thumbnailUrl' => $poster,
				'description'  => 'Watch the official trailer for ' . $title,
				'uploadDate'   => $release_date ?: get_the_date( 'Y-m-d', $post_id ),
			);
		}

		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}

	if ( $post_type === 'tvshows' ) {
		$title          = get_the_title( $post_id );
		$description    = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
		$poster         = doodhtheme_get_poster_url( $post_id, 'full' );
		$first_air_date = get_post_meta( $post_id, '_doodh_first_air_date', true );
		$seasons_count  = (int) get_post_meta( $post_id, '_doodh_total_seasons', true );
		$episodes_count = (int) get_post_meta( $post_id, '_doodh_total_episodes', true );

		$metrics = function_exists( 'doodhtheme_get_reviews_metrics' ) ? doodhtheme_get_reviews_metrics( $post_id ) : array();
		$rating  = ! empty( $metrics['avg_rating'] ) ? (float) $metrics['avg_rating'] : (float) doodhtheme_get_rating( $post_id );
		$votes   = ! empty( $metrics['total_count'] ) ? (int) $metrics['total_count'] : (int) doodhtheme_get_votes( $post_id );

		$genre_terms = get_the_terms( $post_id, 'genres' );
		$genres      = array();
		if ( ! is_wp_error( $genre_terms ) && ! empty( $genre_terms ) ) {
			foreach ( $genre_terms as $g ) {
				$genres[] = $g->name;
			}
		}

		$schema = array(
			'@context'          => 'https://schema.org',
			'@type'             => 'TVSeries',
			'name'              => $title,
			'url'               => get_permalink( $post_id ),
			'image'             => $poster,
			'description'       => $description,
			'startDate'         => $first_air_date ?: get_the_date( 'Y-m-d', $post_id ),
			'numberOfSeasons'   => $seasons_count > 0 ? $seasons_count : 1,
			'numberOfEpisodes'  => $episodes_count > 0 ? $episodes_count : 1,
			'genre'             => $genres,
		);

		if ( $rating > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (string) number_format( $rating, 1 ),
				'bestRating'  => '10',
				'worstRating' => '1',
				'ratingCount' => (string) ( $votes > 0 ? $votes : 1 ),
			);
		}

		// Reviews Dataset
		if ( ! empty( $metrics['reviews'] ) ) {
			$schema_reviews = array();
			foreach ( $metrics['reviews'] as $rev ) {
				$schema_reviews[] = array(
					'@type'         => 'Review',
					'name'          => ! empty( $rev['title'] ) ? $rev['title'] : ( $title . ' Review' ),
					'reviewBody'    => wp_strip_all_tags( $rev['content'] ),
					'datePublished' => ! empty( $rev['date'] ) ? $rev['date'] : get_the_date( 'Y-m-d', $post_id ),
					'author'        => array(
						'@type' => 'Person',
						'name'  => $rev['author'],
					),
					'reviewRating'  => array(
						'@type'       => 'Rating',
						'ratingValue' => (string) $rev['rating'],
						'bestRating'  => '10',
						'worstRating' => '1',
					),
				);
			}
			$schema['review'] = $schema_reviews;
		}

		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}

	if ( $post_type === 'episodes' ) {
		$title          = get_the_title( $post_id );
		$description    = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
		$tv_id          = (int) get_post_meta( $post_id, '_doodh_tv_id', true );
		$season_num     = (int) get_post_meta( $post_id, '_doodh_season_number', true ) ?: 1;
		$ep_num         = (int) get_post_meta( $post_id, '_doodh_episode_number', true ) ?: 1;
		$still_url      = get_post_meta( $post_id, '_doodh_still_url', true ) ?: doodhtheme_get_backdrop_url( $post_id );

		$metrics = function_exists( 'doodhtheme_get_reviews_metrics' ) ? doodhtheme_get_reviews_metrics( $post_id ) : array();

		$schema = array(
			'@context'       => 'https://schema.org',
			'@type'          => 'TVEpisode',
			'name'           => $title,
			'url'            => get_permalink( $post_id ),
			'image'          => $still_url,
			'description'    => $description,
			'episodeNumber'  => $ep_num,
			'partOfSeason'   => array(
				'@type'        => 'TVSeason',
				'seasonNumber' => $season_num,
			),
		);

		if ( $tv_id ) {
			$schema['partOfSeries'] = array(
				'@type' => 'TVSeries',
				'name'  => get_the_title( $tv_id ),
				'url'   => get_permalink( $tv_id ),
			);
		}

		if ( ! empty( $metrics['avg_rating'] ) ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (string) $metrics['avg_rating'],
				'bestRating'  => '10',
				'worstRating' => '1',
				'ratingCount' => (string) ( $metrics['total_count'] > 0 ? $metrics['total_count'] : 1 ),
			);
		}

		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}

	if ( $post_type === 'post' ) {
		$title       = get_the_title( $post_id );
		$description = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
		$image       = function_exists( 'vmtheme_get_blog_thumbnail_url' ) ? vmtheme_get_blog_thumbnail_url( $post_id, 'full' ) : get_the_post_thumbnail_url( $post_id, 'full' );
		$author_id   = get_post_field( 'post_author', $post_id );
		$author_name = get_the_author_meta( 'display_name', $author_id );
		$author_url  = get_author_posts_url( $author_id );
		$brand_name  = function_exists( 'vmtheme_get_brand_name' ) ? vmtheme_get_brand_name() : get_bloginfo( 'name' );
		$metrics     = function_exists( 'doodhtheme_get_reviews_metrics' ) ? doodhtheme_get_reviews_metrics( $post_id ) : array();

		$schema = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'headline'         => $title,
			'url'              => get_permalink( $post_id ),
			'image'            => $image ?: '',
			'description'      => $description,
			'datePublished'    => get_the_date( 'c', $post_id ),
			'dateModified'     => get_the_modified_date( 'c', $post_id ),
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink( $post_id ),
			),
			'author'           => array(
				'@type' => 'Person',
				'name'  => $author_name,
				'url'   => $author_url,
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => $brand_name,
				'url'   => home_url( '/' ),
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => function_exists( 'vmtheme_get_fallback_backdrop_url' ) ? vmtheme_get_fallback_backdrop_url() : '',
				),
			),
		);

		if ( ! empty( $metrics['real_count'] ) && $metrics['real_count'] > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (string) $metrics['avg_rating'],
				'bestRating'  => '10',
				'worstRating' => '1',
				'ratingCount' => (string) $metrics['total_count'],
			);
		}

		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'vmtheme_output_seo_schema', 5 );

if ( ! function_exists( 'doodhtheme_output_seo_schema' ) ) {
	function doodhtheme_output_seo_schema() {
		vmtheme_output_seo_schema();
	}
}

/**
 * Output Social Meta Tags (OpenGraph / Twitter)
 */
function vmtheme_output_social_meta() {
	$site_name = function_exists( 'vmtheme_get_brand_name' ) ? vmtheme_get_brand_name() : get_bloginfo( 'name' );
	$og_title  = wp_get_document_title();
	$og_desc   = function_exists( 'vmtheme_get_brand_tagline' ) ? vmtheme_get_brand_tagline() : get_bloginfo( 'description' );
	$og_url    = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
	$og_image  = function_exists( 'vmtheme_get_fallback_backdrop_url' ) ? vmtheme_get_fallback_backdrop_url() : '';
	$og_type   = 'website';

	if ( is_singular() ) {
		$post_id   = get_the_ID();
		$og_title  = get_the_title( $post_id ) . ' - ' . $site_name;
		$content   = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
		$og_desc   = wp_trim_words( $content, 30, '...' );
		$og_url    = get_permalink( $post_id );
		$poster    = function_exists( 'vmtheme_get_poster_url' ) ? vmtheme_get_poster_url( $post_id, 'full' ) : '';
		if ( $poster ) {
			$og_image = $poster;
		}

		$post_type = get_post_type( $post_id );
		if ( $post_type === 'movies' ) {
			$og_type = 'video.movie';
		} elseif ( $post_type === 'tvshows' || $post_type === 'episodes' ) {
			$og_type = 'video.tv_show';
		}
	}
	?>
	<!-- VMTheme OpenGraph & Social SEO -->
	<meta property="og:locale" content="<?php echo esc_attr( get_locale() ); ?>">
	<meta property="og:type" content="<?php echo esc_attr( $og_type ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $og_title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $og_url ); ?>">
	<meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>">
	<?php if ( ! empty( $og_image ) ) : ?>
		<meta property="og:image" content="<?php echo esc_url( $og_image ); ?>">
	<?php endif; ?>
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc_attr( $og_title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $og_desc ); ?>">
	<?php if ( ! empty( $og_image ) ) : ?>
		<meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>">
	<?php endif; ?>
	<meta name="theme-color" content="#0b0e14">
	<?php
}
add_action( 'wp_head', 'vmtheme_output_social_meta', 2 );

if ( ! function_exists( 'doodhtheme_output_social_meta' ) ) {
	function doodhtheme_output_social_meta() {
		vmtheme_output_social_meta();
	}
}

