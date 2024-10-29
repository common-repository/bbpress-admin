<?php
add_action('admin_menu', 'bbpress_forum_admin_add_page');
function bbpress_forum_admin_add_page() {
	add_management_page(__('Manage Forums'), __('Forums'), 'edit_post', basename(__FILE__),'bbpress_forum_admin_page');
}

function bbpress_forum_admin_page() {
	require_once(ABSPATH.'/forums/bb-admin/admin-functions.php');
	
	$forums = get_forums();
	$forums_count = $forums ? count($forums) : 0;
	
	if ( isset($_GET['action']) && 'delete' == $_GET['action'] ) {
		$forum_to_delete = (int) $_GET['id'];
		$deleted_forum = get_forum( $forum_to_delete );
		if ( !$deleted_forum || $forums_count < 2 || !bb_current_user_can( 'delete_forum', $forum_to_delete ) )
			bb_safe_redirect( add_query_arg( array('action' => false, 'id' => false) ) );
	}
	$base_link = remove_query_arg(array('page','action','id','message')); 
	$base_link = add_query_arg(array('page' => basename(__FILE__)), $base_link );
	
/*	if ( isset($_GET['message']) ) {
		switch ( $_GET['message'] ) :
		case 'updated' :
			bb_admin_notice( __('Forum Updated.') );
			break;
		case 'deleted' :
			bb_admin_notice( sprintf(__('Forum deleted.  You should have bbPress <a href="%s">recount your site information</a>.'), bb_get_option( 'uri' ) . 'bb-admin/site.php') );
			break;
		endswitch;
	}
*/

	switch ( @$_GET['action'] ) { 
		case 'edit' : 
			$forum_id = (int) $_GET['id']; 
			?>
			<div class="wrap">
				<h2><?php _e('Update Forum'); ?></h2>
				<br class="clear" />		
				<form method="post" id="update-forum" action="<?php bb_option('uri'); ?>bb-admin/bb-forum.php">
				
					<?php bb_nonce_field( 'order-forums', 'order-nonce' ); ?>
					<?php bb_nonce_field( "update-forum" ); ?>
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>" />
					<table class="form-table">
						<tr class="form-field form-required">
							<th scope="row" valign="top"><label for="forum-name">Forum Name</label></th>
							<td><input type="text" name="forum_name" id="forum-name" value="<?php if ( $forum_id ) echo attribute_escape( get_forum_name( $forum_id ) ); ?>" /></td>
						</tr>
						<tr class="form-field">
							<th scope="row" valign="top"><label for="forum-desc">Forum Description</label></th>
							<td><textarea name="forum_desc" id="forum-desc" rows="5" cols="50" style="width: 97%;"><?php if ( $forum_id ) echo attribute_escape( get_forum_description( $forum_id ) ); ?></textarea></td>
						</tr>
						<tr class="form-field">
							<th scope="row" valign="top"><label for="forum-parent">Forum Parent</label></th>
							<td>
								<?php bb_forum_dropdown( array('cut_branch' => $forum_id, 'id' => 'forum_parent', 'none' => true, 'selected' => $forum_id ? get_forum_parent( $forum_id ) : 0) ); ?>
							</td>
						</tr>
						<tr class="form-field">
							<th scope="row" valign="top"><label for="forum-order">Position</label></th>
							<td><input type="text" name="forum_order" id="forum-order" value="<?php if ( $forum_id ) echo get_forum_position( $forum_id ); ?>" maxlength="10" /></td>
						</tr>
					</table>
					<p class="submit"><input type="submit" class="button" name="submit" value="Update Forum" /></p>
				</form>
			</div>
			<?php break; 
		case 'delete' : ?>
			<div class="wrap">
				<h2><?php _e('Delete Forum'); ?></h2>
				<br class="clear" />
				<p><big><?php printf(__('Are you sure you want to delete the "<strong>%s</strong>" forum?'), $deleted_forum->forum_name); ?></big></p>
				<p><?php _e('This forum contains'); ?></p>
				<ul>
					<li><?php printf(__ngettext('%d topic', '%d topics', $deleted_forum->topics), $deleted_forum->topics); ?></li>
					<li><?php printf(__ngettext('%d post', '%d posts', $deleted_forum->posts), $deleted_forum->posts); ?></li>
				</ul>
			
				<form method="post" id="delete-forums" action="<?php bb_option('uri'); ?>bb-admin/bb-forum.php">
					<p>
						<label for="move-topics-delete"><input type="radio" name="move_topics" id="move-topics-delete" value="delete" /> <?php _e('Delete all topics and posts in this forum. <em>This can never be undone.</em>'); ?></label><br />
						<label for="move-topics-move"><input type="radio" name="move_topics" id="move-topics-move" value="move" checked="checked" /> <?php _e('Move topics from this forum into'); ?></label>
						<?php bb_forum_dropdown( array('id' => 'move_topics_forum', 'callback' => 'strcmp', 'callback_args' => array($deleted_forum->forum_id), 'selected' => $deleted_forum->forum_parent) ); ?>
					</p>
					<p class="submit alignright">
						<input class="delete" name="Submit" type="submit" value="<?php _e('Delete forum &raquo;'); ?>" tabindex="10" />
						<input type="hidden" name="action" value="delete" />
						<input type="hidden" name="forum_id" value="<?php echo $deleted_forum->forum_id; ?>" />
					</p>
					<?php bb_nonce_field( 'delete-forums' ); ?>
				</form>
				<form method="post" action="http://localhost/wordpress/wp-admin/edit.php?page=bbpress-forum-admin.php">
					<p class="submit alignleft">
						<input type="submit" name="submit" value="<?php _e('&laquo; Go back'); ?>" />
					</p>
				</form>
			</div>
			<?php break; 
		default : ?>
			<div class="wrap">
			<h2><?php _e('Forum Management'); ?></h2>
			<br class="clear" />
			<?php if ( $forums) : ?>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php _e('Name') ?></th>
							<th><?php _e('Description') ?></th>
							<th class="num" colspan="2"><?php _e('Actions') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $forums as $forum ) : ?>
						<?php 
						$edit_link = add_query_arg( 
							array(	
								'action' => 'edit',
								'id' => $forum->forum_id 
							), $base_link );
						$delete_link = add_query_arg( 
							array(	
								'action' => 'delete',
								'id' => $forum->forum_id 
							), $base_link );
						?>
						<tr <?php echo (($alternate = !$alternate) ? 'class="alternate"' : '' ); ?>>
							<td><?php echo get_forum_name( $forum->forum_id ); ?></td>
							<td><?php echo get_forum_description( $forum->forum_id ); ?></td>
							<td><?php echo "<a class='edit' href='$edit_link'>" . __('Edit') . "</a>"; ?></td>
							<td><?php echo "<a class='delete' href='$delete_link'>" . __('Delete') . "</a>"; ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>		
			<?php endif; // bb_forums() ?>
			</div>
			<div class="wrap">
				<h2>Add Category</h2>
				<form method="post" id="add-forum" action="<?php bb_option('uri'); ?>bb-admin/bb-forum.php">
				
					<?php bb_nonce_field( "add-forum" ); ?>
					<input type="hidden" name="action" value="add" />
					<table class="form-table">
						<tr class="form-field form-required">
							<th scope="row" valign="top"><label for="forum-name">Forum Name</label></th>
							<td><input type="text" name="forum_name" id="forum-name" value="" /></td>
						</tr>
						<tr class="form-field">
							<th scope="row" valign="top"><label for="forum-desc">Forum Description</label></th>
							<td><textarea name="forum_desc" id="forum-desc" rows="5" cols="50"></textarea></td>
						</tr>
					</table>
					<p class="submit"><input type="submit" class="button" name="submit" value="Add Forum" /></p>
				</form>
			</div>			
			<?php break; 
	} ?>

<?php	
}
?>