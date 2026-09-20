<?php
/**
 * Template Name: Top 100 IMDb Leaderboard
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Query Top 100 Titles ordered by Rating with Responsive Pagination
$paged = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;
if ( get_query_var( 'page' ) ) {
	$paged = (int) get_query_var( 'page' );
}

$top_query = new WP_Query( array(
	'post_type'      => array( 'movies', 'tvshows' ),
	'posts_per_page' => 25,
	'paged'          => $paged,
	'post_status'    => 'publish',
	'meta_key'       => '_doodh_rating',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
) );
?>

<main class="container" style="padding-top: 35px;">
	<div class="doodh-section-header">
		<div>
			<h1 class="doodh-section-title"><i class="fas fa-trophy" style="color:var(--dt-accent-yellow);"></i> <?php esc_html_e( 'Top 100 Highest-Rated Titles', 'vmtheme' ); ?></h1>
			<p style="color:var(--dt-text-muted); font-size:14px; margin-top:4px;"><?php esc_html_e( 'The highest-rated blockbuster movies and acclaimed TV series of all time.', 'vmtheme' ); ?></p>
		</div>
	</div>

	<!-- Top 100 Table / Cards Leaderboard -->
	<div class="doodh-leaderboard-list">
		<?php
		if ( $top_query->have_posts() ) :
			$rank = ( ( $paged - 1 ) * 25 ) + 1;
			while ( $top_query->have_posts() ) :
				$top_query->the_post();
				$item_id   = get_the_ID();
				$poster    = doodhtheme_get_poster_url( $item_id, 'thumbnail' );
				$rating    = doodhtheme_get_rating( $item_id );
				$votes     = doodhtheme_get_votes( $item_id );
				$year      = doodhtheme_get_release_year( $item_id );
				$quality   = doodhtheme_get_quality_badge( $item_id );
				$type      = get_post_type();
				$type_name = ( $type === 'movies' ) ? __( 'Movie', 'vmtheme' ) : __( 'TV Show', 'vmtheme' );
				?>
				<div class="doodh-leaderboard-item">
					<!-- Rank Number / Medal -->
					<div class="doodh-rank-col">
						<span class="doodh-rank-num <?php echo ( $rank <= 3 ) ? 'doodh-rank-top3 rank-' . $rank : ''; ?>">
							#<?php echo esc_html( $rank ); ?>
						</span>
					</div>

					<!-- Poster -->
					<a href="<?php the_permalink(); ?>" class="doodh-leaderboard-thumb">
						<img src="<?php echo esc_url( $poster ); ?>" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $item_id ) ); ?>" loading="lazy" decoding="async" width="60" height="90" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
					</a>

					<!-- Title & Meta -->
					<div class="doodh-leaderboard-info">
						<h3 class="doodh-leaderboard-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>
						<div class="doodh-leaderboard-meta">
							<span class="doodh-live-tag"><?php echo esc_html( $type_name ); ?></span>
							<span><i class="far fa-calendar-alt"></i> <?php echo esc_html( $year ); ?></span>
							<span><i class="far fa-clock"></i> <?php echo esc_html( doodhtheme_get_runtime_formatted( $item_id ) ); ?></span>
							<span class="doodh-badge-quality"><?php echo esc_html( $quality ); ?></span>
						</div>
					</div>

					<!-- Score & Action -->
					<div class="doodh-leaderboard-score-col">
						<div class="doodh-score-badge">
							<i class="fas fa-star" style="color:var(--dt-accent-yellow);"></i>
							<strong><?php echo esc_html( $rating ); ?></strong>
							<span>(<?php echo esc_html( number_format( $votes ) ); ?>)</span>
						</div>
						<a href="<?php the_permalink(); ?>" class="doodh-btn-primary" style="padding:6px 14px; font-size:12px;">
							<i class="fas fa-play"></i> <?php esc_html_e( 'Watch', 'vmtheme' ); ?>
						</a>
					</div>
				</div>
				<?php
				$rank++;
			endwhile;
			?>
			</div>

			<!-- Attractive Pagination -->
			<?php doodhtheme_render_pagination( $top_query ); ?>
			<?php
			wp_reset_postdata();
		else :
			?>
			<p style="color:var(--dt-text-muted); text-align:center; padding:50px 0;">
				<?php esc_html_e( 'No titles found. Please run the 1-Click Data Seeder to populate the Top 100.', 'vmtheme' ); ?>
			</p>
			</div>
		<?php endif; ?>
</main>

<?php
get_footer();
