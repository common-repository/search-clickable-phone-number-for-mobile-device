<?php
/**
 * This file contains the functions related to searching phone numbers.
 *
 * @package    MyPhoneNumberPlugin
 * @subpackage Admin
 * @category   Functions
 * @since      1.0.0
 */

if ( ! function_exists( 'spnrp_search_phone_number' ) ) {
	/**
	 * Search function for phone number.
	 *
	 * This function searches phone numbers in post content, post excerpt,
	 * and comment content and displays the results in a table.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	function spnrp_search_phone_number() {
		global $wpdb;

		$spnrp_tbl_name = '';
		$spnrp_field = '';
		$spnrp_order = '';
		$spnrp_post_table_name = $wpdb->prefix . 'posts';
		$spnrp_comment_table_name = $wpdb->prefix . 'comments';
		?>
		<div class="wrap nosubsub">
		<h1><?php echo esc_html( 'Phone Numbers' ); ?></h1>
		<div id="ajax-response"></div>
		<br class="clear">

		<div id="col-container">
			<?php
			if ( isset( $_POST['spnrp_submit'], $_POST['spnrp_form_security'], $_POST['spnrp_select_field'], $_POST['spnrp_select_order'] ) &&
			! empty( $_POST['spnrp_submit'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['spnrp_form_security'] ) ), 'spnrp-form-security' ) &&
			! empty( $_POST['spnrp_select_field'] ) &&
			! empty( $_POST['spnrp_select_order'] ) ) {

				$spnrp_field = sanitize_text_field( wp_unslash( $_POST['spnrp_select_field'] ) );
				$spnrp_order = sanitize_text_field( wp_unslash( $_POST['spnrp_select_order'] ) );

				switch ( $spnrp_field ) {
					case 'post_content':
					case 'post_excerpt':
						$spnrp_tbl_name = $spnrp_post_table_name;
						$spnrp_results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %1s WHERE post_status = 'publish' ORDER BY post_date %2s", $spnrp_post_table_name, $spnrp_order ) );
						break;
					case 'comment_content':
						$spnrp_tbl_name = $spnrp_comment_table_name;
						$spnrp_results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE comment_approved = true ORDER BY comment_date %2s', $spnrp_comment_table_name, $spnrp_order ) );
						break;
				}
				?>
				<div id="col-right">
					<div class="col-wrap">
						<h2 class="screen-reader-text"><?php esc_html_e( 'Phone number list' ); ?></h2>
						<table class="wp-list-table widefat fixed striped tags">
							<thead>
								<tr>
									<th scope="col" id="spnrp_title" class="manage-column column-spnrp-title column-primary">
										<span><?php esc_html_e( 'Title' ); ?></span>
									</th>
									<th scope="col" id="spnrp_type" class="manage-column column-spnrp-type">
										<span><?php esc_html_e( 'Type' ); ?></span>
									</th>
									<th scope="col" id="spnrp_pnumber" class="manage-column column-spnrp-pnumber">
										<span><?php esc_html_e( 'Phone numbers' ); ?></span>
									</th>
									<th scope="col" id="spnrp_status" class="manage-column column-spnrp-status">
										<span><?php esc_html_e( 'Status - Clickable' ); ?></span>
									</th>
								</tr>
							</thead>
							<tbody id="the-list" data-wp-lists="list:tag">
								<?php

								if ( count( $spnrp_results ) > 0 ) {
									$spnrp_count = 0;
									foreach ( $spnrp_results as $check ) {
										$spnrp_found_yes = '';
										$spnrp_col = '';
										$spnrp_title = '';
										$spnrp_type = '';
										$spnrp_id = '';
										$spnrp_edit_link = '';

										if ( $spnrp_tbl_name == $wpdb->prefix . 'posts' && 'post_content' == $spnrp_field ) {
											$spnrp_col = $check->post_content;
											$spnrp_title = $check->post_title;
											$spnrp_type = $check->post_type;
											$spnrp_id = $check->ID;
											$spnrp_edit_link = admin_url( 'post.php?post=' . $check->ID . '&action=edit' );
										}

										if ( $spnrp_tbl_name == $wpdb->prefix . 'posts' && 'post_excerpt' == $spnrp_field ) {
											$spnrp_col = $check->post_excerpt;
											$spnrp_title = $check->post_title;
											$spnrp_type = $check->post_type;
											$spnrp_id = $check->ID;
											$spnrp_edit_link = admin_url( 'post.php?post=' . $check->ID . '&action=edit' );
										}

										if ( $spnrp_tbl_name == $wpdb->prefix . 'comments' && 'comment_content' == $spnrp_field ) {
											$spnrp_col = $check->comment_content;
											$spnrp_title = get_the_title( $check->comment_post_ID );
											$spnrp_type = get_comment_type( $check->comment_ID );
											$spnrp_id = $check->comment_ID;
											$spnrp_edit_link = admin_url( 'comment.php?action=editcomment&c=' . $check->comment_ID );
										}

										// Include phone numbers from post meta.
										$post_phone_number = get_post_meta( $spnrp_id, 'phone_number', true );
										if ( $post_phone_number ) {
											$spnrp_col .= ' ' . $post_phone_number;
										}

										// Include phone numbers from comment meta.
										$comment_phone_number = get_comment_meta( $spnrp_id, 'phone_number', true );
										if ( $comment_phone_number ) {
											$spnrp_col .= ' ' . $comment_phone_number;
										}

										if ( preg_match_all( '/\+{0,1}[0-9]{0,2}[ .-]*\(*[0-9]{3}\)*[ .-]*[0-9]{3}[ .-]*[0-9]{4}/', $spnrp_col, $matches ) ) {
											foreach ( $matches[0] as $match ) {
												$spnrp_found_yes .= '<a href="tel:' . esc_attr( $match ) . '">' . esc_html( $match ) . '</a>,<br>';
											}
											?>
											<tr>
												<td class="column-primary">
													<strong>
														<a class="row-title" href="<?php echo esc_url( $spnrp_edit_link ); ?>"><?php echo esc_html( $spnrp_title ); ?></a>
													</strong>
													<br>
													<div class="row-actions">
														<span class="edit"><a href="<?php echo esc_url( $spnrp_edit_link ); ?>">Edit</a> | </span>
														<span class="view"><a href="<?php the_permalink( $spnrp_id ); ?>">View</a></span>
													</div>
												</td>
												<td class="column-spnrp-type">
													<p><?php echo esc_html( $spnrp_type ); ?></p>
												</td>
												<td class="column-spnrp-pnumber">
												   <?php echo wp_kses_post( $spnrp_found_yes ); ?>
												</td>
												<td class="column-spnrp-status">
												<span style="color: #01a252"><?php esc_html_e( 'Yes', 'my-phone-number-plugin' ); ?></span>
												</td>
											</tr>
											 <?php
												$spnrp_count++;
										} else {
											?>
											<tr>
												<td class="column-primary">
													<strong>
														<a class="row-title" href="<?php echo esc_url( $spnrp_edit_link ); ?>"><?php echo esc_html( $spnrp_title ); ?></a>
													</strong>
													<br>
													<div class="row-actions">
														<span class="edit"><a href="<?php echo esc_url( $spnrp_edit_link ); ?>">Edit</a> | </span>
														<span class="view"><a href="<?php the_permalink( $spnrp_id ); ?>">View</a></span>
													</div>
												</td>
												<td class="column-spnrp-type">
												<p><?php echo esc_html( $spnrp_type ); ?></p>
												</td>
												<td class="column-spnrp-pnumber">
												  
												</td>
												<td class="column-spnrp-status">
												<span style="color: #F30"><?php esc_html_e( 'No', 'my-phone-number-plugin' ); ?></span>
												</td>
											</tr>
											 <?php
												$spnrp_count++;
										}
									}
									if ( $spnrp_count < 0 ) {
										?>
										<tr class='no-items'>
											<td class='colspanchange' colspan='4'><?php esc_html_e( 'No results found.', 'my-phone-number-plugin' ); ?></td>
										</tr>
										<?php
									}
								} else {
									?>
									<tr class='no-items'>
										<td class='colspanchange' colspan='4'><?php esc_html_e( 'No results found.', 'my-phone-number-plugin' ); ?></td>
									</tr>
								 <?php } ?>
							</tbody>
							<tfoot>
								<tr>
									<th scope="col" id="spnrp_title" class="manage-column column-spnrp-title column-primary">
									<span><?php esc_html_e( 'Title', 'my-phone-number-plugin' ); ?></span>
									</th>
									<th scope="col" id="spnrp_type" class="manage-column column-spnrp-type">
									<span><?php esc_html_e( 'Type', 'my-phone-number-plugin' ); ?></span>
									</th>
									<th scope="col" id="spnrp_pnumber" class="manage-column column-spnrp-pnumber">
									<span><?php esc_html_e( 'Phone numbers', 'my-phone-number-plugin' ); ?></span>
									</th>
									<th scope="col" id="spnrp_status" class="manage-column column-spnrp-status">
									<span><?php esc_html_e( 'Status - Clickable', 'my-phone-number-plugin' ); ?></span>
									</th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div><!-- /col-right -->
				 <?php
			}
			?>
			<div id="col-left">
				<div class="col-wrap">
					<div class="form-wrap">
						<h2><?php esc_html_e( 'Search Clickable Phone Number', 'my-phone-number-plugin' ); ?></h2>
						<form name="spnrp_form" id="spnrp_from" method="POST" action="">
							<div class="form-field term-parent-wrap">
								<label for="parent"><?php esc_html_e( 'Field', 'my-phone-number-plugin' ); ?></label>
								<select name="spnrp_select_field" id="spnrp_select_field" class="postform">
									<option value="post_content" <?php echo esc_html( 'post_content' == $spnrp_field ) ? 'selected' : ''; ?>><?php esc_html_e( 'Post Content', 'my-phone-number-plugin' ); ?></option>
									<option value="post_excerpt" <?php echo esc_html( 'post_excerpt' == $spnrp_field ) ? 'selected' : ''; ?>><?php esc_html_e( 'Post Excerpt', 'my-phone-number-plugin' ); ?></option>
									<option value="comment_content" <?php echo esc_html( 'comment_content' == $spnrp_field ) ? 'selected' : ''; ?>><?php esc_html_e( 'Comment Content', 'my-phone-number-plugin' ); ?></option>
								</select>
								<p><?php esc_html_e( 'Select a field to search for phone numbers.', 'my-phone-number-plugin' ); ?></p>
							</div>
							<div class="form-field term-parent-wrap">
								<label for="parent"><?php esc_html_e( 'Order', 'my-phone-number-plugin' ); ?></label>
								<select name="spnrp_select_order" id="spnrp_select_order">
									<option value="ASC" <?php echo esc_html( 'ASC' == $spnrp_order ) ? 'selected' : ''; ?>><?php esc_html_e( 'Ascending', 'my-phone-number-plugin' ); ?></option>
									<option value="DESC" <?php echo esc_html( 'DESC' == $spnrp_order ) ? 'selected' : ''; ?>><?php esc_html_e( 'Descending', 'my-phone-number-plugin' ); ?></option>
								</select>
								<p><?php esc_html_e( 'Select the order of the search results.', 'my-phone-number-plugin' ); ?></p>
							</div>
							<p class="submit">
								<?php wp_nonce_field( 'spnrp-form-security', 'spnrp_form_security' ); ?>
								<input type="submit" name="spnrp_submit" id="spnrp_submit" class="button button-primary" value="<?php esc_attr_e( 'Search', 'my-phone-number-plugin' ); ?>">
							</p>
						</form>
					</div>
				</div><!-- /col-left -->
			</div><!-- /col-container -->
		</div>
	</div>
		<?php
	}
}
