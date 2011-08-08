<?php

function quotescollection_output_format($quotes)
{
	$display = "";

	foreach($quotes as $quote_data) {
		$quote_data = quotescollection_txtfmt($quote_data);
		$display .= "<blockquote class=\"quotescollection\" id=\"quote-".$quote_data['quote_id']."\"><p><q>".$quote_data['quote']."</q>";
		$cite = "";
		if($quote_data['author'])
			$cite = $quote_data['author'];
		if($quote_data['source']) {
			if($cite) $cite .= ", ";
			$cite .= $quote_data['source'];
		}
		if($cite) $cite = " <cite>&mdash;&nbsp;{$cite}</cite>"; 
		$display .= $cite."</p></blockquote>\n";
	}
	return $display;
}


function quotescollection_shortcodes($atts = array())
{
	extract( shortcode_atts( array(
		'limit' => 0,
		'id' => 0,
		'author' => '',
		'source' => '',
		'tags' => '',
		'orderby' => 'quote_id',
		'order' => 'ASC',
		'paging' => false,
		'limit_per_page' => 10
	), $atts ) );
	
	$condition = " WHERE public = 'yes'";
	
	if(isset($quote_id) && is_numeric($quote_id)) $id = $quote_id;
	
	if($id && is_numeric($id)) {
		$condition .= " AND quote_id = ".$id;
		
		if ($quote = quotescollection_get_quotes($condition))
			return quotescollection_output_format($quote);
		else
			return "";
	}
	
	if($author)
		$condition .= " AND author = '".$author."'";
	if($source) 
		$condition .= " AND source = '".$source."'";
	if ($tags) {
		$tags = html_entity_decode($tags);
		if(!$tags)
			break;
		$taglist = explode(',', $tags);
		$tags_condition = "";
		foreach($taglist as $tag) {
			$tag = trim($tag);
			if($tags_condition) $tags_condition .= " OR ";
			$tags_condition .= "tags = '{$tag}' OR tags LIKE '{$tag},%' OR tags LIKE '%,{$tag},%' OR tags LIKE '%,{$tag}'";
		}
		if($tags_condition) $condition .= " AND ".$tags_condition;
	}



	if($orderby == 'id' || !$orderby) $orderby = 'quote_id';
	else if($orderby == 'random' || $orderby == 'rand') {
		$orderby = 'RAND()';
		$order = '';
		$paging = false;
	};
	$order = strtoupper($order);
	if($order && $order != 'DESC')	
		$order = 'ASC';
	
	$condition .= " ORDER BY {$orderby} {$order}";
	
	if($paging == true || $paging == 1) {
	
		$num_quotes = quotescollection_count($condition);
		
		$total_pages = ceil($num_quotes / $limit_per_page);
		
		
		if(!isset($_GET['quotes_page']) || !$_GET['quotes_page'] || !is_numeric($_GET['quotes_page']))
			$page = 1;
		else
			$page = $_GET['quotes_page'];
		
		if($page > $total_pages) $page = $total_pages;
		
		if($page_nav = quotescollection_pagenav($total_pages, $page, 0, 'quotes_page'))
			$page_nav = '<div class="quotescollection_pagenav">'.$page_nav.'</div>';
			
		$start = ($page - 1) * $limit_per_page;
		
		$condition .= " LIMIT {$start}, {$limit_per_page}"; 

//		return $condition;
		
		if($quotes = quotescollection_get_quotes($condition))
			return $page_nav.quotescollection_output_format($quotes).$page_nav;
		else
			return "";
		
	}
	
	else if($limit && is_numeric($limit))
		$condition .= " LIMIT ".$limit;
	
//	return $condition;

	if($quotes = quotescollection_get_quotes($condition))
		return quotescollection_output_format($quotes);
	else
		return "";
}

add_shortcode('quotescollection', 'quotescollection_shortcodes');
add_shortcode('quotcoll', 'quotescollection_shortcodes');
add_shortcode('quotecoll', 'quotescollection_shortcodes'); // just in case, somebody misspells the shortcode




/* Backward compatibility for [quote] */



function quotescollection_displayquote($quote_id = 0)
{
	if($quote_id == 0)
		$atts = array( 'orderby' => 'random', 'limit' => 1 );
	else
		$atts = array (	'id' => $quote_id );
	
	return quotescollection_shortcodes($atts);
}


function quotescollection_displayquotes_author($author = "")
{
	return quotescollection_shortcodes(array('author'=>$author));
}


function quotescollection_displayquotes_source($source = "")
{
	return quotescollection_shortcodes(array('source'=>$source));
}

function quotescollection_displayquotes_tags($tags = "")
{
	return quotescollection_shortcodes(array('tags'=>$tags));
}

function quotescollection_inpost( $text )
{
  $start = strpos($text,"[quote|id=");
  if ($start !== FALSE) {
    $text = preg_replace( "/\[quote\|id=(\d+)\]/ie", "quotescollection_displayquote('\\1')", $text );
  }
  $start = strpos($text,"[quote|random]");
  if ($start !== FALSE) {
    $text = preg_replace( "/\[quote\|random\]/ie", "quotescollection_displayquote()", $text );
  }
  $start = strpos($text,"[quote|all]");
  if ($start !== FALSE) {
    $text = preg_replace( "/\[quote\|all\]/ie", "quotescollection_shortcodes()", $text );
  }
	$start = strpos($text,"[quote|author=");
	if($start !== FALSE) {
		$text = preg_replace("/\[quote\|author=(.{1,})?\]/ie", "quotescollection_displayquotes_author(\"\\1\")", $text);
	}
	$start = strpos($text,"[quote|source=");
	if($start !== FALSE) {
		$text = preg_replace("/\[quote\|source=(.{1,})?\]/ie", "quotescollection_displayquotes_source(\"\\1\")", $text);
	}
	$start = strpos($text,"[quote|tags=");
	if($start !== FALSE) {
		$text = preg_replace("/\[quote\|tags=(.{1,})?\]/ie", "quotescollection_displayquotes_tags(\"\\1\")", $text);
	}	return $text;
}
add_filter('the_content', 'quotescollection_inpost', 7);
add_filter('the_excerpt', 'quotescollection_inpost', 7);

?>
