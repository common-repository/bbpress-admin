<?php
add_action('admin_menu', 'bbpress_options_admin_add_page');
function bbpress_options_admin_add_page() {
	add_options_page(__('BBPress Options'), __('BBPress Options'), 'manage_options', basename(__FILE__),'bbpress_options_admin_page');
}

function bbpress_options_admin_page() {
	
if ($_POST['action'] == 'update') {
	
	bb_check_admin_referer( 'options-general-update' );
	
	foreach ( (array) $_POST as $option => $value ) {
		if ( !in_array( $option, array('_wpnonce', '_wp_http_referer', 'action', 'submit') ) ) {
			$option = trim( $option );
			$value = is_array( $value ) ? $value : trim( $value );
			$value = stripslashes_deep( $value );
			if ($option == 'uri') {
				$value = rtrim($value) . '/';
			}
			if ( $value ) {
				bb_update_option( $option, $value );
			} else {
				bb_delete_option( $option );
			}
		}
	}
	
	$goback = add_query_arg('updated', 'true', wp_get_referer());
	bb_safe_redirect($goback);
	
}

?>
<div class="wrap">
	<h2><?php _e('BBPress General Settings'); ?></h2>
	
	<form class="options" method="post" action="<?php bb_option('uri'); ?>bb-admin/options-general.php">
		<table class="form-table">
			<tr class="form-field">
				<th scope="row" valign="top"><label for="name"><?php _e('Site title'); ?></label></th>
				<td><input class="text" name="name" id="name" value="<?php bb_form_option('name'); ?>" /></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="uri"><?php _e('bbPress address (URL)'); ?></label></th>
				<td><input style="width:80%;" class="text" name="uri" id="uri" value="<?php bb_form_option('uri'); ?>" /><br/>
				<?php _e('The full URL of your bbPress install.'); ?></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="from_email"><?php _e('E-mail address'); ?></label></th>
				<td><input class="text" name="from_email" id="from_email" value="<?php bb_form_option('from_email'); ?>" /><br/>
				<?php _e('Emails sent by the site will appear to come from this address.'); ?></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="mod_rewrite"><?php _e('Pretty permalink type'); ?></label></th>
				<td><select name="mod_rewrite" id="mod_rewrite">
				<?php
				$selected = array();
				$selected[bb_get_option('mod_rewrite')] = ' selected="selected"';
				?>
						<option value="0"<?php echo $selected[0]; ?>><?php _e('None'); ?>&nbsp;&nbsp;&nbsp;.../forums.php?id=1</option>
						<option value="1"<?php echo $selected[1]; ?>><?php _e('Numeric'); ?>&nbsp;&nbsp;&nbsp;.../forums/1</option>
						<option value="slugs"<?php echo $selected['slugs']; ?>><?php _e('Name based'); ?>&nbsp;&nbsp;&nbsp;.../forums/first-forum</option>
				<?php
				unset($selected);
				?>
				</select></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="page_topics"><?php _e('Items per page'); ?></label></th>
				<td><input class="text" name="page_topics" id="page_topics" value="<?php bb_form_option('page_topics'); ?>" /><br/>
				<?php _e('Number of topics, posts or tags to show per page.') ?></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="edit_lock"><?php _e('Lock post editing after'); ?></label></th>
				<td><input class="text" name="edit_lock" id="edit_lock" value="<?php bb_form_option('edit_lock'); ?>" />
				<?php _e('minutes') ?><br/>
				<?php _e('A user can edit a post for this many minutes after submitting.') ?></td>
			</tr>
		</table>
		<h3>Date and Time</h3>
		<table class="form-table">
			<tr class="form-field">
				<th scope="row" valign="top"><label for=""><?php _e('<abbr title="Coordinated Universal Time">UTC</abbr> time is') ?></label></th>
				<td><?php echo gmdate(__('Y-m-d g:i:s a')); ?></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="gmt_offset"><?php _e('Times should differ from UTC by') ?></label></th>
				<td><input class="text" name="gmt_offset" id="gmt_offset" value="<?php bb_form_option('gmt_offset'); ?>" />
				<?php _e('hours') ?><br/>
				<?php _e('Example: -7 for Pacific Daylight Time.'); ?></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="datetime_format"><?php _e('Date and time format') ?></label></th>
				<td><input class="text" name="datetime_format" id="datetime_format" value="<?php echo(attribute_escape(bb_get_datetime_formatstring_i18n())); ?>" /><br/>
				<?php printf(__('Output: <strong>%s</strong>'), bb_datetime_format_i18n( bb_current_time() )); ?></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="date_format"><?php _e('Date format') ?></label></th>
				<td><input class="text" name="date_format" id="date_format" value="<?php echo(attribute_escape(bb_get_datetime_formatstring_i18n('date'))); ?>" /><br/>
				<?php printf(__('Output: <strong>%s</strong>'), bb_datetime_format_i18n( bb_current_time(), 'date' )); ?><br/>
				<?php _e('Click "Update settings" to update sample output.') ?><br/>
				<?php _e('<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date formatting</a>.'); ?></td>
			</tr>
		</table>
		<h3>Avatars</h3>
		<p>
			<?php _e('bbPress includes built-in support for <a href="http://gravatar.com/">Gravatars</a>, you can enable this feature here.'); ?>
		</p>
		<table class="form-table">
			<tr class="form-field">
				<th scope="row" valign="top"><label for="avatars_show"><?php _e('Show avatars') ?></label></th>
				<td><?php
				$checked = array();
				$checked[bb_get_option('avatars_show')] = ' checked="checked"';
				?>
							<input type="checkbox" class="checkbox" name="avatars_show" id="avatars_show" value="1"<?php echo $checked[1]; ?> />
				<?php
				unset($checked);
				?></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="avatars_rating"><?php _e('Gravatar maximum rating') ?></label></th>
				<td>
					<select name="avatars_rating" id="avatars_rating">
						<?php
						$selected = array();
						$selected[bb_get_option('avatars_rating')] = ' selected="selected"';
						?>
										<option value="0"<?php echo $selected[0]; ?>><?php _e('None'); ?></option>
										<option value="x"<?php echo $selected['x']; ?>><?php _e('X'); ?></option>
										<option value="r"<?php echo $selected['r']; ?>><?php _e('R'); ?></option>
										<option value="pg"<?php echo $selected['pg']; ?>><?php _e('PG'); ?></option>
										<option value="g"<?php echo $selected['g']; ?>><?php _e('G'); ?></option>
						<?php
						unset($selected);
						?>
					</select>
				<p>
					<img src="http://site.gravatar.com/images/gravatars/ratings/3.gif" alt="Rated X" style="height:30px; width:30px; float:left; margin-right:10px;" />
					<?php _e('X rated gravatars may contain hardcore sexual imagery or extremely disturbing violence.'); ?>
				</p>
				<p>
					<img src="http://site.gravatar.com/images/gravatars/ratings/2.gif" alt="Rated R" style="height:30px; width:30px; float:left; margin-right:10px;" />
					<?php _e('R rated gravatars may contain such things as harsh profanity, intense violence, nudity, or hard drug use.'); ?>
				</p>
				<p>
					<img src="http://site.gravatar.com/images/gravatars/ratings/1.gif" alt="Rated PG" style="height:30px; width:30px; float:left; margin-right:10px;" />
					<?php _e('PG rated gravatars may contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence.'); ?>
				</p>
				<p>
					<img src="http://site.gravatar.com/images/gravatars/ratings/0.gif" alt="Rated G" style="height:30px; width:30px; float:left; margin-right:10px;" />
					<?php _e('A G rated gravatar is suitable for display on all websites with any audience type.'); ?>
				</p>
				</td>
			</tr>
		</table>
		<?php bb_nonce_field( 'options-general-update' ); ?>
		<input type="hidden" name="action" value="update" />
		<p class="submit">
			<input type="submit" name="submit" value="<?php _e('Update Settings &raquo;') ?>" />
		</p>
	</form>

</div>


<?php
}
?>
