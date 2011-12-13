<?php
/*
 * Plugin Name: Ciptcha
 * Plugin URI:  http://www.ciptcha.com/
 * Description: Plugin Ciptcha intended to prove that the visitor is a human being and not a spam robot. Plugin asks the visitor to answer a math question.
 * Author: Ciptcha
 * Version: 1.4
 * Author URI: http://www.ciptcha.com/


 * CIPTCHA - Completely Image-Based Public Turing test to tell Computers and Humans Apart
 *
 * http://www.ciptcha.com/
 *
 * Copyright 2011, SWM
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software 
 * and associated documentation files (the "Software"), to deal in the Software without restriction, 
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, 
 * sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or 
 * substantial portions of the Software.
 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING 
 * BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 *-------------------------------------------------------------------------------------------------
 */
// These fields for the 'Enable CIPTCHA on the' block which is located at the admin setting CIPTCHA page
$cptch_admin_fields_enable = array (
		array( 'cptch_login_form', 'Login form', 'Login form' ),
		array( 'cptch_register_form', 'Register form', 'Register form' ),
		array( 'cptch_lost_password_form', 'Lost password form', 'Lost password form' ),
		array( 'cptch_comments_form', 'Comments form', 'Comments form' ),
		array( 'cptch_hide_register', 'Hide CIPTCHA for registered users', 'Hide CIPTCHA for registered users' ),		

);

$cptch_admin_fields_actions = array (
		array( 'cptch_displaytype_modal', 'modal', 'modal' ),
		array( 'cptch_displaytype_inpage', 'inpage', 'inpage' ),
		array( 'cptch_displaytype_popout', 'popout', 'popout' ),
);

$cptch_admin_fields_lang = array (
		array( 'clang_us', 'us', 'us' ),
		array( 'clang_de', 'de', 'de' ),
);

add_action( 'admin_menu', 'add_cptch_admin_menu' );

$active_plugins = get_option('active_plugins');
if( 0 < count( preg_grep( '/contact-form-plugin\/contact_form.php/', $active_plugins ) ) )
{
	$cptch_options = get_option( 'cptch_options' );
	if( $cptch_options['cptch_contact_form'] == 1)
	{
		add_filter('cntctfrm_display_captcha', 'cptch_custom_form');
		add_filter('cntctfrm_check_form', 'cptch_check_custom_form');
	}
	if( $cptch_options['cptch_contact_form'] == 0 )
	{
		remove_filter('cntctfrm_display_captcha', 'cptch_custom_form');
		remove_filter('cntctfrm_check_form', 'cptch_check_custom_form');
	}
}

function add_cptch_admin_menu() {
	add_submenu_page('my_new_menu', 'Ciptcha Options', 'Ciptcha', 'manage_options', "captcha.php", 'cptch_settings_page');

	//call register settings function
	add_action( 'admin_init', 'register_cptch_settings' );
}

// register settings function
function register_cptch_settings() {
	global $wpmu;
	global $cptch_options;

	$cptch_option_defaults = array(
		'cptch_login_form' => '1',
		'cptch_register_form' => '1',
		'cptch_lost_password_form' => '1',
		'cptch_comments_form' => '1',
		'cptch_hide_register' => '1',
		'cptch_label_form' => '',
		'cptch_displaytype' => '',

  );

  // install the option defaults
	if ( 1 == $wpmu ) {
		if( !get_site_option( 'cptch_options' ) ) {
			add_site_option( 'cptch_options', $cptch_option_defaults, '', 'yes' );
		}
	} 
	else {
		if( !get_option( 'cptch_options' ) )
			add_option( 'cptch_options', $cptch_option_defaults, '', 'yes' );
	}

  // get options from the database
  if ( 1 == $wpmu )
   $cptch_options = get_site_option( 'cptch_options' ); // get options from the database
  else
   $cptch_options = get_option( 'cptch_options' );// get options from the database

  // array merge incase this version has added new options
  $cptch_options = array_merge( $cptch_option_defaults, $cptch_options );
}

// Add global setting for CIPTCHA
global $wpmu;

if ( 1 == $wpmu )
   $cptch_options = get_site_option( 'cptch_options' ); // get the options from the database
  else
   $cptch_options = get_option( 'cptch_options' );// get the options from the database

// Add CIPTCHA into login form
if( 1 == $cptch_options['cptch_login_form'] ) {
	add_action( 'login_form', 'cptch_login_form' );
	add_filter( 'login_errors', 'cptch_login_post' );
	add_filter( 'login_redirect', 'cptch_login_check', 10, 3 ); 
	//add_filter( 'login_redirect', 'admin_default_page');
}
// Add CIPTCHA into comments form
if( 1 == $cptch_options['cptch_comments_form'] ) {
	global $wp_version;
	if( version_compare($wp_version,'3','>=') ) { // wp 3.0 +
		add_action( 'comment_form_after_fields', 'cptch_comment_form_wp3', 1 );
		add_action( 'comment_form_logged_in_after', 'cptch_comment_form_wp3', 1 );
	}	
	// for WP before WP 3.0
	add_action( 'comment_form', 'cptch_comment_form' );
	add_filter( 'preprocess_comment', 'cptch_comment_post' );	
}
// Add CIPTCHA in the register form
if( 1 == $cptch_options['cptch_register_form'] ) {
	add_action( 'register_form', 'cptch_register_form' );
	add_action( 'register_post', 'cptch_register_post', 10, 3 );
}
// Add CIPTCHA into lost password form
if( 1 == $cptch_options['cptch_lost_password_form'] ) {
	add_action( 'lostpassword_form', 'cptch_register_form' );
	add_action( 'lostpassword_post', 'cptch_lostpassword_post', 10, 3 );
}

// adds "Settings" link to the plugin action page
add_filter( 'plugin_action_links', 'cptch_plugin_action_links',10,2);

//Additional links on the plugin page
add_filter('plugin_row_meta', 'cptch_register_plugin_links',10,2);

function cptch_plugin_action_links( $links, $file ) {
		//Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ){
			 $settings_link = '<a href="admin.php?page=captcha.php">' . __( 'Settings', 'captcha' ) . '</a>';
			 array_unshift( $links, $settings_link );
		}
	return $links;
} // end function cptch_plugin_action_links

function cptch_register_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="admin.php?page=captcha.php">' . __( 'Settings', 'captcha' ) . '</a>';
	}
	return $links;
}

// Function for display CIPTCHA settings page in the admin area
function cptch_settings_page() {
	global $cptch_admin_fields_enable;
	global $cptch_admin_fields_actions;
	global $cptch_admin_fields_lang;
	global $cptch_options;

	$error = "";
	
	// Save data for settings page
	if( isset( $_REQUEST['cptch_form_submit'] ) ) {
		$cptch_request_options = array();

		foreach( $cptch_options as $key => $val ) {
			if( isset( $_REQUEST[$key] ) ) {
				if( $key != 'cptch_label_form' )
					$cptch_request_options[$key] = 1;
				else
					$cptch_request_options[$key] = $_REQUEST[$key];
			} else {
				if( $key != 'cptch_label_form' )
					$cptch_request_options[$key] = 0;
				else
					$cptch_request_options[$key] = "";
			}
					}

		// array merge incase this version has added new options
		$cptch_options = array_merge( $cptch_options, $cptch_request_options );

			// Update options in the database
			update_option( 'cptch_options', $cptch_request_options, '', 'yes' );
			$message = "Options saved.";
		}
	

	// Display form on the setting page
?>
<div class="wrap">
	<h2>CIPTCHA Options</h2>
	<div class="updated fade" <?php if( ! isset( $_REQUEST['cptch_form_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
	<div class="error" <?php if( "" == $error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>
	<form method="post" action="admin.php?page=captcha.php">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Enable CIPTCHA on the: </th>
				<td>
			<? foreach( $cptch_admin_fields_enable as $fields ) { ?>
					<input type="checkbox" name="<?php echo $fields[0]; ?>" value="<?php echo $fields[0]; ?>" <?php if( 1 == $cptch_options[$fields[0]] ) echo "checked=\"checked\""; ?> /><label for="<?php echo $fields[0]; ?>"><?php echo $fields[1]; ?></label><br />
			<? } ?>
			<tr valign="top">
				<th scope="row">DisplayMode</th>
				<td>
			<?php foreach($cptch_admin_fields_actions as $actions) { ?>
					<div style="float:left; width:100px;"><input type="checkbox" name="<?php echo $actions[0]; ?>" value="<?php echo $cptch_options[$actions[0]]; ?>" <?php if( 1 == $cptch_options[$actions[0]] ) echo "checked=\"checked\""; ?> /><label for="<?php echo $actions[0]; ?>"><?php echo $actions[1]; ?></label></div><br />
			<?php } ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Language</th>
				<td>
			<?php foreach($cptch_admin_fields_lang as $diff) { ?>
					<div style="float:left; width:100px;"><input type="checkbox" name="<?php echo $diff[0]; ?>" value="<?php echo $cptch_options[$diff[0]]; ?>" <?php if( 1 == $cptch_options[$diff[0]] ) echo "checked=\"checked\""; ?> /><label for="<?php echo $diff[0]; ?>"><?php echo $diff[1]; ?></label></div><img src="<?php echo plugins_url( 'images/'.$diff[0].'.jpg' , __FILE__ );?>" alt="" title="" width="" height="" /><br />
			<?php } ?>
				</td>
			</tr>
				</td>
			</tr>
		</table>    
		<input type="hidden" name="cptch_form_submit" value="submit" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
<?php } 

// this function adds CIPTCHA to the login form
function cptch_login_form() {
	session_start();
	global $cptch_options;
	
	// CIPTCHA html - login form
	echo '<p>';
	if( "" != $cptch_options['cptch_label_form'] )	
		echo '<label>'. $cptch_options['cptch_label_form'] .'</label><br />';
	if( isset( $_SESSION['cptch_error'] ) )
	{
		echo "<br />bla<span style='color:red'>". $_SESSION['cptch_error'] ."</span><br />";
		unset($_SESSION['cptch_error']);
	}
	echo '<br />';
	cptch_display_captcha();
	echo '</p>
	<br />';

} //  end function cptch_login_form


function _ciptcha_checkcode($cip_key='',$cip_code='') {
		include_once("ciptchacheck.php");
		if( ciptcha_checkcode() != "OK") {
				$_SESSION['cptch_error']='Please complete the CIPTCHA. not OK'; 
		}else{
			return true;
		}
	}

// this function checks CIPTCHA posted with a login
function cptch_login_post($errors) {
	// Delete errors, if they set
	//var_dump($_POST);
	if( isset( $_SESSION['cptch_error'] ) ){
		unset( $_SESSION['cptch_error'] );
	}
	if( $_REQUEST['action'] == 'register' ){
		return($errors);
		}
	if (empty($_POST["cip_key"])){	
		return $errors.'<strong>'. __( 'ERROR', 'cptch' ) .'</strong>: '. __( 'Please complete the CIPTCHA. no cip_key', 'cptch' );

	}
	if( _ciptcha_checkcode() != true){

	return $errors.'<strong>'. __( 'ERROR', 'cptch' ) .'</strong>: '. __( 'That CIPTCHA was incorrect. not True', 'cptch' );
		}
	return true;

} // end function cptch_login_post

// this function checks the CIPTCHA posted with a login when login errors are absent
function cptch_login_check($url) {
	if (empty($_POST["cip_key"])){	
		$_SESSION['cptch_error'] = __( 'Please complete the CIPTCHA. no cip_key 2', 'cptch' );
		return $_SERVER["REQUEST_URI"];
		}
	if( _ciptcha_checkcode() != true){
		$_SESSION['cptch_error'] = __('That CIPTCHA was incorrect. not true 2', 'cptch');
		return $_SERVER["REQUEST_URI"];
	}
		return true;

}

 // end function cptch_login_post

// this function adds CIPTCHA to the comment form
function cptch_comment_form() {
	global $cptch_options;

	// skip CIPTCHA if user is logged in and the settings allow
	if ( is_user_logged_in() && 1 == $cptch_options['cptch_hide_register'] ) {
		return true;
	}

	// CIPTCHA html - comment form
	echo '<p>';
	if( "" != $cptch_options['cptch_label_form'] )	
		echo '<label>'. $cptch_options['cptch_label_form'] .'</label>';
	echo '<br />';
	cptch_display_captcha();
	echo '</p>';

	return true;
} // end function cptch_comment_form

// this function adds CIPTCHA to the comment form
function cptch_comment_form_wp3() {
	global $cptch_options;

	// skip CIPTCHA if user is logged in and the settings allow
	if ( is_user_logged_in() && 1 == $cptch_options['cptch_hide_register'] ) {
		return true;
	}

	// CIPTCHA html - comment form
	echo '<p>';
	if( "" != $cptch_options['cptch_label_form'] )	
		echo '<label>'. $cptch_options['cptch_label_form'] .'</label>';
	echo '<br />';
	cptch_display_captcha();
	echo '</p>';

	remove_action( 'comment_form', 'cptch_comment_form' );

	
	return true;
	
	
	
} // end function cptch_comment_form


// this function checks CIPTCHA posted with the comment
function cptch_comment_post($comment) {	
	global $cptch_options;

	_ciptcha_checkcode(); {
		return $comment;
	}
    
	if ( function_exists( 'WPWall_Widget' ) && isset( $_POST['wpwall_comment'] ) ) {
			// skip CIPTCHA
			return $comment;
	}

	// skip CIPTCHA for comment replies from the admin menu
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'replyto-comment' &&
	( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false ) ) ) {
				// skip capthca
				return $comment;
	}

	// Skip CIPTCHA for trackback or pingback
	if ( $comment['comment_type'] != '' && $comment['comment_type'] != 'comment' ) {
						 // skip CIPTCHA
						 return $comment;
	}
	
} // end function cptch_comment_post

// this function adds the CIPTCHA to the register form
function cptch_register_form() {
	global $cptch_options;

	// the CIPTCHA html - register form
	echo '<p style="text-align:left;">';
	if( "" != $cptch_options['cptch_label_form'] )	
		echo '<label>'.$cptch_options['cptch_label_form'].'</label><br />';
	echo '<br />';
	cptch_display_captcha();
	echo '</p>
	<br />';

  return true;
} // end function cptch_register_form

// this function checks CIPTCHA posted with registration
function cptch_register_post($login,$email,$errors) {

	return($errors);
} // end function cptch_register_post

// this function checks the CIPTCHA posted with lostpassword form
function cptch_lostpassword_post() {
	_ciptcha_checkcode(); {
	return;
	}
} // function cptch_lostpassword_post

// Functionality of the CIPTCHA logic work
function cptch_display_captcha()
{
	global $cptch_options;
	echo "
<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js'> </script>
<!--
  <link rel=\"stylesheet\" href=\"http://cdn.ciptcha.com/flyout.css\" type=\"text/css\" />
-->
  <script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js\"></script>

  <script src=\"http://cdn.ciptcha.com/ciptcha.js\"></script>
<!--
  <link rel=\"stylesheet\" href=\"http://cdn.ciptcha.com/captcha.css\" type=\"text/css\" />
-->

<style>
.challenge-button {
    display:inline-block;
	width: auto;
    height: 20px;
    position: relative;
    overflow: hidden;
    background:#DCDCDC;
	background: -webkit-gradient(linear, left top, left bottom, from(#FAFAFA), to(#DCDCDC)); /*webkit*/
	background: -moz-linear-gradient(center top , #FAFAFA, #DCDCDC) repeat scroll 0 0 transparent;/*FF*/
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#FFFAFAFA, endColorstr=#FFDCDCDC);/*ie5-7*/ 
	-ms-filter: \"progid:DXImageTransform.Microsoft.gradient(startColorstr=#FFFAFAFA, endColorstr=#FFDCDCDC)\"; /*ie8*/
    margin-left: -1px;
    font: bold 11px Arial,Helvetica,sans-serif;
    color: #3B3B3B;
    text-decoration: none;
    border: 1px solid #ACACAC;
    border-radius: 3px 3px 3px 3px; 
    -moz-border-radius: 3px 3px 3px 3px;
    padding: 0;
	/* z-index: 50; */
    cursor:pointer;
}
.challenge-button-green {
    background:#4E7D0E;
	background: -webkit-gradient(linear, left top, left bottom, from(#7DB72F), to(#4E7D0E)); /*webkit*/
	background: -moz-linear-gradient(center top , #7DB72F, #4E7D0E) repeat scroll 0 0 transparent;/*FF*/
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#FF7DB72F, endColorstr=#FF4E7D0E);/*ie5-7*/ 
	-ms-filter: \"progid:DXImageTransform.Microsoft.gradient(startColorstr=#FF7DB72F, endColorstr=#FF4E7D0E)\"; /*ie8*/
    color: #FFFFFF;
}
.challenge-button-inst {
    overflow: hidden;
    white-space: nowrap;
    line-height: 20px;
    text-decoration: none;
    outline: medium none;
    padding: 1px 10px 1px 2px;
    margin: 2px 20px 2px 8px;
	border-right:1px solid #ACACAC;
}

.challenge-button-icon {
    background: transparent url(\"arrows.png\") no-repeat scroll -10px -136px;
    height: 4px;
    width: 7px;
    margin-top: -2px;
    position: absolute;
    right: 7px;
    top: 50%;
}
</style>

<script type=\"text/javascript\">

$(document).ready(function() {



$('#c12').ciptcha({
	displaytype:'modal',
	autoScale:true,
	fx:{open:'fadeIn',openSpeed:'3200'},
});

$.fn.onsuccesschallenge = function() {
};
$.fn.onfailurechallenge = function() {
};		
});

</script>
<br />
<span id=\"c12\" class=\"challenge-button\">
	<span class=\"challenge-button-inst\">Das hier fadet anders rein - kein titel</span>
	<span class=\"challenge-button-icon\"></span>
</span>
";
}

?>