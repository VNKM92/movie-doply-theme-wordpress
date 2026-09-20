<?php
/**
 * Single TV Show Template with Cast Avatars, Seasons/Episodes Grid, Ads, and User Reviews
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$tv_id          = get_the_ID();
	$backdrop       = doodhtheme_get_backdrop_url( $tv_id );
	$poster         = doodhtheme_get_poster_url( $tv_id );
	$rating         = doodhtheme_get_rating( $tv_id );
	$votes          = doodhtheme_get_votes( $tv_id );
	$year           = doodhtheme_get_release_year( $tv_id );
	$release_date   = doodhtheme_get_release_date( $tv_id );
	$total_seasons  = (int) get_post_meta( $tv_id, '_doodh_total_seasons', true ) ?: 1;
	$total_episodes = (int) get_post_meta( $tv_id, '_doodh_total_episodes', true );
	$status         = get_post_meta( $tv_id, '_doodh_status', true ) ?: 'Returning Series';
	$rich_cast      = get_post_meta( $tv_id, '_doodh_rich_cast', true );
	$rich_directors = get_post_meta( $tv_id, '_doodh_rich_directors', true );

	// Taxonomies
	$genres    = get_the_terms( $tv_id, 'genres' );
	$actors    = get_the_terms( $tv_id, 'dtcast' );
	$directors = get_the_terms( $tv_id, 'dtdirector' );

	// Fetch Episodes belonging to this TV Show
	$all_episodes = get_posts( array(
		'post_type'      => 'episodes',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_key'       => '_doodh_tv_id',
		'meta_value'     => $tv_id,
		'orderby'        => 'meta_value_num',
		'meta_key'       => '_doodh_episode_number',
		'order'          => 'ASC',
	) );

	// Group episodes by season number
	$seasons_episodes = array();
	foreach ( $all_episodes as $ep ) {
		$s_num = (int) get_post_meta( $ep->ID, '_doodh_season_number', true ) ?: 1;
		$seasons_episodes[ $s_num ][] = $ep;
	}
	ksort( $seasons_episodes );
	if ( empty( $seasons_episodes ) ) {
		$seasons_episodes[1] = array();
	}
	?>

	<!-- TV Show Header & Backdrop -->
	<div class="doodh-single-backdrop" style="background-image: url('<?php echo esc_url( $backdrop ); ?>');">
		<div class="container">
			<?php doodhtheme_render_breadcrumbs(); ?>

			<div class="doodh-single-header">
				<!-- Poster -->
				<div class="doodh-poster-box">
					<img src="<?php echo esc_url( $poster ); ?>" class="doodh-single-poster" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $tv_id ) ); ?>" loading="eager" decoding="async" width="300" height="450" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
				</div>

				<!-- Details Info -->
				<div class="doodh-single-details">
					<h1 class="doodh-single-title"><?php the_title(); ?></h1>

					<div class="doodh-meta-tags">
						<span class="doodh-badge-imdb"><i class="fas fa-star"></i> <?php echo esc_html( $rating ); ?> <em>(<?php echo number_format( (int) $votes ); ?>)</em></span>
						<span class="doodh-badge-quality" style="background:#2563eb;"><?php echo esc_html( $status ); ?></span>
						<?php if ( ! empty( $release_date ) ) : ?>
							<span class="doodh-tag-pill doodh-tag-release-date"><i class="far fa-calendar-alt"></i> <?php echo esc_html( $release_date ); ?></span>
						<?php elseif ( ! empty( $year ) ) : ?>
							<span class="doodh-tag-pill doodh-tag-release-date"><i class="far fa-calendar-alt"></i> <?php echo esc_html( $year ); ?></span>
						<?php endif; ?>
						<span class="doodh-tag-pill"><i class="fas fa-layer-group"></i> <?php printf( esc_html__( '%d Seasons', 'vmtheme' ), count( $seasons_episodes ) ); ?></span>
						<?php if ( $total_episodes ) : ?>
							<span class="doodh-tag-pill"><i class="fas fa-list"></i> <?php printf( esc_html__( '%d Episodes', 'vmtheme' ), $total_episodes ); ?></span>
						<?php endif; ?>
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

					<!-- Creators & Release Highlights -->
					<?php if ( ( ! is_wp_error( $directors ) && ! empty( $directors ) ) || ! empty( $release_date ) ) : ?>
						<div class="doodh-director-highlight" style="display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
							<?php if ( ! is_wp_error( $directors ) && ! empty( $directors ) ) : ?>
								<div>
									<strong><i class="fas fa-user-gear"></i> <?php esc_html_e( 'Created by:', 'vmtheme' ); ?></strong>
									<?php foreach ( $directors as $d ) : ?>
										<a href="<?php echo esc_url( get_term_link( $d ) ); ?>" class="doodh-director-link">
											<i class="fas fa-clapperboard"></i> <?php echo esc_html( $d->name ); ?>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $release_date ) ) : ?>
								<div class="doodh-release-info">
									<strong style="color:#fff;"><i class="far fa-calendar-check" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'First Air Date:', 'vmtheme' ); ?></strong>
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
		<!-- Ad Slot: Header Ad -->
		<?php doodhtheme_display_ad( 'header' ); ?>

		<!-- Seasons & Episodes Section -->
		<section class="doodh-section doodh-tv-seasons-box">
			<div class="doodh-section-header">
				<h2 class="doodh-section-title"><i class="fas fa-list-ol" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Seasons & Episodes', 'vmtheme' ); ?></h2>
				<div>
					<?php doodhtheme_render_watchlist_btn( $tv_id ); ?>
				</div>
			</div>

			<!-- Season Tabs -->
			<div class="doodh-season-tabs">
				<?php $first_tab = true; ?>
				<?php foreach ( array_keys( $seasons_episodes ) as $season_num ) : ?>
					<button type="button" class="doodh-season-tab-btn <?php echo $first_tab ? 'active' : ''; ?>" data-season="<?php echo esc_attr( $season_num ); ?>">
						<i class="fas fa-folder"></i> <?php printf( esc_html__( 'Season %d', 'vmtheme' ), $season_num ); ?>
					</button>
					<?php $first_tab = false; ?>
				<?php endforeach; ?>
			</div>

			<!-- Episodes Lists -->
			<?php $first_group = true; ?>
			<?php foreach ( $seasons_episodes as $season_num => $episodes ) : ?>
				<div class="doodh-season-episodes-group" id="doodh-season-episodes-<?php echo esc_attr( $season_num ); ?>" style="<?php echo $first_group ? '' : 'display:none;'; ?>">
					<?php if ( ! empty( $episodes ) ) : ?>
						<div class="doodh-episodes-grid">
							<?php foreach ( $episodes as $ep ) : 
								$ep_num     = get_post_meta( $ep->ID, '_doodh_episode_number', true ) ?: 1;
								$ep_name    = get_post_meta( $ep->ID, '_doodh_episode_name', true ) ?: $ep->post_title;
								$ep_still   = get_post_meta( $ep->ID, '_doodh_still_url', true ) ?: $backdrop;
								?>
								<a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>" class="doodh-ep-card">
									<div class="doodh-ep-thumb-box">
										<img src="<?php echo esc_url( $ep_still ); ?>" class="doodh-ep-thumb" alt="<?php echo esc_attr( $ep_name ); ?>" loading="lazy" decoding="async" width="300" height="170" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_backdrop_url() ); ?>';">
										<span class="doodh-ep-play-icon"><i class="fas fa-play"></i></span>
									</div>
									<div class="doodh-ep-info">
										<span class="doodh-ep-num"><?php printf( esc_html__( 'Episode %d', 'vmtheme' ), $ep_num ); ?></span>
										<h4 class="doodh-ep-title"><?php echo esc_html( $ep_name ); ?></h4>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p style="color:var(--dt-text-muted); padding:20px 0;"><?php esc_html_e( 'Episodes for this season are being updated soon.', 'vmtheme' ); ?></p>
					<?php endif; ?>
				</div>
				<?php $first_group = false; ?>
			<?php endforeach; ?>
		</section>

		<!-- Downloads Box (Season Pack / Series Downloads) -->
		<?php doodhtheme_render_downloads( $tv_id ); ?>

		<!-- Interactive Cast & Actors Section -->
		<section class="doodh-section">
			<div class="doodh-section-header">
				<h3 class="doodh-section-title"><i class="fas fa-users" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Starring Cast', 'vmtheme' ); ?></h3>
			</div>

			<div class="doodh-cast-grid">
				<?php
				if ( ! empty( $rich_cast ) && is_array( $rich_cast ) ) :
					foreach ( $rich_cast as $actor ) :
						$act_name  = $actor['name'] ?? '';
						$act_char  = $actor['character'] ?? '';
						$act_photo = $actor['photo'] ?? doodhtheme_get_fallback_avatar_url();
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
						$photo = get_term_meta( $act->term_id, '_dt_actor_photo', true ) ?: doodhtheme_get_fallback_avatar_url();
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
		<?php doodhtheme_render_reviews_section( $tv_id ); ?>

		<!-- ══════════════════════════════════════════════════════════════
		     Similar TV Series (Shown After Ratings & Reviews)
		     ══════════════════════════════════════════════════════════════ -->
		<?php
		$genre_ids = ! empty( $genres ) && ! is_wp_error( $genres ) ? wp_list_pluck( $genres, 'term_id' ) : array();
		$related_args = array(
			'post_type'      => 'tvshows',
			'posts_per_page' => 12,
			'post_status'    => 'publish',
			'post__not_in'   => array( $tv_id ),
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

		// Fallback to latest TV shows if genre matches are fewer than 6
		if ( $related_query->post_count < 6 ) {
			$fallback_args = array(
				'post_type'      => 'tvshows',
				'posts_per_page' => 12,
				'post_status'    => 'publish',
				'post__not_in'   => array( $tv_id ),
				'orderby'        => 'date',
				'order'          => 'DESC',
			);
			$related_query = new WP_Query( $fallback_args );
		}

		if ( $related_query->have_posts() ) :
			?>
			<section class="doodh-section doodh-similar-tvshows-section" style="margin-top:40px;">
				<div class="doodh-section-header">
					<h3 class="doodh-section-title">
						<i class="fas fa-layer-group" style="color:var(--dt-primary);"></i> 
						<?php esc_html_e( 'Similar TV Series You May Like', 'vmtheme' ); ?>
					</h3>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>" class="doodh-view-all">
						<?php esc_html_e( 'View All TV Series', 'vmtheme' ); ?> <i class="fas fa-arrow-right"></i>
					</a>
				</div>
				<div class="doodh-grid doodh-grid-movies">
					<?php
					while ( $related_query->have_posts() ) :
						$related_query->the_post();
						$rel_id      = get_the_ID();
						$rel_poster  = doodhtheme_get_poster_url( $rel_id );
						$rel_rating  = doodhtheme_get_rating( $rel_id );
						$rel_year    = doodhtheme_get_release_year( $rel_id );
						$rel_seasons = (int) get_post_meta( $rel_id, '_doodh_total_seasons', true ) ?: 1;
						?>
						<article class="doodh-card">
							<div class="doodh-poster">
								<img src="<?php echo esc_url( $rel_poster ); ?>" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $rel_id ) ); ?>" loading="lazy" decoding="async" width="300" height="450" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
								<span class="doodh-badge-quality" style="background:#2563eb;"><?php printf( esc_html__( 'SS %d', 'vmtheme' ), $rel_seasons ); ?></span>
								<span class="doodh-badge-rating"><i class="fas fa-star"></i> <?php echo esc_html( $rel_rating ); ?></span>
								<div class="doodh-poster-overlay">
									<a href="<?php the_permalink(); ?>" class="doodh-play-btn" aria-label="<?php the_title_attribute(); ?>"><i class="fas fa-play"></i></a>
								</div>
							</div>
							<div class="doodh-card-body">
								<h4 class="doodh-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
								<div class="doodh-card-meta">
									<span><i class="far fa-calendar-alt"></i> <?php echo esc_html( $rel_year ); ?></span>
									<span><i class="fas fa-tv"></i> <?php esc_html_e( 'Series', 'vmtheme' ); ?></span>
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
