<?php
/**
 * DoodhTheme - Modern Streaming Landing Page & Homepage
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// 1. Fetch Top 3 Featured Items for Hero Showcase
$hero_query = new WP_Query( array(
	'post_type'      => array( 'movies', 'tvshows' ),
	'posts_per_page' => 1,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

if ( $hero_query->have_posts() ) :
	while ( $hero_query->have_posts() ) :
		$hero_query->the_post();
		$hero_id       = get_the_ID();
		$hero_backdrop = doodhtheme_get_backdrop_url( $hero_id );
		$hero_rating   = doodhtheme_get_rating( $hero_id );
		$hero_year     = doodhtheme_get_release_year( $hero_id );
		$hero_quality  = doodhtheme_get_quality_badge( $hero_id );
		$hero_tagline  = get_post_meta( $hero_id, '_doodh_tagline', true ) ?: get_the_title();
		$hero_trailer  = get_post_meta( $hero_id, '_doodh_trailer_url', true );
		$hero_cert     = get_post_meta( $hero_id, '_doodh_certification', true ) ?: 'PG-13';
		?>
		<!-- Hero Featured Banner -->
		<section class="doodh-hero-section" style="background-image: url('<?php echo esc_url( $hero_backdrop ); ?>');">
			<div class="doodh-hero-overlay"></div>
			<div class="container">
				<div class="doodh-hero-content">
					<span class="doodh-hero-tagline"><i class="fas fa-fire"></i> <?php esc_html_e( '#1 Trending Premiere', 'doodhtheme' ); ?></span>
					<h1 class="doodh-hero-title"><?php the_title(); ?></h1>
					<div class="doodh-hero-meta">
						<span class="doodh-badge-imdb"><i class="fas fa-star"></i> <?php echo esc_html( $hero_rating ); ?></span>
						<span class="doodh-badge-quality"><?php echo esc_html( $hero_quality ); ?></span>
						<span class="doodh-tag-pill"><?php echo esc_html( $hero_cert ); ?></span>
						<span><i class="far fa-calendar-alt"></i> <?php echo esc_html( $hero_year ); ?></span>
						<span><i class="far fa-clock"></i> <?php echo esc_html( doodhtheme_get_runtime_formatted( $hero_id ) ); ?></span>
					</div>
					<div class="doodh-hero-desc">
						<?php echo wp_trim_words( get_the_excerpt() ?: get_the_content(), 35, '...' ); ?>
					</div>
					<div class="doodh-hero-btns">
						<a href="<?php the_permalink(); ?>" class="doodh-btn-primary">
							<i class="fas fa-play"></i> <?php esc_html_e( 'Watch Now', 'doodhtheme' ); ?>
						</a>
						<?php if ( ! empty( $hero_trailer ) ) : ?>
							<a href="<?php echo esc_url( $hero_trailer ); ?>" target="_blank" class="doodh-btn-secondary">
								<i class="fab fa-youtube"></i> <?php esc_html_e( 'Watch Trailer', 'doodhtheme' ); ?>
							</a>
						<?php endif; ?>
						<?php doodhtheme_render_watchlist_btn( $hero_id ); ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	endwhile;
	wp_reset_postdata();
endif;
?>

<main class="container">

	<!-- ══════════════════════════════════════════════════════════════
	     Top 10 Today Leaderboard (Netflix / DooPlay Big Number Badges)
	     ══════════════════════════════════════════════════════════════ -->
	<section class="doodh-section">
		<div class="doodh-section-header">
			<h2 class="doodh-section-title">
				<i class="fas fa-chart-line" style="color:var(--dt-primary);"></i> 
				<?php esc_html_e( 'Top 10 Today in Movies & TV', 'doodhtheme' ); ?>
			</h2>
			<a href="<?php echo esc_url( home_url( '/top-imdb/' ) ); ?>" class="doodh-view-all">
				<?php esc_html_e( 'Top 100 Leaderboard', 'doodhtheme' ); ?> <i class="fas fa-arrow-right"></i>
			</a>
		</div>

		<div class="doodh-top10-slider">
			<?php
			$top10_query = new WP_Query( array(
				'post_type'      => array( 'movies', 'tvshows' ),
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'meta_key'       => '_doodh_rating',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
			) );

			if ( $top10_query->have_posts() ) :
				$rank = 1;
				while ( $top10_query->have_posts() ) :
					$top10_query->the_post();
					$t10_id     = get_the_ID();
					$t10_poster = doodhtheme_get_poster_url( $t10_id );
					$t10_rating = doodhtheme_get_rating( $t10_id );
					?>
					<div class="doodh-top10-item">
						<span class="doodh-top10-rank"><?php echo esc_html( $rank ); ?></span>
						<div class="doodh-card doodh-top10-card">
							<div class="doodh-card-poster-wrap">
								<img src="<?php echo esc_url( $t10_poster ); ?>" class="doodh-card-poster" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $t10_id ) ); ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
								<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $t10_rating ); ?></span>
								<a href="<?php the_permalink(); ?>" class="doodh-card-overlay">
									<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
								</a>
							</div>
							<div class="doodh-card-body">
								<h3 class="doodh-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							</div>
						</div>
					</div>
					<?php
					$rank++;
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</section>

	<!-- ══════════════════════════════════════════════════════════════
	     Interactive Genre Tabs Quick Filter
	     ══════════════════════════════════════════════════════════════ -->
	<div class="doodh-genre-tabs-bar">
		<button type="button" class="doodh-genre-tab active" data-genre="all"><i class="fas fa-fire"></i> <?php esc_html_e( 'All Genres', 'doodhtheme' ); ?></button>
		<button type="button" class="doodh-genre-tab" data-genre="action"><i class="fas fa-fist-raised"></i> <?php esc_html_e( 'Action', 'doodhtheme' ); ?></button>
		<button type="button" class="doodh-genre-tab" data-genre="sci-fi"><i class="fas fa-rocket"></i> <?php esc_html_e( 'Sci-Fi', 'doodhtheme' ); ?></button>
		<button type="button" class="doodh-genre-tab" data-genre="drama"><i class="fas fa-theater-masks"></i> <?php esc_html_e( 'Drama', 'doodhtheme' ); ?></button>
		<button type="button" class="doodh-genre-tab" data-genre="animation"><i class="fas fa-magic"></i> <?php esc_html_e( 'Animation', 'doodhtheme' ); ?></button>
		<button type="button" class="doodh-genre-tab" data-genre="thriller"><i class="fas fa-skull"></i> <?php esc_html_e( 'Thriller', 'doodhtheme' ); ?></button>
		<button type="button" class="doodh-genre-tab" data-genre="comedy"><i class="fas fa-laugh-squint"></i> <?php esc_html_e( 'Comedy', 'doodhtheme' ); ?></button>
		<a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>" class="doodh-genre-tab doodh-genre-tab-more"><i class="fas fa-ellipsis-h"></i> <?php esc_html_e( 'More', 'doodhtheme' ); ?></a>
	</div>

	<!-- ══════════════════════════════════════════════════════════════
	     Section: Blockbuster Movies
	     ══════════════════════════════════════════════════════════════ -->
	<section class="doodh-section" id="doodh-movies-grid-section">
		<div class="doodh-section-header">
			<h2 class="doodh-section-title"><i class="fas fa-film" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Latest Blockbuster Movies', 'doodhtheme' ); ?></h2>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>" class="doodh-view-all">
				<?php esc_html_e( 'View All Movies', 'doodhtheme' ); ?> <i class="fas fa-arrow-right"></i>
			</a>
		</div>

		<div class="doodh-grid">
			<?php
			$movies_query = new WP_Query( array(
				'post_type'      => 'movies',
				'posts_per_page' => 12,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );

			if ( $movies_query->have_posts() ) :
				while ( $movies_query->have_posts() ) :
					$movies_query->the_post();
					$m_id      = get_the_ID();
					$m_poster  = doodhtheme_get_poster_url( $m_id );
					$m_rating  = doodhtheme_get_rating( $m_id );
					$m_year    = doodhtheme_get_release_year( $m_id );
					$m_quality = doodhtheme_get_quality_badge( $m_id );
					$genres    = wp_get_post_terms( $m_id, 'genres', array( 'fields' => 'slugs' ) );
					$genre_cls = ! is_wp_error( $genres ) ? implode( ' ', array_map( function($g){ return 'genre-' . $g; }, $genres ) ) : '';
					?>
					<article class="doodh-card doodh-filterable-card <?php echo esc_attr( $genre_cls ); ?>">
						<div class="doodh-card-poster-wrap">
							<img src="<?php echo esc_url( $m_poster ); ?>" class="doodh-card-poster" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $m_id ) ); ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
							<span class="doodh-badge-top-left doodh-badge-quality"><?php echo esc_html( $m_quality ); ?></span>
							<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $m_rating ); ?></span>
							<a href="<?php the_permalink(); ?>" class="doodh-card-overlay">
								<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
							</a>
						</div>
						<div class="doodh-card-body">
							<h3 class="doodh-card-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
							<div class="doodh-card-meta">
								<span><?php echo esc_html( $m_year ); ?></span>
								<span><?php echo esc_html( doodhtheme_get_runtime_formatted( $m_id ) ); ?></span>
							</div>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</section>

	<!-- ══════════════════════════════════════════════════════════════
	     Section: Top-Rated TV Shows & Series
	     ══════════════════════════════════════════════════════════════ -->
	<section class="doodh-section">
		<div class="doodh-section-header">
			<h2 class="doodh-section-title"><i class="fas fa-tv" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Popular TV Series & Seasons', 'doodhtheme' ); ?></h2>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>" class="doodh-view-all">
				<?php esc_html_e( 'View All TV Shows', 'doodhtheme' ); ?> <i class="fas fa-arrow-right"></i>
			</a>
		</div>

		<div class="doodh-grid">
			<?php
			$tv_query = new WP_Query( array(
				'post_type'      => 'tvshows',
				'posts_per_page' => 12,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );

			if ( $tv_query->have_posts() ) :
				while ( $tv_query->have_posts() ) :
					$tv_query->the_post();
					$tv_id       = get_the_ID();
					$tv_poster   = doodhtheme_get_poster_url( $tv_id );
					$tv_rating   = doodhtheme_get_rating( $tv_id );
					$tv_year     = doodhtheme_get_release_year( $tv_id );
					$tv_seasons  = (int) get_post_meta( $tv_id, '_doodh_total_seasons', true ) ?: 1;
					?>
					<article class="doodh-card">
						<div class="doodh-card-poster-wrap">
							<img src="<?php echo esc_url( $tv_poster ); ?>" class="doodh-card-poster" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $tv_id ) ); ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
							<span class="doodh-badge-top-left doodh-badge-quality" style="background:#2563eb;"><?php printf( esc_html__( 'SS %d', 'doodhtheme' ), $tv_seasons ); ?></span>
							<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $tv_rating ); ?></span>
							<a href="<?php the_permalink(); ?>" class="doodh-card-overlay">
								<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
							</a>
						</div>
						<div class="doodh-card-body">
							<h3 class="doodh-card-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
							<div class="doodh-card-meta">
								<span><?php echo esc_html( $tv_year ); ?></span>
								<span><?php esc_html_e( 'TV Series', 'doodhtheme' ); ?></span>
							</div>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</section>

	<!-- ══════════════════════════════════════════════════════════════
	     Streaming Feature Banner Showcase
	     ══════════════════════════════════════════════════════════════ -->
	<section class="doodh-features-banner">
		<div class="doodh-features-grid">
			<div class="doodh-feature-item">
				<div class="doodh-feature-icon"><i class="fas fa-tv"></i></div>
				<h3><?php esc_html_e( '4K UltraHD Streaming', 'doodhtheme' ); ?></h3>
				<p><?php esc_html_e( 'Enjoy crisp cinematic resolutions with immersive surround sound on any device.', 'doodhtheme' ); ?></p>
			</div>
			<div class="doodh-feature-item">
				<div class="doodh-feature-icon"><i class="fas fa-server"></i></div>
				<h3><?php esc_html_e( 'Multi-Server Redundancy', 'doodhtheme' ); ?></h3>
				<p><?php esc_html_e( 'Never face buffering or broken links with 4 high-speed failover streaming servers.', 'doodhtheme' ); ?></p>
			</div>
			<div class="doodh-feature-item">
				<div class="doodh-feature-icon"><i class="fas fa-download"></i></div>
				<h3><?php esc_html_e( 'Direct High-Speed Downloads', 'doodhtheme' ); ?></h3>
				<p><?php esc_html_e( 'Download full movies and seasons to watch offline anytime with verified safe links.', 'doodhtheme' ); ?></p>
			</div>
			<div class="doodh-feature-item">
				<div class="doodh-feature-icon"><i class="fas fa-bolt"></i></div>
				<h3><?php esc_html_e( 'Daily Premieres', 'doodhtheme' ); ?></h3>
				<p><?php esc_html_e( 'New movies, TV series, and fresh episodes updated 24/7 across all genres.', 'doodhtheme' ); ?></p>
			</div>
		</div>
	</section>

	<!-- ══════════════════════════════════════════════════════════════
	     Interactive FAQ Accordion
	     ══════════════════════════════════════════════════════════════ -->
	<section class="doodh-section" style="max-width:920px; margin:40px auto;">
		<div class="doodh-section-header" style="justify-content:center; border:none; padding:0; text-align:center;">
			<h2 class="doodh-section-title"><i class="fas fa-question-circle" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Frequently Asked Questions', 'doodhtheme' ); ?></h2>
		</div>

		<div class="doodh-faq-container">
			<div class="doodh-faq-item">
				<button type="button" class="doodh-faq-question">
					<span><?php esc_html_e( 'What is DoodhTheme and how do I watch movies?', 'doodhtheme' ); ?></span>
					<i class="fas fa-chevron-down"></i>
				</button>
				<div class="doodh-faq-answer">
					<p><?php esc_html_e( 'DoodhTheme is a high-speed streaming platform that allows you to stream and download thousands of movies and TV shows for free in HD and 4K resolution. Simply click on any title to open the video player and choose your preferred server.', 'doodhtheme' ); ?></p>
				</div>
			</div>

			<div class="doodh-faq-item">
				<button type="button" class="doodh-faq-question">
					<span><?php esc_html_e( 'How do I switch servers if a stream is buffering?', 'doodhtheme' ); ?></span>
					<i class="fas fa-chevron-down"></i>
				</button>
				<div class="doodh-faq-answer">
					<p><?php esc_html_e( 'Each title is equipped with 4 redundant streaming servers (Server 1 - VIP, Server 2 - StreamTape, Server 3 - FastCloud, and Server 4 - Direct). If one server is slow, simply click another server button at the top of the video player.', 'doodhtheme' ); ?></p>
				</div>
			</div>

			<div class="doodh-faq-item">
				<button type="button" class="doodh-faq-question">
					<span><?php esc_html_e( 'Can I request a movie or TV show that is not listed?', 'doodhtheme' ); ?></span>
					<i class="fas fa-chevron-down"></i>
				</button>
				<div class="doodh-faq-answer">
					<p><?php printf( esc_html__( 'Yes! Visit our %s page and submit the title. Our content indexing team adds requested streams within 24 to 48 hours.', 'doodhtheme' ), '<a href="' . esc_url( home_url( '/request/' ) ) . '" style="color:var(--dt-primary); font-weight:700;">' . esc_html__( 'Request Movie', 'doodhtheme' ) . '</a>' ); ?></p>
				</div>
			</div>

			<div class="doodh-faq-item">
				<button type="button" class="doodh-faq-question">
					<span><?php esc_html_e( 'How does the Watchlist work?', 'doodhtheme' ); ?></span>
					<i class="fas fa-chevron-down"></i>
				</button>
				<div class="doodh-faq-answer">
					<p><?php esc_html_e( 'Click the "+ Watchlist" button on any movie or TV show card to save it. You can access all your saved titles anytime from the header Watchlist button without needing an account.', 'doodhtheme' ); ?></p>
				</div>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
