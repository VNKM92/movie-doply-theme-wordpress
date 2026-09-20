<?php
/**
 * Single Movie Template with Interactive Cast, Crew, Multi-Server Player, Ads, and Reviews
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$movie_id       = get_the_ID();
	$backdrop       = doodhtheme_get_backdrop_url( $movie_id );
	$poster         = doodhtheme_get_poster_url( $movie_id );
	$tagline        = get_post_meta( $movie_id, '_doodh_tagline', true );
	$rating         = doodhtheme_get_rating( $movie_id );
	$votes          = doodhtheme_get_votes( $movie_id );
	$year           = doodhtheme_get_release_year( $movie_id );
	$release_date   = doodhtheme_get_release_date( $movie_id );
	$runtime        = doodhtheme_get_runtime_formatted( $movie_id );
	$certification  = get_post_meta( $movie_id, '_doodh_certification', true ) ?: 'PG-13';
	$quality        = doodhtheme_get_quality_badge( $movie_id );
	$rich_cast      = get_post_meta( $movie_id, '_doodh_rich_cast', true );
	$rich_directors = get_post_meta( $movie_id, '_doodh_rich_directors', true );

	// Taxonomies
	$genres    = get_the_terms( $movie_id, 'genres' );
	$directors = get_the_terms( $movie_id, 'dtdirector' );
	$actors    = get_the_terms( $movie_id, 'dtcast' );
	?>

	<!-- Movie Header & Backdrop -->
	<div class="doodh-single-backdrop" style="background-image: url('<?php echo esc_url( $backdrop ); ?>');">
		<div class="container">
			<?php doodhtheme_render_breadcrumbs(); ?>

			<div class="doodh-single-header">
				<!-- Poster -->
				<div class="doodh-poster-box">
					<img src="<?php echo esc_url( $poster ); ?>" class="doodh-single-poster" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $movie_id ) ); ?>" loading="eager" decoding="async" width="300" height="450" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
				</div>

				<!-- Details Info -->
				<div class="doodh-single-details">
					<h1 class="doodh-single-title"><?php the_title(); ?></h1>
					<?php if ( ! empty( $tagline ) ) : ?>
						<p class="doodh-single-tagline">"<?php echo esc_html( $tagline ); ?>"</p>
					<?php endif; ?>

					<div class="doodh-meta-tags">
						<span class="doodh-badge-imdb"><i class="fas fa-star"></i> <?php echo esc_html( $rating ); ?> <em>(<?php echo number_format( (int) $votes ); ?> votes)</em></span>
						<span class="doodh-badge-quality"><?php echo esc_html( $quality ); ?></span>
						<?php if ( ! empty( $release_date ) ) : ?>
							<span class="doodh-tag-pill doodh-tag-release-date"><i class="far fa-calendar-alt"></i> <?php echo esc_html( $release_date ); ?></span>
						<?php elseif ( ! empty( $year ) ) : ?>
							<span class="doodh-tag-pill doodh-tag-release-date"><i class="far fa-calendar-alt"></i> <?php echo esc_html( $year ); ?></span>
						<?php endif; ?>
						<?php if ( $runtime ) : ?>
							<span class="doodh-tag-pill"><i class="far fa-clock"></i> <?php echo esc_html( $runtime ); ?></span>
						<?php endif; ?>
						<span class="doodh-tag-pill"><?php echo esc_html( $certification ); ?></span>
					</div>

					<!-- Genres -->
					<?php if ( ! is_wp_error( $genres ) && ! empty( $genres ) ) : ?>
						<div class="doodh-genres-list" style="margin-bottom:15px;">
							<?php foreach ( $genres as $g ) : ?>
								<a href="<?php echo esc_url( get_term_link( $g ) ); ?>"><?php echo esc_html( $g->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<!-- Synopsis -->
					<div class="doodh-single-synopsis">
						<?php the_content(); ?>
					</div>

					<!-- Director & Release Highlights -->
					<?php if ( ( ! is_wp_error( $directors ) && ! empty( $directors ) ) || ! empty( $release_date ) ) : ?>
						<div class="doodh-director-highlight" style="display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
							<?php if ( ! is_wp_error( $directors ) && ! empty( $directors ) ) : ?>
								<div>
									<strong><i class="fas fa-video"></i> <?php esc_html_e( 'Director:', 'vmtheme' ); ?></strong>
									<?php foreach ( $directors as $d ) : ?>
										<a href="<?php echo esc_url( get_term_link( $d ) ); ?>" class="doodh-director-link">
											<i class="fas fa-user-tie"></i> <?php echo esc_html( $d->name ); ?>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $release_date ) ) : ?>
								<div class="doodh-release-info">
									<strong style="color:#fff;"><i class="far fa-calendar-check" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Release Date:', 'vmtheme' ); ?></strong>
									<span style="color:var(--dt-text-muted); font-size:13px; font-weight:500; margin-left:4px;"><?php echo esc_html( $release_date ); ?></span>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<main class="container">
		<!-- Ad Slot: Above Player -->
		<?php doodhtheme_display_ad( 'player_top' ); ?>

		<!-- Multi-Server Video Player (Conditional on Enable/Disable Setting) -->
		<?php if ( doodhtheme_is_player_enabled( $movie_id ) ) : ?>
			<section class="doodh-section" id="doodh-movie-player-section">
				<div class="doodh-section-header">
					<h2 class="doodh-section-title"><i class="fas fa-play" style="color:var(--dt-primary);"></i> <?php echo esc_html( get_option( 'doodh_player_title', __( 'Watch Online / Stream', 'vmtheme' ) ) ); ?></h2>
				</div>
				<?php doodhtheme_render_player( $movie_id ); ?>
			</section>

			<!-- Ad Slot: Below Player -->
			<?php doodhtheme_display_ad( 'player_bottom' ); ?>
		<?php endif; ?>

		<!-- Downloads Box (Controlled by Download Settings & Per-Post Switch) -->
		<?php doodhtheme_render_downloads( $movie_id ); ?>

		<!-- Interactive Cast & Actors Section -->
		<section class="doodh-section">
			<div class="doodh-section-header">
				<h3 class="doodh-section-title"><i class="fas fa-users" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Cast & Characters', 'vmtheme' ); ?></h3>
			</div>

			<div class="doodh-cast-grid">
				<?php
				if ( ! empty( $rich_cast ) && is_array( $rich_cast ) ) :
					foreach ( $rich_cast as $actor ) :
						$act_name  = $actor['name'] ?? '';
						$act_char  = $actor['character'] ?? '';
						$act_photo = $actor['photo'] ?? 'https://image.tmdb.org/t/p/w185/placeholder.jpg';
						$term_obj  = get_term_by( 'name', $act_name, 'dtcast' );
						$term_url  = $term_obj ? get_term_link( $term_obj ) : '#';
						?>
						<div class="doodh-cast-card">
							<a href="<?php echo esc_url( $term_url ); ?>" class="doodh-cast-avatar-wrap">
								<img src="<?php echo esc_url( $act_photo ); ?>" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( null, 'cast', $act_name ) ); ?>" loading="lazy" decoding="async" width="90" height="90" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_avatar_url() ); ?>';">
							</a>
							<div class="doodh-cast-info">
								<h5 class="doodh-cast-name"><a href="<?php echo esc_url( $term_url ); ?>"><?php echo esc_html( $act_name ); ?></a></h5>
								<span class="doodh-cast-role"><?php echo esc_html( $act_char ); ?></span>
							</div>
						</div>
					<?php endforeach;
				elseif ( ! is_wp_error( $actors ) && ! empty( $actors ) ) :
					foreach ( $actors as $act ) :
						$photo = get_term_meta( $act->term_id, '_dt_actor_photo', true ) ?: 'https://image.tmdb.org/t/p/w185/placeholder.jpg';
						?>
						<div class="doodh-cast-card">
							<a href="<?php echo esc_url( get_term_link( $act ) ); ?>" class="doodh-cast-avatar-wrap">
								<img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( null, 'cast', $act->name ) ); ?>" loading="lazy" decoding="async" width="90" height="90" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_avatar_url() ); ?>';">
							</a>
							<div class="doodh-cast-info">
								<h5 class="doodh-cast-name"><a href="<?php echo esc_url( get_term_link( $act ) ); ?>"><?php echo esc_html( $act->name ); ?></a></h5>
								<span class="doodh-cast-role"><?php esc_html_e( 'Actor', 'vmtheme' ); ?></span>
							</div>
						</div>
					<?php endforeach;
				endif;
				?>
			</div>
		</section>

		<!-- User Star Reviews & Ratings Module -->
		<?php doodhtheme_render_reviews_section( $movie_id ); ?>

		<!-- ══════════════════════════════════════════════════════════════
		     Similar Movies / You May Also Like (After Ratings & Reviews)
		     ══════════════════════════════════════════════════════════════ -->
		<?php
		$genre_ids = ! empty( $genres ) && ! is_wp_error( $genres ) ? wp_list_pluck( $genres, 'term_id' ) : array();
		$related_args = array(
			'post_type'      => 'movies',
			'posts_per_page' => 12,
			'post_status'    => 'publish',
			'post__not_in'   => array( $movie_id ),
		);

		if ( ! empty( $genre_ids ) ) {
			$related_args['tax_query'] = array(
				array(
					'taxonomy' => 'genres',
					'field'    => 'term_id',
					'terms'    => $genre_ids,
				),
			);
		} else {
			$related_args['orderby'] = 'rand';
		}

		$related_query = new WP_Query( $related_args );

		// Fallback to latest movies if genre matches are fewer than 6
		if ( $related_query->post_count < 6 ) {
			$fallback_args = array(
				'post_type'      => 'movies',
				'posts_per_page' => 6,
				'post_status'    => 'publish',
				'post__not_in'   => array( $movie_id ),
				'orderby'        => 'date',
				'order'          => 'DESC',
			);
			$related_query = new WP_Query( $fallback_args );
		}

		if ( $related_query->have_posts() ) :
			?>
			<section class="doodh-section doodh-similar-movies-section" style="margin-top:40px;">
				<div class="doodh-section-header">
					<h3 class="doodh-section-title">
						<i class="fas fa-layer-group" style="color:var(--dt-primary);"></i> 
						<?php esc_html_e( 'You May Also Like', 'vmtheme' ); ?>
					</h3>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>" class="doodh-view-all">
						<?php esc_html_e( 'View All Movies', 'vmtheme' ); ?> <i class="fas fa-arrow-right"></i>
					</a>
				</div>
				<div class="doodh-grid doodh-grid-movies">
					<?php
					while ( $related_query->have_posts() ) :
						$related_query->the_post();
						$rel_id     = get_the_ID();
						$rel_poster = doodhtheme_get_poster_url( $rel_id );
						$rel_rating = doodhtheme_get_rating( $rel_id );
						$rel_year   = doodhtheme_get_release_year( $rel_id );
						$rel_qual   = doodhtheme_get_quality_badge( $rel_id );
						?>
						<article class="doodh-card">
							<div class="doodh-poster">
								<img src="<?php echo esc_url( $rel_poster ); ?>" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $rel_id ) ); ?>" loading="lazy" decoding="async" width="300" height="450" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
								<span class="doodh-badge-quality"><?php echo esc_html( $rel_qual ); ?></span>
								<span class="doodh-badge-rating"><i class="fas fa-star"></i> <?php echo esc_html( $rel_rating ); ?></span>
								<div class="doodh-poster-overlay">
									<a href="<?php the_permalink(); ?>" class="doodh-play-btn" aria-label="<?php the_title_attribute(); ?>"><i class="fas fa-play"></i></a>
								</div>
							</div>
							<div class="doodh-card-body">
								<h4 class="doodh-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
								<div class="doodh-card-meta">
									<span><i class="far fa-calendar-alt"></i> <?php echo esc_html( $rel_year ); ?></span>
									<span><i class="fas fa-film"></i> <?php esc_html_e( 'Movie', 'vmtheme' ); ?></span>
								</div>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>
	</main>

	<?php
endwhile;

get_footer();
