<?php
add_action('admin_menu', 'bbpress_theme_admin_add_page');
function bbpress_theme_admin_add_page() {
	add_theme_page(__('Manage BBpress Theme'), __('BBpress Theme'), 'switch_themes', basename(__FILE__),'bbpress_theme_admin_page');
}

function bbpress_theme_admin_page() {
	require_once(ABSPATH.'/forums/bb-admin/admin-functions.php');
	$themes = bb_get_themes();
	
	$activetheme = bb_get_option('bb_active_theme');
	if (!$activetheme) {
		$activetheme = BB_DEFAULT_THEME;
	}
	
	if ( !in_array($activetheme, $themes) ) :
		if ($activetheme == BB_DEFAULT_THEME) {
			?>
			<div id="message1" class="updated fade"><p><?php _e('Default theme is missing.'); ?></p></div>
			<?php
		} else {
			bb_delete_option( 'bb_active_theme' );
			?>
			<div id="message1" class="updated fade"><p><?php _e('The active theme is broken.  Reverting to the default theme.'); ?></p></div>
			<?php
		}
	elseif ( isset($_GET['activated']) ) : ?>
		<div id="message2" class="updated fade"><p><?php printf(__('New theme activated. <a href="%s" target="_blank">Visit site</a>'), bb_get_option( 'uri' )); ?></p></div>
	<?php endif; ?>

	<div class="wrap">
		<h2><?php _e('Current Theme'); ?></h2>
		<?php
		$c_theme = $themes[$activetheme];
		$theme_directory = bb_get_theme_directory( $c_theme );
		$theme_data = file_exists( $theme_directory . 'style.css' ) ? bb_get_theme_data( $c_theme ) : false;
		$screen_shot = file_exists( $theme_directory . 'screenshot.png' ) ? clean_url( bb_get_theme_uri( $c_theme ) . 'screenshot.png' ) : false;
		$activation_url = clean_url( bb_nonce_url( add_query_arg( 'theme', urlencode($c_theme) ), 'switch-theme' ) );
		?>
		<div id="currenttheme">
			<?php if ( $screen_shot ) : ?>
				<img src="<?php echo $screen_shot; ?>" alt="<?php _e('Current theme preview'); ?>" />
			<?php endif; ?>
			<h3><?php printf(_c('%1$s %2$s by %3$s|1: theme title, 2: theme version, 3: theme author'), $theme_data['Title'], $theme_data['Version'], $theme_data['Author']) ; ?></h3>
			<p><?php echo $theme_data['Description']; ?></p>
			<p><?php printf(__('Installed in: %s'), str_replace(array('core#', 'user#'), array(__('Core themes -&gt; '), __('User installed themes -&gt; ')),  $c_theme)); ?></p>
		</div>
		<?php unset($themes[$activetheme] );  ?>

	<h2><?php _e('Available Themes'); ?></h2>
	<?php if ( 1 < count($themes) ) { ?>
		<?php
		$style = '';
		
		$theme_names = array_keys($themes);
		natcasesort($theme_names);
		
		foreach ($themes as $c_theme) {
			$theme_directory = bb_get_theme_directory( $c_theme );
			$theme_data = file_exists( $theme_directory . 'style.css' ) ? bb_get_theme_data( $c_theme ) : false;
			$screen_shot = file_exists( $theme_directory . 'screenshot.png' ) ? clean_url( bb_get_theme_uri( $c_theme ) . 'screenshot.png' ) : false;
			$activation_url = clean_url( bb_nonce_url( add_query_arg( 'theme', urlencode($c_theme)), 'switch-theme' ) );
			?>
			<div class="available-theme">
				<h3><a href="<?php echo $activation_url; ?>" title="<?php echo attribute_escape( __('Click to activate') ); ?>"><?php echo $theme_data['Title']; ?></a></h3>
				<a href="<?php echo $activation_url; ?>" title="<?php echo attribute_escape( __('Click to activate') ); ?>"  class="screenshot">
					<?php if ( $screen_shot ) : ?>
						<img alt="<?php echo attribute_escape( $theme_data['Title'] ); ?>" src="<?php echo $screen_shot; ?>" />
					<?php endif; ?>
				</a>
				
				<p><?php echo $theme_data['Description']; // Description is autop'ed ?></p>
			</div>
		<?php } // end foreach theme_names ?>
	<?php } ?> 
	</div>
	<?php
}

add_action('admin_init','bbpress_theme_admin_process_post');
function bbpress_theme_admin_process_post() {
	
	global $plugin_page;
	
	if($plugin_page == basename(__FILE__) ) :
		require_once(ABSPATH.'/forums/bb-admin/admin-functions.php');
		$themes = bb_get_themes();
		
		$activetheme = bb_get_option('bb_active_theme');
		if (!$activetheme) {
			$activetheme = BB_DEFAULT_THEME;
		}
		
		if ( isset($_GET['theme']) ) {
			if ( !bb_current_user_can( 'manage_themes' ) ) {
				wp_redirect( bb_get_option( 'uri' ) );
				exit;
			}
			
			bb_check_admin_referer( 'switch-theme' );
			do_action( 'bb_deactivate_theme_' . $activetheme );
			
			$theme = stripslashes($_GET['theme']);
			$theme_data = bb_get_theme_data( $theme );
			if ($theme_data['Name']) {
				$name = $theme_data['Name'];
			} else {
				$name = str_replace(array('core#', 'user#'), '', $theme);
			}
			if ($theme == BB_DEFAULT_THEME) {
				bb_delete_option( 'bb_active_theme' );
			} else {
				bb_update_option( 'bb_active_theme', $theme );
			}
			do_action( 'bb_activate_theme_' . $theme );
			$goto = add_query_arg( 
				array(	
					'activated' => '',
					'name' => urlencode($name) 
				)
			);
			$goto = remove_query_arg(array('theme','_wpnonce'),$goto);
			wp_redirect($goto); 
			
			exit;
		}
	endif;
}

?>