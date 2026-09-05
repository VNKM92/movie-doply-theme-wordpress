<?php
/**
 * Advanced SEO, Schema.org JSON-LD Structured Data, and Social Meta
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output JSON-LD Schema in WP Head
 */
function doodhtheme_output_seo_schema() {
	if ( ! is_singular() ) {
		// Output WebSite & Organization schema for homepage/archives
		$site_schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'WebSite',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url( '/' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);
		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $site_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
		return;
	}

	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	if ( $post_type === 'movies' ) {
		$title       = get_the_title( $post_id );
		$description = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
		$poster      = doodhtheme_get_poster_url( $post_id, 'full' );
		$release_date= get_post_meta( $post_id, '_doodh_release_date', true );
		$runtime     = (int) get_post_meta( $post_id, '_doodh_runtime', true );
		$rating      = (float) doodhtheme_get_rating( $post_id );
		$votes       = (int) doodhtheme_get_votes( $post_id );
		$trailer     = get_post_meta( $post_id, '_doodh_trailer_url', true );

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
		$rating         = (float) doodhtheme_get_rating( $post_id );
		$votes          = (int) doodhtheme_get_votes( $post_id );

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

		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'doodhtheme_output_seo_schema', 5 );

/**
 * Output Social Meta Tags (OpenGraph / Twitter)
 */
function doodhtheme_output_social_meta() {
	$site_name = get_bloginfo( 'name' );
	$og_title  = wp_get_document_title();
	$og_desc   = get_bloginfo( 'description' );
	$og_url    = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
	$og_image  = get_header_image() ?: '';
	$og_type   = 'website';

	if ( is_singular() ) {
		$post_id   = get_the_ID();
		$og_title  = get_the_title( $post_id ) . ' - ' . $site_name;
		$content   = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
		$og_desc   = wp_trim_words( $content, 30, '...' );
		$og_url    = get_permalink( $post_id );
		$poster    = doodhtheme_get_poster_url( $post_id, 'full' );
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
	<!-- DoodhTheme OpenGraph & Social SEO -->
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
add_action( 'wp_head', 'doodhtheme_output_social_meta', 2 );
