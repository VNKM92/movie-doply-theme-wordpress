<?php
/**
 * Template Name: Request Movie / TV Show
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="container" style="padding-top: 40px; max-width: 800px;">
	<div class="doodh-request-box">
		<header style="text-align:center; margin-bottom:30px;">
			<div style="width:65px; height:65px; background:rgba(229,9,20,0.15); border:2px solid var(--dt-primary); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:var(--dt-primary); font-size:26px; margin-bottom:15px;">
				<i class="fas fa-paper-plane"></i>
			</div>
			<h1 style="font-size:30px; font-weight:800; color:#fff;"><?php esc_html_e( 'Request a Movie or TV Show', 'doodhtheme' ); ?></h1>
			<p style="color:var(--dt-text-muted); font-size:14px;"><?php esc_html_e( 'Can\'t find what you are looking for? Submit your request below and our team will add high-speed streams within 24 hours.', 'doodhtheme' ); ?></p>
		</header>

		<form id="doodh-request-form" class="doodh-request-form" style="background:var(--dt-bg-surface); border:1px solid var(--dt-border); border-radius:var(--dt-radius); padding:30px;">
			<div id="doodh-request-feedback" style="display:none; padding:12px; border-radius:6px; margin-bottom:20px; font-size:14px;"></div>

			<div class="doodh-form-group" style="margin-bottom:18px;">
				<label style="display:block; font-weight:600; font-size:13px; color:#fff; margin-bottom:6px;"><?php esc_html_e( 'Title Name *', 'doodhtheme' ); ?></label>
				<input type="text" name="request_title" class="doodh-search-input" style="border-radius:6px; width:100%;" placeholder="e.g. Inception, Stranger Things Season 5" required>
			</div>

			<div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:18px;">
				<div class="doodh-form-group">
					<label style="display:block; font-weight:600; font-size:13px; color:#fff; margin-bottom:6px;"><?php esc_html_e( 'Content Type *', 'doodhtheme' ); ?></label>
					<select name="request_type" class="doodh-filter-select" style="width:100%; height:42px;">
						<option value="Movie"><?php esc_html_e( 'Movie', 'doodhtheme' ); ?></option>
						<option value="TV Show / Series"><?php esc_html_e( 'TV Show / Series', 'doodhtheme' ); ?></option>
						<option value="Anime"><?php esc_html_e( 'Anime', 'doodhtheme' ); ?></option>
					</select>
				</div>

				<div class="doodh-form-group">
					<label style="display:block; font-weight:600; font-size:13px; color:#fff; margin-bottom:6px;"><?php esc_html_e( 'Release Year (Optional)', 'doodhtheme' ); ?></label>
					<input type="number" name="request_year" class="doodh-search-input" style="border-radius:6px; width:100%;" placeholder="e.g. 2024" min="1950" max="2030">
				</div>
			</div>

			<div class="doodh-form-group" style="margin-bottom:18px;">
				<label style="display:block; font-weight:600; font-size:13px; color:#fff; margin-bottom:6px;"><?php esc_html_e( 'TMDb / IMDb Link (Optional)', 'doodhtheme' ); ?></label>
				<input type="url" name="request_imdb" class="doodh-search-input" style="border-radius:6px; width:100%;" placeholder="https://www.imdb.com/title/tt...">
			</div>

			<div class="doodh-form-group" style="margin-bottom:25px;">
				<label style="display:block; font-weight:600; font-size:13px; color:#fff; margin-bottom:6px;"><?php esc_html_e( 'Additional Notes / Episode Number', 'doodhtheme' ); ?></label>
				<textarea name="request_notes" class="doodh-search-input" style="border-radius:6px; width:100%; height:100px; padding:10px 15px;" placeholder="e.g. Please add 4K HDR and Dual Audio if available..."></textarea>
			</div>

			<button type="submit" class="doodh-btn-primary" style="width:100%; justify-content:center; padding:14px; font-size:15px;">
				<i class="fas fa-paper-plane"></i> <?php esc_html_e( 'Submit Streaming Request', 'doodhtheme' ); ?>
			</button>
		</form>
	</div>
</main>

<?php
get_footer();
