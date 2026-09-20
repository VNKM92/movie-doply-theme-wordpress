<?php
/**
 * Template Name: Request Movie / TV Show
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$is_logged_in = is_user_logged_in();
$user_id      = get_current_user_id();
?>

<main class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 860px;">
	<div class="doodh-request-box">
		<header style="text-align:center; margin-bottom:28px;">
			<div style="width:64px; height:64px; background:rgba(229,9,20,0.15); border:2px solid var(--dt-primary); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:var(--dt-primary); font-size:24px; margin-bottom:14px; box-shadow:0 0 20px rgba(229,9,20,0.25);">
				<i class="fas fa-paper-plane"></i>
			</div>
			<h1 style="font-size:32px; font-weight:800; color:#fff; margin:0 0 8px; letter-spacing:-0.5px;"><?php esc_html_e( 'Request Movies & TV Shows', 'vmtheme' ); ?></h1>
			<p style="color:var(--dt-text-muted); font-size:14px; max-width:540px; margin:0 auto;"><?php esc_html_e( 'Can\'t find a title? Submit your request below and our team will add high-speed streaming links within 24 hours.', 'vmtheme' ); ?></p>
		</header>

		<!-- Tabs Bar -->
		<div class="doodh-req-tabs-bar" style="display:flex; justify-content:center; gap:10px; margin-bottom:25px;">
			<button type="button" class="doodh-filter-pill active doodh-req-tab-btn" data-req-tab="submit">
				<i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Submit Request', 'vmtheme' ); ?>
			</button>
			<?php if ( $is_logged_in ) : ?>
				<button type="button" class="doodh-filter-pill doodh-req-tab-btn" data-req-tab="tracking" id="doodh-my-requests-tab-btn">
					<i class="fas fa-clock"></i> <?php esc_html_e( 'My Submitted Requests', 'vmtheme' ); ?>
				</button>
			<?php else : ?>
				<button type="button" class="doodh-filter-pill doodh-auth-trigger">
					<i class="fas fa-lock"></i> <?php esc_html_e( 'Sign In to Track Requests', 'vmtheme' ); ?>
				</button>
			<?php endif; ?>
		</div>

		<!-- TAB 1: SUBMIT REQUEST -->
		<div class="doodh-req-pane active" id="doodh-req-pane-submit">
			<form id="doodh-request-form" class="doodh-request-form" style="background:var(--dt-bg-surface); border:1px solid var(--dt-border); border-radius:12px; padding:30px; box-shadow:0 10px 30px rgba(0,0,0,0.35);">
				<div id="doodh-request-feedback" style="display:none; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px;"></div>

				<?php if ( $is_logged_in ) : 
					$cur_user = wp_get_current_user();
					$cur_avatar = doodhtheme_get_user_avatar( $cur_user->ID );
					?>
					<div style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); padding:10px 16px; border-radius:8px; margin-bottom:20px;">
						<img src="<?php echo esc_url( $cur_avatar ); ?>" width="32" height="32" style="border-radius:50%; border:2px solid var(--dt-primary); object-fit:cover;">
						<span style="font-size:13.5px; color:#e2e8f0;"><?php printf( esc_html__( 'Requesting as %s', 'vmtheme' ), '<strong>' . esc_html( $cur_user->display_name ?: $cur_user->user_login ) . '</strong>' ); ?></span>
					</div>
				<?php endif; ?>

				<div class="doodh-form-group" style="margin-bottom:18px;">
					<label class="doodh-form-label"><i class="fas fa-heading"></i> <?php esc_html_e( 'Title Name *', 'vmtheme' ); ?></label>
					<input type="text" name="request_title" class="doodh-input" placeholder="<?php esc_attr_e( 'e.g. Inception, Stranger Things Season 5, Jujutsu Kaisen', 'vmtheme' ); ?>" required>
				</div>

				<div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:18px;">
					<div class="doodh-form-group">
						<label class="doodh-form-label"><i class="fas fa-film"></i> <?php esc_html_e( 'Content Type *', 'vmtheme' ); ?></label>
						<select name="request_type" class="doodh-input" style="height:46px;">
							<option value="Movie"><?php esc_html_e( 'Movie', 'vmtheme' ); ?></option>
							<option value="TV Show / Series"><?php esc_html_e( 'TV Show / Series', 'vmtheme' ); ?></option>
							<option value="Anime"><?php esc_html_e( 'Anime', 'vmtheme' ); ?></option>
						</select>
					</div>

					<div class="doodh-form-group">
						<label class="doodh-form-label"><i class="fas fa-calendar-alt"></i> <?php esc_html_e( 'Release Year (Optional)', 'vmtheme' ); ?></label>
						<input type="number" name="request_year" class="doodh-input" placeholder="e.g. 2024" min="1950" max="2030">
					</div>
				</div>

				<div class="doodh-form-group" style="margin-bottom:18px;">
					<label class="doodh-form-label"><i class="fas fa-link"></i> <?php esc_html_e( 'TMDb or IMDb Link (Optional)', 'vmtheme' ); ?></label>
					<input type="url" name="request_imdb" class="doodh-input" placeholder="https://www.imdb.com/title/tt... or https://www.themoviedb.org/movie/...">
				</div>

				<div class="doodh-form-group" style="margin-bottom:24px;">
					<label class="doodh-form-label"><i class="fas fa-comment-alt"></i> <?php esc_html_e( 'Additional Notes / Audio Preferences', 'vmtheme' ); ?></label>
					<textarea name="request_notes" class="doodh-textarea" placeholder="<?php esc_attr_e( 'e.g. Please add 4K HDR, Dual Audio, or English Subtitles...', 'vmtheme' ); ?>" style="min-height:90px;"></textarea>
				</div>

				<button type="submit" class="doodh-btn-primary doodh-btn-full" id="doodh-request-submit-btn" style="justify-content:center; padding:13px; font-size:15px;">
					<i class="fas fa-paper-plane"></i> <span><?php esc_html_e( 'Submit Streaming Request', 'vmtheme' ); ?></span>
				</button>
			</form>
		</div>

		<!-- TAB 2: MY SUBMITTED REQUESTS TRACKING -->
		<?php if ( $is_logged_in ) : ?>
			<div class="doodh-req-pane" id="doodh-req-pane-tracking" style="display:none;">
				<div class="doodh-my-requests-wrap" id="doodh-user-requests-list" style="display:flex; flex-direction:column; gap:12px;">
					<div style="text-align:center; padding:40px 20px; color:#94a3b8;">
						<i class="fas fa-spinner fa-spin" style="font-size:28px; color:var(--dt-primary); margin-bottom:10px;"></i>
						<p><?php esc_html_e( 'Loading your requests...', 'vmtheme' ); ?></p>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
