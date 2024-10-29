<?php
add_action('admin_menu', 'bbpress_topic_admin_add_page');
function bbpress_topic_admin_add_page() {
	add_management_page(__('Manage Forum Topics'), __('Forum Topics'), 'edit_post', basename(__FILE__),'bbpress_topic_admin_page');
}

function bbpress_topic_admin_page() {
	require_once(ABSPATH.'/forums/bb-admin/admin-functions.php');
	if ( !bb_current_user_can('browse_deleted') )
		die(__("Now how'd you get here?  And what did you think you'd being doing?")); //This should never happen.
	add_filter( 'topic_link', 'bb_make_link_view_all' );
	add_filter( 'topic_last_post_link', 'bb_make_link_view_all' );
	if ( !isset( $_GET['paged'] ) ) $_GET['paged'] = 1;
	$_GET['page'] = $_GET['paged'];
	$topic_query_vars = array('topic_status' => 1, 'open' => 'all', 'count' => true);
	if ( isset($_REQUEST['search']) && $_REQUEST['search'] )
		$topic_query_vars['post_status'] = 'all';
	$topic_query = new BB_Query_Form( 'topic', $topic_query_vars );
	$topics = $topic_query->results;
	wp_enqueue_script('admin-forms');
	?>
	<div class="wrap">
	<form id="posts-filter" action="" method="get">
	<h2><?php
	$h2_search = $topic_query->get( 'search' );
	$h2_forum  = $topic_query->get( 'forum_id' );
	$h2_tag    = $topic_query->get( 'tag_id' );
	$h2_author = $topic_query->get( 'topic_author_id' );
	$h2_status = $topic_query->get( 'topic_status' );
	$h2_open   = $topic_query->get( 'open' );
	
	$h2_search = $h2_search ? ' ' . sprintf( __('matching &#8220;%s&#8221;'), wp_specialchars( $h2_search ) ) : '';
	$h2_forum  = $h2_forum  ? ' ' . sprintf( __('in &#8220;%s&#8221;')      , get_forum_name( $h2_forum ) ) : '';
	$h2_tag    = $h2_tag    ? ' ' . sprintf( __('with tag &#8220;%s&#8221;'), wp_specialchars( bb_get_tag_name( $h2_tag ) ) ) : '';
	$h2_author = $h2_author ? ' ' . sprintf( __('by %s')                    , wp_specialchars( get_user_name( $h2_author ) ) ) : '';
	
	$topic_stati = array( 0 => __('Normal') . ' ', 1 => __('Deleted') . ' ', 'all' => '' );
	$topic_open  = array( 0 => __('Closed') . ' ', 1 => __('Open') . ' '   , 'all' => '' );
	
	if ( 'all' == $h2_status && 'all' == $h2_open )
		$h2_noun = __('Topics');
	else
		$h2_noun = sprintf( __( '%1$s%2$stopics'), $topic_stati[$h2_status], $topic_open[$h2_open] );
	
	printf( __( '%1$s%2$s%3$s%4$s%5$s' ), $h2_noun, $h2_search, $h2_forum, $h2_tag, $h2_author );
	$page_links = paginate_links( array(
		'base' => add_query_arg( 'paged', '%#%' ),
		'format' => '',
		'total' => ceil($topic_query->found_rows/bb_get_option('page_topics')),
		'current' => $_GET['paged']
	));
	?></h2>
		<div class="tablenav">
			<div class="alignleft">

				<input type="hidden" name="page" value="<?php echo basename(__FILE__); ?>" />

				<legend>Search </legend>
				<input name='search' id='search' type='text' class='text-input' value='<?php echo $topic_query->get( 'search' ); ?>' size="10" />
				
				<?php $forums = get_forums();?>
				<select name='forum_id' id='forum-id' >
				<option value='0'>Show All Forums</option>
				<?php foreach($forums as $forum) :?>
					<option value='<?php echo $forum->forum_id; ?>' <?php echo ($topic_query->get( 'forum_id' ) == $forum->forum_id)? "selected" :"" ?> ><?php echo $forum->forum_name; ?></option>
				<?php endforeach; ?>
				</select>
	

				<legend>Tag</legend>
				<input name='tag' id='topic-tag' type='text' class='text-input' value='<?php echo $topic_query->get('tag'); ?>' size="10" />

				<legend>Topic Author</legend>
				<input name='topic_author' id='topic-author' type='text' class='text-input' value='<?php echo $topic_query->get('topic_author'); ?>' size="10"  />

				<select name='topic_status' id='topic-status'>
					<option value='all' <?php echo ($topic_query->get( 'topic_status' ) == 'all')? "selected" :"" ?> >All Topic Status</option>
					<option value='0' <?php echo ($topic_query->get( 'topic_status' ) == '0')? "selected" :"" ?> >Normal</option>
					<option value='1' <?php echo ($topic_query->get( 'topic_status' ) == '1')? "selected" :"" ?> >Deleted</option>
				</select>
	
				<select name='open' id='topic-open'>
					<option value='all' <?php echo ($topic_query->get( 'open' ) == 'all')? "selected" :"" ?> >Open or Closed</option>
					<option value='1' <?php echo ($topic_query->get( 'open' ) == '1')? "selected" :"" ?> >Open</option>
					<option value='0' <?php echo ($topic_query->get( 'open' ) == '0')? "selected" :"" ?> >Closed</option>
				</select>

				<input type='submit' class='button-secondary' value='Filter' id='topic-search-form-submit' />
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
					<th><?php _e('Topic') ?></th>
					<th class="num"><?php _e('Last Poster') ?></th>
					<th class="num"><?php _e('Freshness') ?></th>	
				</tr>
			</thead>
			<tbody>
				<?php 
				global $topic;
				$alternate = 1; 
				?>
				<?php if($topics): foreach($topics as $topic) :?>
					<tr <?php echo (($alternate = !$alternate) ? 'class="alternate"' : '' ); ?>>
						<td><?php bb_topic_labels(); ?> <a href="<?php topic_link(); ?>"><?php topic_title(); ?></a></td>
						<td class="num"><?php topic_last_poster(); ?></td>
						<td class="num"><a href="<?php topic_last_post_link(); ?>"><?php topic_time(); ?></a></td>
					</tr>
				<?php endforeach; ?>
				<?php else: ?>
					<tr <?php echo (($alternate = !$alternate) ? 'class="alternate"' : '' ); ?>>
						<td colspan="3">No Topics Found</td>
					</tr>
				<?php endif; ?>
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