<?php
/**
 * Taxonomy Template: Reviewer & Critic Profile Page
 * Displays Reviewer details (Avatar, Verified Badge, Rating Stats, Bio)
 * and all Movies / TV Shows reviewed by this person.
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term = get_queried_object();
$reviewer_name   = $term->name;
$reviewer_avatar = get_term_meta( $term->term_id, '_dt_reviewer_avatar', true );
if ( empty( $reviewer_avatar ) || strpos( $reviewer_avatar, 'placeholder' ) !== false ) {
	$reviewer_avatar = doodhtheme_get_fallback_avatar_url();
}
$reviewer_badge  = get_term_meta( $term->term_id, '_dt_reviewer_badge', true ) ?: 'verified_critic';
$reviewer_site   = get_term_meta( $term->term_id, '_dt_reviewer_site', true );
$post_count      = $term->count;

// Calculate reviewer metrics across their reviews
$reviews_args = array(
	'meta_query' => array(
		'relation' => 'OR',
		array(
			'key'   => '_doodh_reviewer_term_id',
			'value' => $term->term_id,
		),
		array(
			'key'   => '_doodh_reviewer_name',
			'value' => $reviewer_name,
		),
	),
	'status' => 'approve',
);
$reviewer_comments = get_comments( $reviews_args );

$total_score = 0;
$scores_count = 0;
$reviews_by_post = array();

if ( ! empty( $reviewer_comments ) ) {
	foreach ( $reviewer_comments as $rc ) {
		$score = (int) get_comment_meta( $rc->comment_ID, '_doodh_review_rating', true );
		if ( $score > 0 ) {
			$total_score += $score;
			$scores_count++;
		}
		if ( ! isset( $reviews_by_post[ $rc->comment_post_ID ] ) ) {
			$reviews_by_post[ $rc->comment_post_ID ] = array(
				'rating'  => $score ?: 9,
				'title'   => get_comment_meta( $rc->comment_ID, '_doodh_review_title', true ),
				'content' => $rc->comment_content,
				'date'    => get_comment_date( 'Y-m-d', $rc ),
			);
		}
	}
}

$avg_score = $scores_count > 0 ? number_format( (float) ( $total_score / $scores_count ), 1 ) : '8.8';

// Badge display info
$badge_labels = array(
	'tmdb_critic'     => array( 'icon' => 'fas fa-check-circle', 'label' => __( 'TMDb Verified Critic', 'vmtheme' ), 'color' => '#01b4e4' ),
	'imdb_critic'     => array( 'icon' => 'fab fa-imdb', 'label' => __( 'IMDb Top Critic', 'vmtheme' ), 'color' => '#f5c518' ),
	'editorial'       => array( 'icon' => 'fas fa-shield-alt', 'label' => __( 'Editorial Staff', 'vmtheme' ), 'color' => '#e50914' ),
	'verified_critic' => array( 'icon' => 'fas fa-certificate', 'label' => __( 'Verified Critic', 'vmtheme' ), 'color' => '#10b981' ),
	'community'       => array( 'icon' => 'fas fa-user-check', 'label' => __( 'Community Reviewer', 'vmtheme' ), 'color' => '#8b5cf6' ),
);
$badge_info = $badge_labels[ $reviewer_badge ] ?? $badge_labels['verified_critic'];
?>

<main class="doodh-main-content container">
	<?php doodhtheme_render_breadcrumbs(); ?>

	<!-- Reviewer Hero Profile Card -->
	<div class="doodh-person-hero" style="background: linear-gradient(135deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95)); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--dt-radius, 12px); padding: 30px; margin-bottom: 35px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
		<div class="doodh-person-avatar" style="position: relative;">
			<img src="<?php echo esc_url( $reviewer_avatar ); ?>" alt="<?php echo esc_attr( $reviewer_name ); ?>" loading="eager" decoding="async" width="150" height="150" style="border-radius: 50%; object-fit: cover; border: 3px solid <?php echo esc_attr( $badge_info['color'] ); ?>; box-shadow: 0 0 20px rgba(0,0,0,0.4);" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_avatar_url() ); ?>';">
			<span style="position: absolute; bottom: 8px; right: 8px; background: <?php echo esc_attr( $badge_info['color'] ); ?>; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
				<i class="<?php echo esc_attr( $badge_info['icon'] ); ?>"></i>
			</span>
		</div>

		<div class="doodh-person-details" style="flex: 1;">
			<div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px;">
				<span class="doodh-badge-quality" style="background: <?php echo esc_attr( $badge_info['color'] ); ?>; color: #fff; font-weight: 700; padding: 4px 12px; border-radius: 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
					<i class="<?php echo esc_attr( $badge_info['icon'] ); ?>"></i> <?php echo esc_html( $badge_info['label'] ); ?>
				</span>
				<span class="doodh-tag-pill" style="background: rgba(255,255,255,0.06); color: #cbd5e1; padding: 4px 10px; border-radius: 6px; font-size: 12px;">
					<i class="fas fa-star" style="color: var(--dt-rating, #f59e0b);"></i> <?php echo esc_html( $avg_score ); ?>/10 <?php esc_html_e( 'Avg Rating', 'vmtheme' ); ?>
				</span>
			</div>

			<h1 class="doodh-person-name" style="margin: 0 0 10px; font-size: 28px; font-weight: 800; color: #fff;">
				<?php echo esc_html( $reviewer_name ); ?>
			</h1>

			<p class="doodh-person-meta" style="color: #94a3b8; font-size: 14px; margin-bottom: 15px; display: flex; gap: 15px; flex-wrap: wrap;">
				<span><i class="fas fa-film"></i> <?php printf( esc_html__( '%d Titles Reviewed', 'vmtheme' ), max( (int) $post_count, count( $reviews_by_post ) ) ); ?></span>
				<?php if ( $reviewer_site ) : ?>
					<span><i class="fas fa-link"></i> <a href="<?php echo esc_url( $reviewer_site ); ?>" target="_blank" rel="nofollow noopener" style="color: var(--dt-primary);"><?php esc_html_e( 'Critic Website / Profile', 'vmtheme' ); ?> &rarr;</a></span>
				<?php endif; ?>
			</p>

			<?php if ( ! empty( $term->description ) ) : ?>
				<div class="doodh-person-bio" style="color: #cbd5e1; font-size: 14px; line-height: 1.7; max-width: 800px;">
					<?php echo wpautop( esc_html( $term->description ) ); ?>
				</div>
			<?php else : ?>
				<p style="color: #64748b; font-size: 13px; margin: 0; font-style: italic;">
					<?php printf( esc_html__( 'Verified film & television reviews authored by %s.', 'vmtheme' ), esc_html( $reviewer_name ) ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Ad Slot -->
	<?php doodhtheme_display_ad( 'header' ); ?>

	<!-- Reviews & Reviewed Filmography Grid -->
	<section class="doodh-section" style="margin-top: 30px;">
		<div class="doodh-section-header">
			<h2 class="doodh-section-title">
				<i class="fas fa-pen-nib" style="color:var(--dt-primary);"></i> 
				<?php printf( esc_html__( 'Reviews & Ratings by %s', 'vmtheme' ), esc_html( $reviewer_name ) ); ?>
			</h2>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="doodh-grid doodh-grid-movies">
				<?php while ( have_posts() ) : the_post(); 
					$film_id  = get_the_ID();
					$rating   = doodhtheme_get_rating( $film_id );
					$year     = doodhtheme_get_release_year( $film_id );
					$quality  = doodhtheme_get_quality_badge( $film_id );
					$poster   = doodhtheme_get_poster_url( $film_id );
					$is_tv    = ( get_post_type() === 'tvshows' );
					$rev_info = $reviews_by_post[ $film_id ] ?? null;
					$given_score = $rev_info ? $rev_info['rating'] : $rating;
					?>
					<article class="doodh-card doodh-card-review-item" style="display: flex; flex-direction: column; justify-content: space-between;">
						<div>
							<div class="doodh-poster">
								<img src="<?php echo esc_url( $poster ); ?>" 
									 alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $film_id ) ); ?>" 
									 loading="lazy" 
									 decoding="async"
									 width="300" 
									 height="450"
									 onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
								<span class="doodh-badge-quality"><?php echo esc_html( $quality ); ?></span>
								<span class="doodh-badge-rating" style="background: rgba(16, 185, 129, 0.9);"><i class="fas fa-star"></i> <?php echo esc_html( $given_score ); ?>/10</span>
								<div class="doodh-poster-overlay">
									<a href="<?php the_permalink(); ?>" class="doodh-play-btn" aria-label="<?php the_title_attribute(); ?>">
										<i class="fas fa-play"></i>
									</a>
								</div>
							</div>
							<div class="doodh-card-body">
								<h3 class="doodh-card-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								<div class="doodh-card-meta">
									<span><i class="far fa-calendar-alt"></i> <?php echo esc_html( $year ); ?></span>
									<span><i class="<?php echo $is_tv ? 'fas fa-tv' : 'fas fa-film'; ?>"></i> <?php echo $is_tv ? esc_html__( 'Series', 'vmtheme' ) : esc_html__( 'Movie', 'vmtheme' ); ?></span>
								</div>

								<?php if ( $rev_info && ! empty( $rev_info['content'] ) ) : ?>
									<div class="doodh-reviewer-card-quote" style="margin-top: 10px; padding: 8px 10px; background: rgba(255,255,255,0.04); border-left: 3px solid var(--dt-primary); border-radius: 0 6px 6px 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">
										"<?php echo esc_html( wp_trim_words( $rev_info['content'], 18, '...' ) ); ?>"
									</div>
								<?php endif; ?>
							</div>
						</div>

						<div style="padding: 0 12px 14px;">
							<a href="<?php the_permalink(); ?>#doodh-reviews-box" class="doodh-btn-secondary" style="display: block; text-align: center; font-size: 12px; padding: 6px 0; border-radius: 6px; text-decoration: none;">
								<i class="fas fa-comment-alt"></i> <?php esc_html_e( 'Read Full Review', 'vmtheme' ); ?> &rarr;
							</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<!-- Pagination -->
			<?php doodhtheme_render_pagination(); ?>
		<?php else : ?>
			<div class="doodh-no-results" style="text-align: center; padding: 50px 20px; background: var(--dt-bg-surface); border: 1px solid var(--dt-border); border-radius: var(--dt-radius);">
				<i class="fas fa-feather-alt" style="font-size: 36px; color: var(--dt-text-muted); margin-bottom: 12px;"></i>
				<p style="color: #cbd5e1; font-size: 15px;"><?php esc_html_e( 'No titles currently found under this reviewer profile.', 'vmtheme' ); ?></p>
			</div>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();
