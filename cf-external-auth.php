<?php
/*
Plugin Name: CF External Auth
Plugin URI: http://crowdfavorite.com
Description: A plugin that allows folders outside the WordPress structure to use WordPress' authentication Cookies along with an API call to allow WordPress users to be authenticated against outside resources.
Version: 1.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com 
*/

define('CFEA_AUTH_SALT',md5($_SERVER['SERVER_NAME']));

# Handle .htaccess auth call

	/**
	 * Handle the API call from the .htaccess file
	 *
	 * @return string
	 */
	function cfea_handle_auth_request() {
		if (!is_admin()  && isset($_REQUEST['cfea_check_user'])) {
			$func = apply_filters('cfea_handle_auth_request', 'cfea_auth_user', $_REQUEST['cfea_request_uri']);
			$ret = false;
			
			if (!empty($func)) {
				$ret = $func($_REQUEST['cfea_request_uri'], $_REQUEST['cfea_cookie_val']);
			}

			if ($ret == true) {
				echo cfea_cookie_val($_REQUEST['cfea_request_uri']);
			}
			else {
				echo 'false';
			}
			exit();
		}
	}
	add_action('init', 'cfea_handle_auth_request',999);
	
	/**
	 * Standard WordPress is_user_logged_in check
	 *
	 * @param string $request_uri 
	 * @param string $cookiestring 
	 * @return bool
	 */
	function cfea_auth_user($request_uri, $cookiestring) {
		$cookies = cfea_parse_cookies($cookiestring);
		
		// this is overly simplistic - should be more robust
		if (isset($cookies['wordpress_logged_in_'])) {
			$cookie_elements = explode('|', $cookies['wordpress_logged_in_']);
			if (count($cookie_elements) == 3) {
				$success = true;
			}
		}
		return $success;
	}
	
	/**
	 * Explode the cookiestring in to an array
	 *
	 * @param string $cookiestring 
	 * @return array
	 */
	function cfea_parse_cookies($cookiestring) {
		$cookie_temp = explode(';', $cookiestring);
		$cookies = array();
		foreach($cookie_temp as $cookie) {
			list($key, $val) = explode('=', $cookie);
			$cookies[trim($key)] = trim($val);
		}
		return $cookies;
	}
	
	function cfea_cookie_val($request_uri) {
		if (!preg_match('|^\/|',$request_uri)) {
			$request_uri = '/'.$request_uri;
		}
		if (!preg_match('|\/$|',$request_uri)) {
			$request_uri .= '/';
		}
		
		return 'cfea_auth'.CFEA_AUTH_SALT.'|'.md5('cfea_auth'.CFEA_AUTH_SALT.$request_uri);
	}

# Handle Auth Redirect

	/**
	 * When the user is denied access, the user is returned back to WordPress
	 * Give the opportunity to catch the landing 
	 *
	 * @return void
	 */
	function cfea_non_auth_redirect() {	
		// this is here purely to provide a hook on a non-authorized user landing on WordPress for authentication
		do_action('cfea_non_auth_redirect', $_REQUEST['cfea_auth']);
	}
	
	/**
	 * For parsing, we add our value to the login form so that we can use it later
	 *
	 * @return void
	 */
	function cfea_login_form_addition() {
		echo '<input type="hidden" name="cfea_auth" value="'.esc_html($_REQUEST['cfea_auth']).'" />';
	}
	
	/**
	 * On Successful login we redirect the user to the requested resource
	 *
	 * @return void
	 */
	function cfea_handle_login($user_login) {
		if (isset($_REQUEST['cfea_auth'])) {
			$redirect_url = apply_filters('cfea_login_redirect', $_REQUEST['cfea_auth'], $user_login);
			if (!empty($redirect_url)) {
				setcookie('cfea_auth', cfea_cookie_val($_REQUEST['cfea_auth']), 43200);
				wp_redirect($redirect_url);
				exit;
			}
		}
	}
	
	/**
	 * WordPress login handlers
	 */
	if (isset($_REQUEST['cfea_auth'])) {
		if ($_SERVER['SCRIPT_NAME'] == '/wp-login.php') {
			add_action('init', 'cfea_non_auth_redirect');
			add_action('login_form', 'cfea_login_form_addition');
		}
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			add_action('wp_login', 'cfea_handle_login');
		}
	}
	
# Handle Logout

	/**
	 * Kill the auth cookie when the user logs out
	 *
	 * @return void
	 */
	function cfea_handle_logout() {
		setcookie('cfea_auth', '', time() - 3600, '/', $_SERVER['SERVER_NAME']);
	}
	add_action('wp_logout', 'cfea_handle_logout');
?>