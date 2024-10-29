<?php

add_action('admin_menu', 'bbpress_posts_admin_add_page');
function bbpress_posts_admin_add_page() {
	add_management_page(__('Manage Forum Posts'), __('Forum Posts'), 'edit_post', basename(__FILE__),'bbpress_posts_admin_page');
}

function bbpress_posts_admin_page() {
	require_once(ABSPATH.'/forums/bb-admin/admin-functions.php');
	add_filter( 'get_topic_where', 'no_where' );
	add_filter( 'get_topic_link', 'bb_make_link_view_all' );
	if ( !isset( $_GET['paged'] ) ) $_GET['paged'] = 1;
	$_GET['page'] = $_GET['paged'];
	global $bb_post, $bb_posts,$total,$post_query;
	$post_query = new BB_Query_Form( 'post', array( 'post_status' => 1, 'count' => true ) );
	$bb_posts =& $post_query->results;
	$total = $post_query->found_rows;
	wp_enqueue_script('admin-forms');
	?>
	<div class="wrap">
	<form id="posts-filter" action="" method="get">
	<h2><?php
	$h2_search = $post_query->get( 'post_text' );
	$h2_forum  = $post_query->get( 'forum_id' );
	$h2_tag    = $post_query->get( 'tag_id' );
	$h2_author = $post_query->get( 'post_author_id' );
	$h2_status = $post_query->get( 'post_status' );
	
	$h2_search = $h2_search ? ' ' . sprintf( __('matching &#8220;%s&#8221;'), wp_specialchars( $h2_search ) ) : '';
	$h2_forum  = $h2_forum  ? ' ' . sprintf( __('in &#8220;%s&#8221;')      , get_forum_name( $h2_forum ) ) : '';
	$h2_tag    = $h2_tag    ? ' ' . sprintf( __('with tag &#8220;%s&#8221;'), wp_specialchars( bb_get_tag_name( $h2_tag ) ) ) : '';
	$h2_author = $h2_author ? ' ' . sprintf( __('by %s')                    , wp_specialchars( get_user_name( $h2_author ) ) ) : '';
	
	$stati = array( 0 => __('Normal') . ' ', 1 => __('Deleted') . ' ', 'all' => '' );
	
	if ( 'all' == $h2_status )
		$h2_noun = __('Posts');
	else
		$h2_noun = sprintf( __( '%1$sposts'), $stati[$h2_status], $topic_open[$h2_open] );
	
	printf( __( '%1$s%2$s%3$s%4$s%5$s' ), $h2_noun, $h2_search, $h2_forum, $h2_tag, $h2_author );
	$page_links = paginate_links( array(
		'base' => add_query_arg( 'paged', '%#%' ),
		'format' => '',
		'total' => ceil($post_query->found_rows/bb_get_option('page_topics')),
		'current' => $_GET['paged']
	));
	?></h2>
		<div class="tablenav">
			<div class="alignleft">
				<input type="hidden" name="page" value="<?php echo basename(__FILE__); ?>" />

				<legend>Search</legend>
				<input name='post_text' size="10" id='post-text' type='text' class='text-input' value='<?php echo $post_query->get('post_text');?>' />	</fieldset>

				<?php $forums = get_forums();?>
				<select name='forum_id' id='forum-id'>
				<option value='0'>Show All Forums</option>
				<?php foreach($forums as $forum) :?>
					<option value='<?php echo $forum->forum_id; ?>' <?php echo ($post_query->get( 'forum_id' ) == $forum->forum_id)? "selected" :"" ?> ><?php echo $forum->forum_name; ?></option>
				<?php endforeach; ?>
				</select>

				<legend>Tag</legend>
				<input name='tag' size="10" id='topic-tag' type='text' class='text-input' value='<?php echo $post_query->get('tag');?>' />	

				<legend>Post Author</legend>
				<input name='post_author' size="10" id='post-author' type='text' class='text-input' value='<?php echo $post_query->get('post_author');?>' />	

				<select name='post_status' id='post-status'>
					<option value='all' <?php echo ($post_query->get( 'post_status' ) == 'all')? "selected" :"" ?> >All Topic Status</option>
					<option value='0' <?php echo ($post_query->get( 'post_status' ) == '0')? "selected" :"" ?> >Normal</option>
					<option value='1' <?php echo ($post_query->get( 'post_status' ) == '1')? "selected" :"" ?> >Deleted</option>
				</select>	
				<input type='submit' class='button submit-input button-secondary' value='Filter' id='post-search-form-submit' />
			</div>
			<?php
			if ( $page_links )
				echo "<div class='tablenav-pages'>$page_links</div>";
			?>			
			<br class="clear" />
		</div>
	<br class="clear" />
		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e('Post Author') ?></th>
					<th><?php _e('Post Content') ?></th>	
					<th class="num" colspan="2"><?php _e('Actions') ?></th>	
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $bb_posts as $bb_post ) : ?>
				<tr <?php echo (($alternate = !$alternate) ? 'class="alternate"' : '' ); ?>>
					<td>
						<p><strong><?php post_author_link(); ?></strong><br />
						<small><?php post_author_type(); ?></small><br/>
						<small><?php post_ip_link(); ?></small></p>
					</td>
					<td>
						<?php post_text(); ?>
						<p><?php printf(__('Posted: %1$s in <a href="%2$s">%3$s</a>'), bb_get_post_time(), get_topic_link( $bb_post->topic_id ), get_topic_title( $bb_post->topic_id ));?></p>
					</td>
					<td>
						<p><?php post_edit_link(); ?></p>
					</td>
					<td>
						<p><?php post_delete_link();?></p>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>		
			<?php
			if ( $page_links )
				echo "<div class='tablenav'><div class='tablenav-pages'>$page_links</div><br class='clear' /></div>";
			?>
	</div>	
	<?php		
}

?>