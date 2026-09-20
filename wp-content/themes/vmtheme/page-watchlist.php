<?php
/**
 * Template Name: Watchlist / Saved Titles
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="container" style="padding-top: 35px; padding-bottom: 60px;">
	<div class="doodh-watchlist-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px; padding-bottom:18px; border-bottom:1px solid rgba(255,255,255,0.08);">
		<div>
			<h1 class="doodh-section-title" style="margin:0 0 4px; font-size:26px; font-weight:800; color:#fff; display:flex; align-items:center; gap:10px;">
				<i class="fas fa-bookmark" style="color:var(--dt-primary);"></i> 
				<span><?php esc_html_e( 'My Saved Watchlist', 'vmtheme' ); ?></span>
				<span class="doodh-pill-count" id="doodh-wl-page-count" style="font-size:13px; padding:2px 8px; background:rgba(255,255,255,0.1); border-radius:12px; color:#cbd5e1;">0</span>
			</h1>
			<p style="margin:0; font-size:13px; color:var(--dt-text-muted);"><?php esc_html_e( 'Your personal streaming queue, synced with your account.', 'vmtheme' ); ?></p>
		</div>

		<div class="doodh-watchlist-actions" style="display:flex; align-items:center; gap:10px;">
			<div class="doodh-filter-pills" id="doodh-watchlist-type-filters">
				<button type="button" class="doodh-filter-pill active" data-wl-filter="all"><?php esc_html_e( 'All', 'vmtheme' ); ?></button>
				<button type="button" class="doodh-filter-pill" data-wl-filter="movies"><i class="fas fa-film"></i> <?php esc_html_e( 'Movies', 'vmtheme' ); ?></button>
				<button type="button" class="doodh-filter-pill" data-wl-filter="tvshows"><i class="fas fa-tv"></i> <?php esc_html_e( 'TV Shows', 'vmtheme' ); ?></button>
			</div>

			<button type="button" class="doodh-btn-secondary" id="doodh-clear-watchlist-btn" style="padding:6px 12px; font-size:12px; color:#ef4444; border-color:rgba(239,68,68,0.3);" title="<?php esc_attr_e( 'Clear Watchlist', 'vmtheme' ); ?>">
				<i class="fas fa-trash-alt"></i> <span class="doodh-hide-mobile"><?php esc_html_e( 'Clear All', 'vmtheme' ); ?></span>
			</button>
		</div>
	</div>

	<!-- Watchlist Container (populated by doodhtheme.js via LocalStorage / User Meta) -->
	<div class="doodh-grid doodh-watchlist-grid" id="doodh-watchlist-items-grid">
		<!-- Dynamic Cards Rendered by JS -->
	</div>
</main>

<?php
get_footer();
