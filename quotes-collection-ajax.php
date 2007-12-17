<?php

if(isset($_REQUEST['refresh'])) {
	$blogdir = preg_replace('|/wp-content.*$|','', __FILE__);
	include_once($blogdir.'/wp-config.php');
	include_once($blogdir.'/wp-includes/wp-db.php');
	include_once(str_replace("-ajax", "", __FILE__));
	if($random_quote = quotescollection_randomquote()) {
		$display .= "<p>";
		$display .= "<q>".wptexturize($random_quote['quote'])."</q>";
		$options = get_option('quotescollection');
		$show_author = isset($options['show_author'])?$options['show_author']:0;
		$show_source = isset($options['show_source'])?$options['show_source']:1;
		if( ($show_author && $random_quote['author']) || ($show_source && $random_quote['source']) )
			$display .= " &mdash;&nbsp;";
		if($show_author && $random_quote['author'])
			$display .= "<cite>".$random_quote['author']."</cite> ";
		if($show_source && $random_quote['source'])
			$display .= "from <cite>".$random_quote['source']."</cite>";
		$display .= "</p>";
		$display .= "<p id=\"quotescollection_nextquote\"><a style=\"cursor:pointer\" onclick=\"quotescollection_refresh();\">Next quote »</a></p>";
		die( "document.getElementById('quotescollection_randomquote').innerHTML = '".$display."'" ); 
	}
	else
		 die( "alert('$error')" );
}

if(isset($_REQUEST['js'])) {
?>
function quotescollection_refresh()
{
    // function body defined below
	var mysack = new sack( 
       "<?php echo $_SERVER['PHP_SELF']; ?>?refresh" );    
	mysack.execute = 1;
	mysack.onError = function() { document.getElementById('quotescollection_randomquote').innerHTML = 'Error getting quote'; };
	mysack.onLoading = function() { document.getElementById('quotescollection_nextquote').innerHTML = 'Loading...'; };
	mysack.onLoaded = function() { document.getElementById('quotescollection_nextquote').innerHTML += '...'; };
	mysack.onInteractive = function() { document.getElementById('quotescollection_nextquote').innerHTML += '...'; };
//	mysack.onCompletion = function() { document.getElementById('quotescollection_randomquote').innerHTML = mysack.response; };
	mysack.runAJAX();
	return true;
} // end of JavaScript function for randomquote
<?php 
}
?>
