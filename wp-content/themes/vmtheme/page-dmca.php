<?php
/**
 * Template Name: DMCA & Copyright Policy
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="container" style="padding-top: 40px; max-width: 960px;">
	<article class="doodh-legal-box">
		<header style="border-bottom:1px solid var(--dt-border); padding-bottom:20px; margin-bottom:25px;">
			<h1 style="font-size:32px; font-weight:800; color:#fff; display:flex; align-items:center; gap:10px;">
				<i class="fas fa-shield-alt" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'DMCA & Copyright Notice', 'vmtheme' ); ?>
			</h1>
			<p style="color:var(--dt-text-muted); font-size:14px; margin-top:5px;"><?php esc_html_e( 'Digital Millennium Copyright Act Compliance Policy', 'vmtheme' ); ?></p>
		</header>

		<div class="doodh-legal-content" style="color:#cbd5e1; line-height:1.8; font-size:15px;">
			<h3 style="color:#fff; margin-top:20px;"><?php esc_html_e( '1. Non-Hosting & Third-Party Content Disclaimer', 'vmtheme' ); ?></h3>
			<p>
				<?php bloginfo( 'name' ); ?> is an online service provider as defined in the Digital Millennium Copyright Act (17 U.S.C. § 512). 
				We strictly do not host, upload, store, or manage any video files, media streams, or copyright-protected content on our servers. 
				All video media displayed or linked on this site are hosted on third-party non-affiliated streaming platforms (such as YouTube, StreamTape, VidCloud, Vimeo, and Dailymotion).
			</p>

			<h3 style="color:#fff; margin-top:25px;"><?php esc_html_e( '2. Notice and Takedown Procedure', 'vmtheme' ); ?></h3>
			<p>
				If you are a copyright owner or an agent thereof and believe that any content linked on our platform infringes upon your copyrights, you may submit a notification pursuant to the DMCA by providing our Copyright Agent with the following information in writing:
			</p>
			<ul style="list-style:disc; margin-left:25px; margin-top:10px; line-height:1.8;">
				<li>A physical or electronic signature of a person authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</li>
				<li>Identification of the copyrighted work claimed to have been infringed, or a representative list of such works.</li>
				<li>Identification of the specific material (URL) that is claimed to be infringing or to be the subject of infringing activity.</li>
				<li>Information reasonably sufficient to permit us to contact you, such as an address, telephone number, and email address.</li>
				<li>A statement that you have a good faith belief that use of the material is not authorized by the copyright owner, its agent, or the law.</li>
				<li>A statement that the information in the notification is accurate, under penalty of perjury.</li>
			</ul>

			<div style="background:var(--dt-bg-surface); border:1px solid var(--dt-border); padding:20px; border-radius:var(--dt-radius); margin-top:30px;">
				<h4 style="color:#fff; margin-bottom:8px;"><i class="fas fa-envelope" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Designated Copyright Contact Agent', 'vmtheme' ); ?></h4>
				<p style="margin:0; font-size:14px; color:var(--dt-text-muted);">
					Please direct all valid DMCA takedown inquiries to: <strong>dmca@<?php echo esc_html( $_SERVER['HTTP_HOST'] ?? 'doodhtheme.local' ); ?></strong>.<br>
					We will process and remove the indexed links within 24 to 48 business hours.
				</p>
			</div>
		</div>
	</article>
</main>

<?php
get_footer();
