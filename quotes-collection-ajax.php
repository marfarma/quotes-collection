<?php

if(isset($_REQUEST['refresh'])) {
	$blogdir = preg_replace('|/wp-content.*$|','', __FILE__);
	include_once($blogdir.'/wp-config.php');
	include_once($blogdir.'/wp-includes/wp-db.php');
	include_once(str_replace("-ajax", "", __FILE__));
	if($random_quote = quotescollection_get_randomquote($_REQUEST['exclude'])) {
		$show_author = isset($_REQUEST['show_author'])?$_REQUEST['show_author']:1;
		$show_source = isset($_REQUEST['show_source'])?$_REQUEST['show_source']:1;
		$display = quotescollection_display_randomquote($show_author, $show_source, 2, $random_quote);
		@header("Content-type: text/javascript; charset=utf-8");
		die( "document.getElementById('quotescollection_randomquote-".$_REQUEST['refresh']."').innerHTML = '".$display."'" ); 
	}
	else
		die( "alert('$error')" );
}

if(isset($_REQUEST['js'])) {
?>
function quotescollection_refresh(instance, exclude, show_author, show_source)
{
    // function body defined below
	var mysack = new sack( 
       "<?php echo $_SERVER['PHP_SELF']; ?>?refresh="+instance+"&exclude="+exclude+"&show_author="+show_author+"&show_source="+show_source );    
	mysack.execute = 1;
	
	mysack.onError = function() { document.getElementById('quotescollection_randomquote-'+instance).innerHTML = quotcoll_error; };
	mysack.onLoading = function() { document.getElementById('quotescollection_nextquote-'+instance).innerHTML = quotcoll_loading; };
	mysack.onLoaded = function() { document.getElementById('quotescollection_nextquote-'+instance).innerHTML = '<a style="cursor:pointer" onclick="quotescollection_refresh('+instance+','+exclude+','+show_author+','+show_source+');">' + quotcoll_nextquote + ' &raquo</a>'; };
//	mysack.onInteractive = function() { document.getElementById('quotescollection_nextquote-'+instance).innerHTML += '...'; };
//	mysack.onCompletion = function() { document.getElementById('quotescollection_randomquote-'+instance).innerHTML = mysack.response; };
	mysack.runAJAX();
	return true;
} // end of JavaScript function for randomquote
<?php 
}
?>
