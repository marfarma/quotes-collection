<?php
// If you have your 'wp-content' directory in a place other than the default location, please specify your blog directory here. This is not your blog url. It is the address in your server. For example: '/public_html/myblog'
$blogdir = ""; 

if(isset($_POST['refresh'])) {

	if (!$blogdir) {
		$blogdir = preg_replace('|/wp-content.*$|','', __FILE__);
	}
	if($blogdir == __FILE__) {
		$blogdir = preg_replace('|\wp-content.*$|','', __FILE__);
		include_once($blogdir.'\wp-config.php');
		include_once($blogdir.'\wp-includes\wp-db.php');
	}
	else {
		include_once($blogdir.'/wp-config.php');
		include_once($blogdir.'/wp-includes/wp-db.php');
	}
	include_once(str_replace("-ajax", "", __FILE__));
	$show_author = isset($_POST['show_author'])?$_POST['show_author']:1;
	$show_source = isset($_POST['show_source'])?$_POST['show_source']:1;
	$char_limit = (isset($_POST['char_limit']) && is_numeric($_POST['char_limit']))?$_POST['char_limit']:'';
	
	
	if($_POST['exclude'] && is_numeric($_POST['exclude']))
		$exclude = $_POST['exclude'];
	else $exclude = '';
		
	$tags = $_POST['tags'];
	
	$args = "echo=0&ajax_refresh=2&show_author={$show_author}&show_source={$show_source}&char_limit={$char_limit}&exclude={$exclude}&tags={$tags}";
		

	if($response = quotescollection_quote($args)) {
		@header("Content-type: text/javascript; charset=utf-8");
		die( $response ); 
	}
	else
		die( $error );
}

?>
