<?php
/**
 * Auto-Seed Production Legal & Information Pages
 *
 * Automatically creates and publishes:
 * 1. About Us (/about-us/)
 * 2. Contact Us (/contact-us/)
 * 3. Disclaimer (/disclaimer/)
 * 4. DMCA Notice & Policy (/dmca/)
 * 5. Privacy Policy (/privacy-policy/)
 * 6. Terms of Service (/terms/)
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function doodhtheme_seed_production_pages() {
	if ( get_option( 'doodh_legal_pages_seeded_v2' ) ) {
		return;
	}

	$brand_name    = doodhtheme_get_brand_name();
	$dmca_email    = doodhtheme_get_dmca_email();
	$support_email = doodhtheme_get_support_email();
	$year          = date( 'Y' );

	$pages = array(
		array(
			'title'     => 'About Us',
			'slug'      => 'about-us',
			'template'  => 'page-about.php',
			'content'   => sprintf(
				'<h3>Welcome to %s</h3>
				<p>%s is the premier cinema catalog and streaming index designed for film enthusiasts, television fans, and binge-watchers worldwide. Our platform delivers ultra-fast indexing of high-definition 4K and 1080p titles, comprehensive cast filmographies, verified director profiles, and interactive community reviews.</p>
				<h3>Our Core Features</h3>
				<ul>
					<li><strong>Multi-Server Fast Streaming:</strong> Embedded video player with multi-source failover and zero latency.</li>
					<li><strong>Rich TMDb & IMDb Data:</strong> Up-to-date release calendars, official trailers, and verified ratings.</li>
					<li><strong>Community Reviews:</strong> 10-Star ratings and honest member feedback for every movie and episode.</li>
					<li><strong>Cross-Platform Compatibility:</strong> Optimized for smartphones, tablets, laptops, and Smart TVs.</li>
				</ul>',
				esc_html( $brand_name ),
				esc_html( $brand_name )
			),
		),
		array(
			'title'     => 'Contact Us',
			'slug'      => 'contact-us',
			'template'  => 'page-contact.php',
			'content'   => sprintf(
				'<p>We are always eager to hear from our users, partners, and content creators. If you have questions about our service, need technical assistance, or wish to report a broken link, please use our contact form or write to us directly at <a href="mailto:%s">%s</a>.</p>',
				esc_attr( $support_email ),
				esc_html( $support_email )
			),
		),
		array(
			'title'     => 'Disclaimer',
			'slug'      => 'disclaimer',
			'template'  => 'page.php',
			'content'   => sprintf(
				'<h3>Non-Hosting & Content Aggregation Disclaimer</h3>
				<p><strong>1. General Information:</strong> The content provided on <strong>%s</strong> is for entertainment, cataloging, and informational purposes only. All images, trademarks, movie titles, and media properties belong to their respective copyright owners.</p>
				<p><strong>2. No Media Files Stored:</strong> <strong>%s</strong> does not host, upload, store, or transmit any video files, media content, or copyrighted streams on its own servers. All streaming media links and embeds found on this website are crawled and indexed from publicly available third-party video sharing platforms (such as YouTube, StreamTape, DoodStream, etc.).</p>
				<p><strong>3. Third-Party Links:</strong> We have no control over the content, servers, privacy practices, or availability of third-party websites. Users access external links at their own risk.</p>
				<p><strong>4. Copyright Compliance:</strong> If you are a copyright owner and believe your copyrighted work is indexed on our site without authorization, please consult our <a href="/dmca/">DMCA Notice Page</a> for immediate takedown procedures.</p>',
				esc_html( $brand_name ),
				esc_html( $brand_name )
			),
		),
		array(
			'title'     => 'DMCA Copyright Notice',
			'slug'      => 'dmca',
			'template'  => 'page.php',
			'content'   => sprintf(
				'<h3>Digital Millennium Copyright Act (DMCA) Compliance Policy</h3>
				<p><strong>%s</strong> respects the intellectual property rights of creators and copyright holders and complies with the provisions of Title 17, United States Code, Section 512 (DMCA).</p>
				<p>Because %s operates purely as a metadata search engine and index of media available across the World Wide Web, no copyrighted videos are physically hosted on our servers.</p>
				<h3>Filing a DMCA Takedown Notice</h3>
				<p>To request the removal of indexed links, the copyright owner or authorized representative must provide a written notice containing:</p>
				<ol>
					<li>A physical or electronic signature of a person authorized to act on behalf of the owner.</li>
					<li>Identification of the copyrighted work claimed to have been infringed.</li>
					<li>The exact URLs on <strong>%s</strong> pointing to the material you wish removed.</li>
					<li>Your contact information including full name, mailing address, telephone number, and email address.</li>
					<li>A statement that you have a good faith belief that the use of the material is not authorized by the copyright owner.</li>
					<li>A statement under penalty of perjury that the information in the notification is accurate.</li>
				</ol>
				<p>Please send all takedown notices directly to our designated copyright agent at: <strong><a href="mailto:%s">%s</a></strong>.</p>
				<p>Valid notices are processed and removed within 24 to 48 business hours.</p>',
				esc_html( $brand_name ),
				esc_html( $brand_name ),
				esc_html( $brand_name ),
				esc_attr( $dmca_email ),
				esc_html( $dmca_email )
			),
		),
		array(
			'title'     => 'Privacy Policy',
			'slug'      => 'privacy-policy',
			'template'  => 'page.php',
			'content'   => sprintf(
				'<h3>Privacy Policy & Data Protection</h3>
				<p>This Privacy Policy outlines how <strong>%s</strong> collects, uses, and safeguards information when you visit our website.</p>
				<h3>1. Log Files & Analytics</h3>
				<p>Like most standard websites, %s utilizes server log files and non-personally identifiable web analytics to monitor site traffic, browser types, and popular pages. This data is used solely to optimize website performance and responsiveness.</p>
				<h3>2. Cookies & Web Beacons</h3>
				<p>We use cookies to remember user preferences (such as watchlist items, dark mode settings, and player volume). Third-party advertising partners (such as Google AdSense) may also use cookies and web beacons to serve personalized advertisements based on visitor browsing history.</p>
				<h3>3. Third-Party Links & External Players</h3>
				<p>Our website contains links to external video servers. We do not control the privacy practices or data collection of third-party domains.</p>
				<h3>4. GDPR & User Rights</h3>
				<p>Users have the right to request information about any stored data, disable cookies in their web browsers, or contact our team at <a href="mailto:%s">%s</a>.</p>',
				esc_html( $brand_name ),
				esc_html( $brand_name ),
				esc_attr( $support_email ),
				esc_html( $support_email )
			),
		),
		array(
			'title'     => 'Terms of Service',
			'slug'      => 'terms',
			'template'  => 'page.php',
			'content'   => sprintf(
				'<h3>Terms of Service & User Agreement</h3>
				<p>By accessing or using <strong>%s</strong>, you agree to be bound by these Terms of Service.</p>
				<h3>1. Acceptance of Terms</h3>
				<p>If you do not agree with any part of these terms, you must discontinue use of the website immediately.</p>
				<h3>2. Personal & Non-Commercial Use</h3>
				<p>%s is provided solely for personal entertainment, research, and informational purposes. You agree not to scrape, exploit, or disrupt platform infrastructure.</p>
				<h3>3. Limitation of Liability</h3>
				<p>Under no circumstances shall %s, its creators, or affiliates be liable for any direct, indirect, incidental, or consequential damages resulting from the use or inability to use this platform.</p>
				<h3>4. Changes to Terms</h3>
				<p>We reserve the right to revise and update these terms at any time without prior notice. Continued use of the platform constitutes acceptance of all updated terms.</p>',
				esc_html( $brand_name ),
				esc_html( $brand_name ),
				esc_html( $brand_name )
			),
		),
	);

	foreach ( $pages as $p ) {
		$existing = get_page_by_path( $p['slug'] );
		if ( ! $existing ) {
			$post_id = wp_insert_post( array(
				'post_title'   => $p['title'],
				'post_name'    => $p['slug'],
				'post_content' => $p['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			) );

			if ( $post_id && ! is_wp_error( $post_id ) && ! empty( $p['template'] ) ) {
				update_post_meta( $post_id, '_wp_page_template', $p['template'] );
			}
		}
	}

	update_option( 'doodh_legal_pages_seeded_v2', 1 );
}
add_action( 'init', 'doodhtheme_seed_production_pages' );
