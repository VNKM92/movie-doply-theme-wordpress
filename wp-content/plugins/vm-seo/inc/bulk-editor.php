<?php
/**
 * VM SEO - Bulk SEO Title & Description Editor
 *
 * @package VMSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VM_SEO_Bulk_Editor {

	public static function render_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'vm-seo' ) );
		}

		// Handle Bulk Save
		if ( isset( $_POST['vm_save_bulk_seo'] ) && check_admin_referer( 'vm_seo_bulk_action', 'vm_seo_bulk_nonce' ) ) {
			if ( ! empty( $_POST['seo_title'] ) && is_array( $_POST['seo_title'] ) ) {
				foreach ( $_POST['seo_title'] as $post_id => $title ) {
					$post_id = (int) $post_id;
					if ( current_user_can( 'edit_post', $post_id ) ) {
						update_post_meta( $post_id, '_vm_seo_title', sanitize_text_field( $title ) );
					}
				}
			}
			if ( ! empty( $_POST['seo_desc'] ) && is_array( $_POST['seo_desc'] ) ) {
				foreach ( $_POST['seo_desc'] as $post_id => $desc ) {
					$post_id = (int) $post_id;
					if ( current_user_can( 'edit_post', $post_id ) ) {
						update_post_meta( $post_id, '_vm_seo_desc', sanitize_textarea_field( $desc ) );
					}
				}
			}
			if ( ! empty( $_POST['focus_kw'] ) && is_array( $_POST['focus_kw'] ) ) {
				foreach ( $_POST['focus_kw'] as $post_id => $kw ) {
					$post_id = (int) $post_id;
					if ( current_user_can( 'edit_post', $post_id ) ) {
						update_post_meta( $post_id, '_vm_focus_keyword', sanitize_text_field( $kw ) );
					}
				}
			}
			echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Bulk SEO metadata updated successfully!', 'vm-seo' ) . '</strong></p></div>';
		}

		$post_type = sanitize_text_field( $_GET['post_type_filter'] ?? 'movies' );
		$paged     = max( 1, (int) ( $_GET['paged'] ?? 1 ) );

		$posts = get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => 20,
			'paged'          => $paged,
			'post_status'    => array( 'publish', 'draft' ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$total_posts = wp_count_posts( $post_type )->publish ?? 0;
		$total_pages = ceil( $total_posts / 20 );
		?>
		<div class="wrap vm-seo-admin-page">
			<h1 style="display:flex; align-items:center; gap:8px;">
				<span class="dashicons dashicons-edit-large" style="color:#e50914; font-size:28px; width:28px; height:28px;"></span>
				<?php esc_html_e( 'VM SEO Bulk Editor', 'vm-seo' ); ?>
			</h1>
			<p><?php esc_html_e( 'Quickly view and edit SEO Titles, Meta Descriptions, and Focus Keywords in bulk without opening each post individually.', 'vm-seo' ); ?></p>

			<!-- Filter Bar -->
			<div style="margin:20px 0; background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:12px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
				<form method="get" action="" style="display:flex; align-items:center; gap:10px;">
					<input type="hidden" name="page" value="vm-seo-bulk">
					<label for="post_type_filter"><strong><?php esc_html_e( 'Content Type:', 'vm-seo' ); ?></strong></label>
					<select name="post_type_filter" id="post_type_filter" onchange="this.form.submit()">
						<option value="movies" <?php selected( $post_type, 'movies' ); ?>><?php esc_html_e( '🎬 Movies', 'vm-seo' ); ?></option>
						<option value="tvshows" <?php selected( $post_type, 'tvshows' ); ?>><?php esc_html_e( '📺 TV Shows', 'vm-seo' ); ?></option>
						<option value="post" <?php selected( $post_type, 'post' ); ?>><?php esc_html_e( '📝 Blog Posts', 'vm-seo' ); ?></option>
						<option value="page" <?php selected( $post_type, 'page' ); ?>><?php esc_html_e( '📄 Pages', 'vm-seo' ); ?></option>
					</select>
				</form>

				<div style="font-size:12px; color:#64748b;">
					<?php printf( esc_html__( 'Showing page %d of %d (Total: %d items)', 'vm-seo' ), $paged, max( 1, $total_pages ), $total_posts ); ?>
				</div>
			</div>

			<form method="post" action="">
				<?php wp_nonce_field( 'vm_seo_bulk_action', 'vm_seo_bulk_nonce' ); ?>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width:200px;"><?php esc_html_e( 'Post Title / Date', 'vm-seo' ); ?></th>
							<th style="width:160px;"><?php esc_html_e( 'Focus Keyword', 'vm-seo' ); ?></th>
							<th><?php esc_html_e( 'Custom SEO Title', 'vm-seo' ); ?></th>
							<th><?php esc_html_e( 'Custom Meta Description', 'vm-seo' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $posts ) ) : ?>
							<?php foreach ( $posts as $p ) : 
								$title    = get_post_meta( $p->ID, '_vm_seo_title', true );
								$desc     = get_post_meta( $p->ID, '_vm_seo_desc', true );
								$focus_kw = get_post_meta( $p->ID, '_vm_focus_keyword', true );
								$edit_url = get_edit_post_link( $p->ID );
							?>
								<tr>
									<td>
										<strong><a href="<?php echo esc_url( $edit_url ); ?>" target="_blank"><?php echo esc_html( $p->post_title ); ?></a></strong>
										<div style="font-size:11px; color:#64748b; margin-top:2px;"><?php echo get_the_date( 'Y-m-d', $p->ID ); ?></div>
									</td>
									<td>
										<input type="text" name="focus_kw[<?php echo esc_attr( $p->ID ); ?>]" value="<?php echo esc_attr( $focus_kw ); ?>" placeholder="e.g. Inception Movie" style="width:100%;">
									</td>
									<td>
										<input type="text" name="seo_title[<?php echo esc_attr( $p->ID ); ?>]" value="<?php echo esc_attr( $title ); ?>" placeholder="%%title%% %%sep%% %%sitename%%" style="width:100%;">
									</td>
									<td>
										<textarea name="seo_desc[<?php echo esc_attr( $p->ID ); ?>]" rows="2" style="width:100%; font-size:12px;" placeholder="<?php esc_attr_e( 'Compelling meta description...', 'vm-seo' ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="4" style="text-align:center; padding:30px; color:#64748b;">
									<?php esc_html_e( 'No posts found in this content type.', 'vm-seo' ); ?>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<div style="margin-top:20px; display:flex; justify-content:space-between; align-items:center;">
					<button type="submit" name="vm_save_bulk_seo" class="button button-primary button-large" style="background:#e50914; border-color:#dc2626; font-weight:700;">
						<span class="dashicons dashicons-saved" style="line-height:28px;"></span> <?php esc_html_e( 'Save All Bulk Changes', 'vm-seo' ); ?>
					</button>

					<?php if ( $total_pages > 1 ) : ?>
						<div class="tablenav-pages">
							<?php
							echo paginate_links( array(
								'base'    => add_query_arg( 'paged', '%#%' ),
								'format'  => '',
								'current' => $paged,
								'total'   => $total_pages,
							) );
							?>
						</div>
					<?php endif; ?>
				</div>
			</form>
		</div>
		<?php
	}
}
