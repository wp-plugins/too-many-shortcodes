<?php
/*
Plugin Name: Too Many Shortcodes
Plugin URI: http://wikiduh.com/plugins/too-many-shortcodes
Description: All of your shortcodes on one settings page, view or change the trigger text, enable or disable individual shortcodes, see the optional arguments and explaination links for each shortcode. Shortcodes added with every update.
Version: 1.1.0
Author: bitacre
Author URI: http://wikiduh.com
License: GPLv2 
	Copyright 2012 bitacre (plugins@wikiduh.com)
*/

function tmsc_default_options() {
	$temp_array = array();
	
	$temp_array = array_merge( $temp_array, tmsc_register_shortcode(
		'lastupdate',
		'lastupdate lastupdated',
		'displays date/time of last post/page update',
		'(format) (before) (after)',
		'tmsc_last_updated' ) );
	
	$temp_array = array_merge( $temp_array,  tmsc_register_shortcode(
		'countposts',
		'countposts',
		'returns the number of posts when given a category slug',
		'cat (type)',
		'tmsc_cat_count_posts' ) );
	
	$temp_array = array_merge( $temp_array,  tmsc_register_shortcode(
		'htmlcomment',
		'htmlcomment comment',
		'inserts a HTML comment that the WP editor won\'t remove',
		'$content',
		'tmsc_html_comment' ) );
		
	$temp_array = array_merge( $temp_array,  tmsc_register_shortcode(
		'spamcomments',
		'spamcomments spam spams',
		'returns the number of comments you\'ve marked as spam',
		'(before) (after)',
		'tmsc_spam_comments' ) );	
	
	$temp_array = array_merge( $temp_array,  tmsc_register_shortcode(
		'anchor',
		'anchor a' ,
		'inserts an HTML anchor for linking',
		'$content',
		'tmsc_anchor' ) );
	
	ksort( $temp_array );
	return $temp_array;
}

function tmsc_register_shortcode( $slug, $trigger, $description, $the_args, $the_callback, $enabled = 1 ) {
	return array( 
		$slug => array( 
			'slug'			=> $slug, // unique option id
			'trigger' 		=> $trigger, // default trigger(s); space seperated
			'description'	=> $description, // short description of functionality
			'args'			=> $the_args, // description of accepted args: required (optional) $content
			'callback'		=> $the_callback, // shortcode function name
			'enabled'		=> $enabled // all on by default
		) 
	);
}

function tmsc_array_add_shortcode( $shortcode_array, $callback_function ) {
// registers an array of strings as shortcodes to a single callback
	foreach( $shortcode_array as $shortcode ) add_shortcode( trim( $shortcode ), $callback_function );
}

function tmsc_set_plugin_meta( $links, $file ) { 
// defines additional plugin meta links (appearing under plugin on Plugins page)
	$plugin_base = plugin_basename(__FILE__);
    if ( $file == $plugin_base ) {
		$newlinks = array( '<a href="options-general.php?page=too-many-shortcodes">Settings</a>' ); 
		return array_merge( $links, $newlinks ); // merge new links into existing $links
	}
	return $links;
}

function tmsc_options_init() { 
// adds plugin's options to white list
	register_setting( 'too-many-shortcodes-options-group', 'too-many-shortcodes-options', 'tmsc_options_validate' );
}

function tmsc_options_validate( $input ) {
	
	return $input;
}
			
function tmsc_add_options_page() { 
// adds link to plugin's settings page under 'settings' on the admin menu 
	add_options_page( 'Too Many Shortcodes Settings', 'Shortcodes', 'manage_options', 'too-many-shortcodes', 'tmsc_draw_options_page' );
}

function tmsc_get_options() {
	$defaults = tmsc_default_options();
	$options = get_option( 'too-many-shortcodes-options', $defaults );
	
	// load deafult subarray if count mismatch
	if( count( $defaults ) != count( $options ) )
		foreach( $defaults as $default )
			if( empty( $options[$default['slug']] ) ) $options[$default['slug']] = $default;

	return $options;
}

function tmsc_draw_options_page() { 
// html code of options page

	// restore to default
	if( $_GET['restore_default'] == 1 ) {
		delete_option( 'too-many-shortcodes-options' );
		echo '<div class="updated settings-error">Settings reset to defaults.</div>';
	}
	
	$options = tmsc_get_options();
?>
	<div class="wrap">
    <div class="icon32" id="icon-options-general"><br /></div>
		<h2>Too Many Shortcodes Settings</h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'too-many-shortcodes-options-group' ); ?>
						
			<!-- Description -->
			<p style="font-size:0.95em">You are using version 1.1.0 of the <a href="http://wikiduh.com/plugins/too-many-shortcodes">Too Many Shortcodes</a> plugin, with support for <?php echo count( $options); ?> shortcodes. You may change the triggers for these shortcodes or add more (seperated by a space) in the text boxes; or disable them completely by unchecking the box. Click the <a href="http://wikiduh.com/plugins/too-many-shortcodes" target="_blank">help</a> link next to a shortcode to read more about it (opens in a new tab/window). Please post requests for additional shortcodes to the <a href="http://wikiduh.com/plugins/too-many-shortcodes#comments" target="_blank">comments</a> section of this plugin's homepage.</p>
			
			<table class="form-table">
				<tr valign="middle">
					<th scope="col"><strong>Shortcodes</strong></th> 
					<th scope="col"><strong>Description</strong></th>
					<th scope="col"><strong>Arguments (Optional)</strong></th>
					<th scope="col"><strong>Help</strong></th>
					<th scope="col"><strong>Enabled</strong></th>
				</tr>
<?php foreach( $options as $option ) tmsc_draw_options_line( $option['slug'], $option['trigger'], $option['description'],  $option['args'], $option['enabled'] ); ?>
			</table>
			
			<p class="submit">
			<p class="alignleft">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
			</p>
			</form>
			<form method="get" action="options.php">
				<?php settings_fields( 'too-many-shortcodes-options-group' ); ?>
				<input type="hidden" name="restore_default" value="1" />
			<p class="alignleft">
				&nbsp;<input type="submit" class="button-primary" value="<?php _e( 'Restore Defaults' ) ?>" />
			</p>
			</form>
			</p>
					
	</div>
<?php 
}

function tmsc_draw_options_line( $slug, $trigger, $description, $the_args, $enabled ) { 
?>
				<tr valign="middle">
					<td><input type="text" name="too-many-shortcodes-options[<?php echo $slug; ?>][trigger]" class="form-text" value="<?php echo $trigger; ?>" /></td>
					<td scope="row"><?php echo $description; ?><input type="hidden" name="too-many-shortcodes-options[<?php echo $slug; ?>][description]" value="<?php echo $description; ?>" /></td>
					<td><?php echo $the_args; ?><input type="hidden" name="too-many-shortcodes-options[<?php echo $slug; ?>][args]" value="<?php echo $the_args; ?>" /></td>
					<td><span style="font-size:0.9em;"><a href="http://wikiduh.com/plugins/too-many-shortcodes/help#<?php echo $slug; ?>"><input type="hidden" name="too-many-shortcodes-options[<?php echo $slug; ?>][slug]" value="<?php echo $slug; ?>" />view</a></span></td>
					<td><input type="checkbox" name="too-many-shortcodes-options[<?php echo $slug; ?>][enabled]" value="1"<?php checked( $enabled ); ?>/></td>
				</tr>

<?php 
}

// SHORTCODE FUNCTIONS

function tmsc_last_updated( $atts ) {
	extract( shortcode_atts( array( 'format' => 'F j, Y \a\t G:i a', 'before' => 'Last updated:', 'after' => '' ), $atts ) );
	return the_modified_date( $format, $before . ' ', $after, 0 );
}

function tmsc_cat_count_posts( $atts ) {
	extract( shortcode_atts( array( // extract arguments
		'cat' => NULL, 
		'type' => NULL,
	), $atts ) ); 
	
	// determine type
	if( $type == 'name' ) $utype = 'name'; // category name
	elseif( $type == 'id' ) $utype = 'cat_ID'; // category ID number
	else $utype = 'slug'; // otherwise assume slug

	$categories = get_categories(); // load all categories into array
	foreach( $categories as $category ) 
		if( $category->$utype == $cat ) return $category->count; // return count on match
	return 0; // else return 0
}

function tmsc_html_comment( $atts, $content = null ) {
	   	return '<!-- ' . $content . ' -->';
}

function tmsc_spam_comments( $atts ) {
	extract( shortcode_atts( array( // extract arguments
		'before' => NULL, 
		'after' => NULL,
	), $atts ) );

	return $before . count( get_comments( 'status=spam' ) ) . $after;
}

function tmsc_anchor( $atts, $content ) {
	extract( shortcode_atts( array( // extract arguments
		'name' => NULL, 
	), $atts ) );
   	return '<a name="' . $name . '">' . $content . ' </a>';
}

// HOOKS AND FILTERS
add_filter( 'plugin_row_meta', 'tmsc_set_plugin_meta', 10, 2 ); // add plugin page meta links
add_action( 'admin_init', 'tmsc_options_init' ); // whitelist options page
add_action( 'admin_menu', 'tmsc_add_options_page' ); // add link to plugin's settings page in 'settings' menu

$options = tmsc_get_options();
foreach( tmsc_default_options() as $default )
	if( $options[$default['slug']]['enabled'] ) tmsc_array_add_shortcode( explode( ' ', $default['trigger'] ), $default['callback'] );
?>