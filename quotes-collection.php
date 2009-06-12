<?php
/*
Plugin Name: Quotes Collection
Plugin URI: http://srinig.com/wordpress/plugins/quotes-collection/
Description: Quotes Collection plugin with Ajax powered Random Quote sidebar widget helps you collect and display your favourite quotes on your WordPress blog.
Author: Srini G
Version: 1.3.2
Author URI: http://srinig.com/wordpress/
*/
/*  Released under GPL:
	http://www.opensource.org/licenses/gpl-license.php
*/



$quotescollection_admin_userlevel = 2; 
// Refer http://codex.wordpress.org/Roles_and_Capabilities


$quotescollection_db_version = '1.1'; 

function quotescollection_get_randomquote($exclude = 0)
{
	if($exclude) $condition = "quote_id <> ".$exclude;
	else $condition = "";
	return quotescollection_get_quote($condition);
}

function quotescollection_get_quote($condition = '')
{
	global $wpdb;
	$sql = "SELECT quote_id, quote, author, source
		FROM " . $wpdb->prefix . "quotescollection";
	if ($condition)
		$sql .= " WHERE ".$condition;
	$sql .= " ORDER BY RAND() LIMIT 1";
	$random_quote = $wpdb->get_row($sql, ARRAY_A);
	if ( !empty($random_quote) ) {
		return $random_quote;
	}
	else
		return 0;
}


function quotescollection_count($condition = "")
{
	global $wpdb;
	if($condition) $condition = " WHERE ".$condition;
	$sql = "SELECT COUNT(*) FROM " . $wpdb->prefix . "quotescollection ".$condition;
	$count = $wpdb->get_var($sql);
	return $count;
}

function quotescollection_js_head()
{
	if ( !defined('WP_PLUGIN_URL') )
		$wp_plugin_url = get_bloginfo( 'url' )."/wp-content/plugins";
	else
		$wp_plugin_url = WP_PLUGIN_URL;
	$requrl = $wp_plugin_url . "/quotes-collection/quotes-collection-ajax.php";
	$nextquote =  __('Next quote', 'quotes-collection');
	$loading = __('Loading...', 'quotes-collection');
	$error = __('Error getting quote', 'quotes-collection');

	?>
<!-- Quotes Collection -->
<script type="text/javascript" src="<?php echo $wp_plugin_url; ?>/quotes-collection/quotes-collection.js"></script>
<script type="text/javascript">
  quotescollection_init(<?php echo "'{$requrl}', '{$nextquote}', '{$loading}', '{$error}'"; ?>);
</script>
<?php
}
add_action('wp_head', 'quotescollection_js_head' );


function quotescollection_enqueue()
{
	wp_enqueue_script('jquery');
}
add_action('init', 'quotescollection_enqueue');


function quotescollection_txtfmt($quotedata = array())
{
	if(!$quotedata)
		return;

	foreach($quotedata as $key => $value){
		$value = wptexturize(str_replace(array("\r\n", "\r", "\n"), '', nl2br(trim($value))));
		$value = ereg_replace("[[:space:]][[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]"," <a href=\"\\0\">\\0</a>", $value); 
		$value = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/][[:space:]]","<a href=\"\\0\">\\0</a> ", $value); 
		$quotedata[$key] = $value;
	}
	
	return $quotedata;	
}


function quotescollection_display_randomquote($show_author = 1, $show_source = 1, $ajax_refresh = 1, $random_quote = array()) 
{
	$args = "show_author={$show_author}&show_source={$show_source}&ajax_refresh={$ajax_refresh}&char_limit={$char_limit}&echo=1";
	return quotescollection_quote($args);
}


function quotescollection_quote($args = '') 
{
	global $quotescollection_instances;
	if(!($instance = $quotescollection_instances))
		$instance = $quotescollection_instances = 0;
	
		$key_value = explode('&', $args);
	$options = array();
	foreach($key_value as $value) {
		$x = explode('=', $value);
		$options[$x[0]] = $x[1]; // $options['key'] = 'value';
	}
	
	$options_default = array(
		'show_author' => 1,
		'show_source' => 1,
		'ajax_refresh' => 1,
		'tags' => '',
		'char_limit' => 500,
		'echo' => 1,
		'order' => 'random',
		'exclude' => ''
	);
	
	$options = array_merge($options_default, $options);
	
	$condition = "visible = 'yes'";
	
	if($options['char_limit'] && is_numeric($options['char_limit']))
		$condition .= " AND CHAR_LENGTH(quote) <= ".$options['char_limit'];
	
	else $options['char_limit'] = 0;
	
	if($options['exclude'])
		$condition .=" AND quote_id <> ".$options['exclude'];
		
	if($options['tags']) {
		$taglist = explode(',', $options['tags']);
		foreach($taglist as $tag) {
			$tag = mysql_real_escape_string(strip_tags(trim($tag)));
			if($tag_condition) $tag_condition .= " OR ";
			$tag_condition .= "tags = '{$tag}' OR tags LIKE '{$tag},%' OR tags LIKE '%,{$tag},%' OR tags LIKE '%,{$tag}'";
		}
		$condition .= " AND ({$tag_condition})";
	}
	$random_quote = quotescollection_get_quote($condition);

	if(!$random_quote)
		return;
	
	$random_quote  = quotescollection_txtfmt($random_quote);
				
	$display = "<p><q>". $random_quote['quote'] ."</q>";
	if($options['show_author'] && $random_quote['author'])
		$cite = '<span class="quotescollection_author">'. $random_quote['author'] .'</span>';

	if($options['show_source'] && $random_quote['source']) {
		if($cite) $cite .= ", ";
			$cite .= '<span class="quotescollection_source">'. $random_quote['source'] .'</span>';
	}
	if($cite) $cite = " <cite>&mdash;&nbsp;{$cite}</cite>";
	$display .= $cite."</p>";
	
	// We don't want to display the 'next quote' link if there is no more than 1 quote
	$quotes_count = quotescollection_count($condition); 
	
	
	if($options['ajax_refresh'] == 1 && $quotes_count > 1) {
		$display .= "<script type=\"text/javascript\">\n<!--\ndocument.write(\"";
		$display .= '<p class=\"quotescollection_nextquote\" id=\"quotescollection_nextquote-'.$instance.'\"><a class=\"quotescollection_refresh\" style=\"cursor:pointer\" onclick=\"quotescollection_refresh('.$instance.', '.$random_quote["quote_id"].', '. $options['show_author'] .', '.$options['show_source'].', \''.$options['tags'].'\', '.$options['char_limit'].');\">'.__('Next quote', 'quotes-collection').' &raquo;<\/a><\/p>';
		$display .= "\")\n//-->\n</script>\n";
	}
	if ($options['ajax_refresh'] == 2 && $quotes_count) {
		$display .= "<p class=\"quotescollection_nextquote\" id=\"quotescollection_nextquote-".$_REQUEST['refresh']."\"><a class=\"quotescollection_refresh\" style=\"cursor:pointer\" onclick=\"quotescollection_refresh(".$_REQUEST['refresh'].", ".$random_quote['quote_id'].', '. $options['show_author'] .', '.$options['show_source'].', \''.$options['tags'].'\', '.$options['char_limit'].");\">".__('Next quote', 'quotes-collection')." &raquo;</a></p>";
		return $display;
	}
	$display = "<div id=\"quotescollection_randomquote-".$instance."\" class=\"quotescollection_randomquote\">{$display}</div>";
	$quotescollection_instances++;
	if($options['echo'])
		echo $display;
	else
		return $display;
}

function quotescollection_init()
{
	if(function_exists('load_plugin_textdomain'))
		load_plugin_textdomain('quotes-collection', 'wp-content/plugins/quotes-collection/languages/');
	
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
	
	function quotescollection_widget($args) {
		$options = get_option('quotescollection');
		$title = isset($options['title'])?apply_filters('the_title', $options['title']):__('Random Quote', 'quotes-collection');
		$show_author = isset($options['show_author'])?$options['show_author']:1;
		$show_source = isset($options['show_source'])?$options['show_source']:1;
		$ajax_refresh = isset($options['ajax_refresh'])?$options['ajax_refresh']:1;
		$char_limit = $options['char_limit'];
		$tags = $options['tags'];
		$parms = "echo=0&show_author={$show_author}&show_source={$show_source}&ajax_refresh={$ajax_refresh}&char_limit={$char_limit}&tags={$tags}";
		if($random_quote = quotescollection_quote($parms)) {
			extract($args);
			echo $before_widget;
			if($title) echo $before_title . $title . $after_title . "\n";
			echo $random_quote;
			echo $after_widget;
		}
	}
	
	function quotescollection_widget_control()
	{
		
		// default values for options
		$options = array(
			'title' => __('Random Quote', 'quotes-collection'), 
			'show_author' => 1,
			'show_source' => 0, 
			'ajax_refresh' => 1,
			'tags' => '',
			'char_limit' => 500
		);

		if($options_saved = get_option('quotescollection'))
			$options = array_merge($options, $options_saved);
			
		// Update options in db when user updates options in the widget page
		if($_REQUEST['quotescollection-submit']) { 
			$options['title'] 
				= strip_tags(stripslashes($_REQUEST['quotescollection-title']));
			$options['show_author'] = $_REQUEST['quotescollection-show_author']?1:0;
			$options['show_source'] = $_REQUEST['quotescollection-show_source']?1:0;
			$options['ajax_refresh'] = $_REQUEST['quotescollection-ajax_refresh']?1:0;
			$options['tags'] = strip_tags(stripslashes($_REQUEST['quotescollection-tags']));
			$options['char_limit'] = strip_tags(stripslashes($_REQUEST['quotescollection-char_limit']));
			if(!$options['char_limit'])
				$options['char_limit'] = __('none', 'quotes-collection');
			update_option('quotescollection', $options);
		}
		
		// Now we define the display of widget options menu
        if($options['show_author'])
        	$show_author_checked = ' checked="checked"';
        if($options['show_source'])
        	$show_source_checked = ' checked="checked"';
        if($options['ajax_refresh'])
        	$ajax_refresh_checked = ' checked="checked"';
		echo "<p style=\"text-align:left;\"><label for=\"quotescollection-title\">".__('Title', 'quotes-collection')." </label><input class=\"widefat\" type=\"text\" id=\"quotescollection-title\" name=\"quotescollection-title\" value=\"".htmlspecialchars($options['title'], ENT_QUOTES)."\" /></p>";
		echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"quotescollection-show_author\" name=\"quotescollection-show_author\" value=\"1\"{$show_author_checked} /> <label for=\"quotescollection-show_author\">".__('Show author?', 'quotes-collection')."</label></p>";
		echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"quotescollection-show_source\" name=\"quotescollection-show_source\" value=\"1\"{$show_source_checked} /> <label for=\"quotescollection-show_source\">".__('Show source?', 'quotes-collection')."</label></p>";
		echo "<p style=\"text-align:left;\"><input type=\"checkbox\" id=\"quotescollection-ajax_refresh\" name=\"quotescollection-ajax_refresh\" value=\"1\"{$ajax_refresh_checked} /> <label for=\"quotescollection-ajax_refresh\">".__('Ajax refresh feature', 'quotes-collection')."</label></p>";
		echo "<p style=\"text-align:left;\"><label for=\"quotescollection-tags\">".__('Tags filter', 'quotes-collection')." </label><input class=\"widefat\" type=\"text\" id=\"quotescollection-tags\" name=\"quotescollection-tags\" value=\"".htmlspecialchars($options['tags'], ENT_QUOTES)."\" /><br/><span class=\"setting-description\">".__('Comma separated', 'quotes-collection')."</span></p>";
		echo "<p style=\"text-align:left;\"><label for=\"quotescollection-char_limit\">".__('Character limit', 'quotes-collection')." </label><input class=\"widefat\" type=\"text\" id=\"quotescollection-char_limit\" name=\"quotescollection-char_limit\" value=\"".htmlspecialchars($options['char_limit'], ENT_QUOTES)."\" /></p>";
		echo "<input type=\"hidden\" id=\"quotescollection-submit\" name=\"quotescollection-submit\" value=\"1\" />";
		echo "<p style=\"text-align:left;\">"."<a href=\"edit.php?page=quotes-collection/quotes-collection.php\">".__('Click here', 'quotes-collection')."</a> ".__('to manage your collection of quotes', 'quotes-collection').".</p>";
	}


	register_sidebar_widget(array('Random Quote', 'widgets'), 'quotescollection_widget');
	register_widget_control('Random Quote', 'quotescollection_widget_control', 250, 350);
}


function quotescollection_admin_menu() 
{
	global $quotescollection_admin_userlevel;
	add_management_page('Quotes Collection', 'Quotes Collection', $quotescollection_admin_userlevel, __FILE__, 'quotescollection_quotes_management');
}

function quotescollection_addquote($quote, $author = "", $source = "", $tags = "", $visible = 'yes')
{
	if(!$quote) return __('Nothing added to the database.', 'quotes-collection');
	global $wpdb;
	$table_name = $wpdb->prefix . "quotescollection";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
		return __('Database table not found', 'quotes-collection');
	else //Add the quote data to the database
	{
		
		if ( ini_get('magic_quotes_gpc') ) {
		  $quote = stripslashes($quote);
		  $author = stripslashes($author);	
		  $source = stripslashes($source);	
		  $tags = stripslashes($tags);
	  	}
		$quote = "'".$wpdb->escape($quote)."'";
		$author = $author?"'".$wpdb->escape($author)."'":"NULL";
		$source = $source?"'".$wpdb->escape($source)."'":"NULL";
		$tags = explode(',', $tags);
		foreach ($tags as $key => $tag)
			$tags[$key] = trim($tag);
		$tags = implode(',', $tags);
		$tags = $tags?"'".$wpdb->escape($tags)."'":"NULL";
		if(!$visible) $visible = "'no'";
		else $visible = "'yes'";
		$insert = "INSERT INTO " . $table_name .
			"(quote, author, source, tags, visible, time_added)" .
			"VALUES ({$quote}, {$author}, {$source}, {$tags}, {$visible}, NOW())";
		$results = $wpdb->query( $insert );
		if(FALSE === $results)
			return __('There was an error in the MySQL query', 'quotes-collection');
		else
			return __('Quote added', 'quotes-collection');
   }
}

function quotescollection_editquote($quote_id, $quote, $author = "", $source = "", $tags = "", $visible = 'yes')
{
	if(!$quote) return __('Quote not updated.', 'quotes-collection');
	if(!$quote_id) return srgq_addquote($quote, $author, $source, $visible);
	global $wpdb;
	$table_name = $wpdb->prefix . "quotescollection";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
		return __('Database table not found', 'quotes-collection');
	else //Update database
	{
		
		if ( ini_get('magic_quotes_gpc') ) {
		  $quote = stripslashes($quote);
		  $author = stripslashes($author);	
		  $source = stripslashes($source);	
		  $tags = stripslashes($tags);
	  	}
	  	$quote = "'".$wpdb->escape($quote)."'";
		$author = $author?"'".$wpdb->escape($author)."'":"NULL";
		$source = $source?"'".$wpdb->escape($source)."'":"NULL";
		$tags = explode(',', $tags);
		foreach ($tags as $key => $tag)
			$tags[$key] = trim($tag);
		$tags = implode(',', $tags);
		$tags = $tags?"'".$wpdb->escape($tags)."'":"NULL";
		if(!$visible) $visible = "'no'";
		else $visible = "'yes'";
		$update = "UPDATE " . $table_name . "
			SET quote = {$quote},
				author = {$author},
				source = {$source}, 
				tags = {$tags},
				visible = {$visible}, 
				time_updated = NOW()
			WHERE quote_id = $quote_id";
		$results = $wpdb->query( $update );
		if(FALSE === $results)
			return __('There was an error in the MySQL query', 'quotes-collection');		
		else
			return __('Changes saved', 'quotes-collection');
   }
}


function quotescollection_deletequote($quote_id)
{
	if($quote_id) {
		global $wpdb;
		$sql = "DELETE from " . $wpdb->prefix ."quotescollection" .
			" WHERE quote_id = " . $quote_id;
		if(FALSE === $wpdb->query($sql))
			return __('There was an error in the MySQL query', 'quotes-collection');		
		else
			return __('Quote deleted', 'quotes-collection');
	}
	else return __('The quote cannot be deleted', 'quotes-collection');
}

function quotescollection_getquotedata($quote_id)
{
	global $wpdb;
	$sql = "SELECT quote_id, quote, author, source, tags, visible
		FROM " . $wpdb->prefix . "quotescollection 
		WHERE quote_id = {$quote_id}";
	$quote_data = $wpdb->get_row($sql, ARRAY_A);	
	return $quote_data;
}

function quotescollection_editform($quote_id = 0)
{
	$visible_selected = " checked=\"checked\"";
	$submit_value = __('Add Quote', 'quotes-collection');
	$form_name = "addquote";
	$action_url = $_SERVER['PHP_SELF']."?page=quotes-collection/quotes-collection.php#addnew";

	if($quote_id) {
		$form_name = "editquote";
		$quote_data = quotescollection_getquotedata($quote_id);
		foreach($quote_data as $key => $value)
			$quote_data[$key] = $quote_data[$key];
		extract($quote_data);
		$quote = htmlspecialchars($quote);
		$author = htmlspecialchars($author);
		$source = htmlspecialchars($source);
		$tags = implode(', ', explode(',', $tags));
		$hidden_input = "<input type=\"hidden\" name=\"quote_id\" value=\"{$quote_id}\" />";
		if($visible == 'no') $visible_selected = "";
		$submit_value = __('Save changes', 'quotes-collection');
		$back = "<input type=\"submit\" name=\"submit\" value=\"".__('Back', 'quotes-collection')."\" />&nbsp;";
		$action_url = $_SERVER['PHP_SELF']."?page=quotes-collection/quotes-collection.php";
	}

	$quote_label = __('The quote', 'quotes-collection');
	$author_label = __('Author', 'quotes-collection');
	$source_label = __('Source', 'quotes-collection');
	$tags_label = __('Tags', 'quotes-collection');
	$visible_label = __('Visible?', 'quotes-collection');
	$optional_text = __('optional', 'quotes-collection');
	$comma_separated_text = __('comma separated', 'quotes-collection');
	

	$display .=<<< EDITFORM
<form name="{$form_name}" method="post" action="{$action_url}">
	{$hidden_input}
	<table class="form-table" cellpadding="5" cellspacing="2" width="100%">
		<tbody><tr class="form-field form-required">
			<th style="text-align:left;" scope="row" valign="top"><label for="quotescollection_quote">{$quote_label}</label></th>
			<td><textarea id="quotescollection_quote" name="quote" rows="5" cols="50" style="width: 97%;">{$quote}</textarea></td>
		</tr>
		<tr class="form-field">
			<th style="text-align:left;" scope="row" valign="top"><label for="quotescollection_author">{$author_label}</label></th>
			<td><input type="text" id="quotescollection_author" name="author" size="40" value="{$author}" /><br />{$optional_text}</td>
		</tr>
		<tr class="form-field">
			<th style="text-align:left;" scope="row" valign="top"><label for="quotescollection_source">{$source_label}</label></th>
			<td><input type="text" id="quotescollection_source" name="source" size="40" value="{$source}" /><br />{$optional_text}</td>
		</tr>
		<tr class="form-field">
			<th style="text-align:left;" scope="row" valign="top"><label for="quotescollection_tags">{$tags_label}</label></th>
			<td><input type="text" id="quotescollection_tags" name="tags" size="40" value="{$tags}" /><br />{$optional_text}, {$comma_separated_text}</small></td>
		</tr>
		<tr>
			<th style="text-align:left;" scope="row" valign="top"><label for="quotescollection_visible">{$visible_label}</label></th>
			<td><input type="checkbox" id="quotescollection_visible" name="visible"{$visible_selected} />
		</tr></tbody>
	</table>
	<p class="submit">{$back}<input name="submit" value="{$submit_value}" type="submit" class="button button-primary" /></p>
</form>
EDITFORM;
	return $display;
}

function quotescollection_changevisibility($quote_ids, $visibility = 'yes')
{
	if(!$quote_ids)
		return __('Nothing done!', 'quotes-collection');
	global $wpdb;
	$sql = "UPDATE ".$wpdb->prefix."quotescollection 
		SET visible = '".$visibility."',
			time_updated = NOW()
		WHERE quote_id IN (".implode(', ', $quote_ids).")";
	$wpdb->query($sql);
	return sprintf(__("Visibility status of selected quotes set to '%s'", 'quotes-collection'), $visibility);
}

function quotescollection_bulkdelete($quote_ids)
{
	if(!$quote_ids)
		return __('Nothing done!', 'quotes-collection');
	global $wpdb;
	$sql = "DELETE FROM ".$wpdb->prefix."quotescollection 
		WHERE quote_id IN (".implode(', ', $quote_ids).")";
	$wpdb->query($sql);
	return __('Quote(s) deleted', 'quotes-collection');
}



function quotescollection_quotes_management()
{	
	global $quotescollection_db_version;
	$options = get_option('quotescollection');
	if($options['db_version'] != $quotescollection_db_version )
		quotescollection_install();
		
	if($_REQUEST['submit'] == __('Add Quote', 'quotes-collection')) {
		extract($_REQUEST);
		$msg = quotescollection_addquote($quote, $author, $source, $tags, $visible);
	}
	else if($_REQUEST['submit'] == __('Save changes', 'quotes-collection')) {
		extract($_REQUEST);
		$msg = quotescollection_editquote($quote_id, $quote, $author, $source, $tags, $visible);
	}
	else if($_REQUEST['action'] == 'editquote') {
		$display .= "<div class=\"wrap\">\n<h2>Quotes Collection &raquo; ".__('Edit quote', 'quotes-collection')."</h2>";
		$display .=  quotescollection_editform($_REQUEST['id']);
		$display .= "</div>";
		echo $display;
		return;
	}
	else if($_REQUEST['action'] == 'delquote') {
		$msg = quotescollection_deletequote($_REQUEST['id']);
	}
	else if(isset($_REQUEST['bulkaction']))  {
		if($_REQUEST['bulkaction'] == __('Delete', 'quotes-collection')) 
			$msg = quotescollection_bulkdelete($_REQUEST['bulkcheck']);
		if($_REQUEST['bulkaction'] == __('Make visible', 'quotes-collection')) {
			$msg = quotescollection_changevisibility($_REQUEST['bulkcheck'], 'yes');
		}
		if($_REQUEST['bulkaction'] == __('Make invisible', 'quotes-collection')) {
			$msg = quotescollection_changevisibility($_REQUEST['bulkcheck'], 'no');
		}
	}
	
	$display .= "<div class=\"wrap\">";
	
	if($msg)
		$display .= "<div id=\"message\" class=\"updated fade\"><p>{$msg}</p></div>";

	$display .= "<h2>Quotes Collection</h2>";


	// Get all the quotes from the database
	global $wpdb;

	$sql = "SELECT quote_id, quote, author, source, tags, visible
		FROM " . $wpdb->prefix . "quotescollection";
	
	if(isset($_REQUEST['orderby'])) {
		$sql .= " ORDER BY " . $_REQUEST['criteria'] . " " . $_REQUEST['order'];
		$option_selected[$_REQUEST['criteria']] = " selected=\"selected\"";
		$option_selected[$_REQUEST['order']] = " selected=\"selected\"";
	}
	else {
		$sql .= " ORDER BY quote_id ASC";
		$option_selected['quote_id'] = " selected=\"selected\"";
		$option_selected['ASC'] = " selected=\"selected\"";
	}

	$quotes = $wpdb->get_results($sql);
	
	foreach($quotes as $quote_data) {
		if($alternate) $alternate = "";
		else $alternate = " class=\"alternate\"";
		$quotes_list .= "<tr{$alternate}>";
		$quotes_list .= "<th scope=\"row\" class=\"check-column\"><input type=\"checkbox\" name=\"bulkcheck[]\" value=\"".$quote_data->quote_id."\" /></th>";
		$quotes_list .= "<td>" . $quote_data->quote_id . "</td>";
		$quotes_list .= "<td>" . wptexturize(nl2br($quote_data->quote)) ."</td>";
		$quotes_list .= "<td>" . $quote_data->author;
		if($quote_data->author && $quote_data->source)
			$quotes_list .= " / ";
		$quotes_list .= $quote_data->source ."</td>";
		$quotes_list .= "<td>" . implode(', ', explode(',', $quote_data->tags)) . "</td>";
		$quotes_list .= "<td>" . $quote_data->visible ."</td>";
		$quotes_list .= "<td><a href=\"" . $_SERVER['PHP_SELF'] . "?page=quotes-collection/quotes-collection.php&action=editquote&amp;id=".$quote_data->quote_id."\" class=\"edit\">".__('Edit', 'quotes-collection')."</a></td>
    <td><a href=\"" . $_SERVER['PHP_SELF'] . "?page=quotes-collection/quotes-collection.php&action=delquote&amp;id=".$quote_data->quote_id."\" onclick=\"return confirm( '".__('Are you sure you want to delete this quote?', 'quotes-collection')."');\" class=\"delete\">".__('Delete', 'quotes-collection')."</a> </td>";
		$quotes_list .= "</tr>";
	}
	
	if($quotes_list) {
		$display .= "<p>";
	$quotes_count = quotescollection_count();
	$display .= sprintf(__ngettext('Currently, you have %d quote.', 'Currently, you have %d quotes.', $quotes_count, 'quotes-collection'), $quotes_count);
	// anchor to add new quote
	$display .= " (<a href=\"#addnew\"><strong>".__('Add new quote', 'quotes-collection')."</strong></a>)";
	$display .= "</p>";

		$display .= "<form id=\"quotescollection\" method=\"post\" action=\"{$_SERVER['PHP_SELF']}?page=quotes-collection/quotes-collection.php\">";
		$display .= "<div class=\"tablenav\">";
		$display .= "<div class=\"alignleft actions\">";
		$display .= "<input type=\"submit\" name=\"bulkaction\" value=\"".__('Delete', 'quotes-collection')."\" class=\"button-secondary\" />";
		$display .= "<input type=\"submit\" name=\"bulkaction\" value=\"".__('Make visible', 'quotes-collection')."\" class=\"button-secondary\" />";
		$display .= "<input type=\"submit\" name=\"bulkaction\" value=\"".__('Make invisible', 'quotes-collection')."\" class=\"button-secondary\" />";
		$display .= "&nbsp;&nbsp;&nbsp;";
		$display .= __('Sort by: ', 'quotes-collection');
		$display .= "<select name=\"criteria\">";
		$display .= "<option value=\"quote_id\"{$option_selected['quote_id']}>".__('Quote', 'quotes-collection')." ID</option>";
		$display .= "<option value=\"quote\"{$option_selected['quote']}>".__('Quote', 'quotes-collection')."</option>";
		$display .= "<option value=\"author\"{$option_selected['author']}>".__('Author', 'quotes-collection')."</option>";
		$display .= "<option value=\"source\"{$option_selected['source']}>".__('Source', 'quotes-collection')."</option>";
		$display .= "<option value=\"time_added\"{$option_selected['time_added']}>".__('Date added', 'quotes-collection')."</option>";
		$display .= "<option value=\"time_updated\"{$option_selected['time_updated']}>".__('Date updated', 'quotes-collection')."</option>";
		$display .= "<option value=\"visible\"{$option_selected['visible']}>".__('Visibility', 'quotes-collection')."</option>";
		$display .= "</select>";
		$display .= "<select name=\"order\"><option{$option_selected['ASC']}>ASC</option><option{$option_selected['DESC']}>DESC</option></select>";
		$display .= "<input type=\"submit\" name=\"orderby\" value=\"".__('Go', 'quotes-collection')."\" class=\"button-secondary\" />";
		$display .= "</div>";
		$display .= "<div class=\"clear\"></div>";	
		$display .= "</div>";
		

		
		$display .= "<table class=\"widefat\">";
		$display .= "<thead><tr>
			<th class=\"check-column\"><input type=\"checkbox\" onclick=\"quotescollection_checkAll(document.getElementById('quotescollection'));\" /></th>
			<th>ID</th><th>".__('The quote', 'quotes-collection')."</th>
			<th>
				".__('Author', 'quotes-collection')." / ".__('Source', 'quotes-collection')."
			</th>
			<th>".__('Tags', 'quotes-collection')."</th>
			<th>".__('Visible?', 'quotes-collection')."</th>
			<th colspan=\"2\" style=\"text-align:center\">".__('Action', 'quotes-collection')."</th>
		</tr></thead>";
		$display .= "<tbody id=\"the-list\">{$quotes_list}</tbody>";
		$display .= "</table>";


		$display .= "<div class=\"tablenav\">";
		$display .= "<div class=\"alignleft actions\">";
		$display .= "<input type=\"submit\" name=\"bulkaction\" value=\"".__('Delete', 'quotes-collection')."\" class=\"button-secondary\" />";
		$display .= "<input type=\"submit\" name=\"bulkaction\" value=\"".__('Make visible', 'quotes-collection')."\" class=\"button-secondary\" />";
		$display .= "<input type=\"submit\" name=\"bulkaction\" value=\"".__('Make invisible', 'quotes-collection')."\" class=\"button-secondary\" />";
		$display .= "</div>";

		$display .= "</div>";
		$display .= "</form>";
		$display .= "<br style=\"clear:both;\" />";

	}
	else
		$display .= "<p>".__('No quotes in the database', 'quotes-collection')."</p>";



	$display .= "</div>";
	
	$display .= "<div id=\"addnew\" class=\"wrap\">\n<h2>".__('Add new quote', 'quotes-collection')."</h2>";
	$display .= quotescollection_editform();
	$display .= "</div>";

	
	echo $display;

}

function quotescollection_admin_head()
{
	?>
<script type="text/javascript">
function quotescollection_checkAll(form) {
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox" && !(form.elements[i].hasAttribute('onclick'))) {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
		}
	}
}
</script>
	<?php
}

add_action('admin_head', 'quotescollection_admin_head');

function quotescollection_install()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "quotescollection";

	if(!defined('DB_CHARSET') || !($db_charset = DB_CHARSET))
		$db_charset = 'utf8';
	$db_charset = "CHARACTER SET ".$db_charset;
	if(defined('DB_COLLATE') && $db_collate = DB_COLLATE) 
		$db_collate = "COLLATE ".$db_collate;


	// if table name already exists
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
   		$wpdb->query("ALTER TABLE `{$table_name}` {$db_charset} {$db_collate}");

   		$wpdb->query("ALTER TABLE `{$table_name}` MODIFY quote TEXT {$db_charset} {$db_collate}");

   		$wpdb->query("ALTER TABLE `{$table_name}` MODIFY author VARCHAR(255) {$db_charset} {$db_collate}");

   		$wpdb->query("ALTER TABLE `{$table_name}` MODIFY source VARCHAR(255) {$db_charset} {$db_collate}");

   		if(!($wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'tags'"))) {
   			$wpdb->query("ALTER TABLE `{$table_name}` ADD `tags` VARCHAR(255) {$db_charset} {$db_collate} AFTER `source`");
		}
	}
	else {
		//Creating the table ... fresh!
		$sql = "CREATE TABLE " . $table_name . " (
			quote_id mediumint(9) NOT NULL AUTO_INCREMENT,
			quote TEXT NOT NULL,
			author VARCHAR(255),
			source VARCHAR(255),
			tags VARCHAR(255),
			visible enum('yes', 'no') DEFAULT 'yes' NOT NULL,
			time_added datetime NOT NULL,
			time_updated datetime,
			PRIMARY KEY  (quote_id)
		) {$db_charset} {$db_collate};";
		$results = $wpdb->query( $sql );
	}
	
	global $quotescollection_db_version;
	$options = get_option('quotescollection');
	$options['db_version'] = $quotescollection_db_version;
	update_option('quotescollection', $options);

}


function quotescollection_displayquote($quote_id = 0)
{
	global $wpdb;
	$sql = "SELECT quote_id, quote, author, source
		FROM " . $wpdb->prefix . "quotescollection 
		WHERE visible = 'yes' ";
	if(!$quote_id) {
		$sql .= "ORDER BY RAND()
			LIMIT 1";
	}
	else {
		$sql .= "AND quote_id = {$quote_id}";
	}
	$quote_data = $wpdb->get_row($sql, ARRAY_A);
	if ( !empty($quote_data) ) {
		$quote_data = quotescollection_txtfmt($quote_data);
		$display = "<blockquote class=\"quotescollection\"><q>".$quote_data['quote']."</q>";
		if($quote_data['author'])
			$cite = $quote_data['author'];
		if($quote_data['source']) {
			if($cite) $cite .= ", ";
			$cite .= $quote_data['source'];
		}
		if($cite) $cite = " <cite>&mdash;&nbsp;{$cite}</cite>"; 
		$display .= $cite."</blockquote>";
		return $display;
	}
	else
		return "";
}

function quotescollection_displayquotes($source = "")
{
	global $wpdb;
	$source = html_entity_decode($source);
	$sql = "SELECT quote_id, quote, author, source
		FROM " . $wpdb->prefix . "quotescollection 
		WHERE visible = 'yes' ";
	if(!$source) {
		$sql .= "ORDER BY quote";
	}
	else if($source == "Anonymous" || $source == "anonymous") {
		$sql .= "AND (author IS NULL OR author = '' OR author ='Anonymous')";
	}
	else {
		$sql .= "AND (source = '{$source}' OR author = '{$source}')";
	}
	$quotes = $wpdb->get_results($sql, ARRAY_A);
	if ( !empty($quotes) ) {
		foreach($quotes as $quote_data) {
			$quote_data = quotescollection_txtfmt($quote_data);
			$display .= "<blockquote class=\"quotescollection\"><q>".$quote_data['quote']."</q>";
			$cite = "";
			if($quote_data['author'])
				$cite = $quote_data['author'];
			if($quote_data['source']) {
				if($cite) $cite .= ", ";
				$cite .= $quote_data['source'];
			}
			if($cite) $cite = " <cite>&mdash;&nbsp;{$cite}</cite>"; 
			$display .= $cite."</blockquote>";
		}
		return $display;
	}
	else
		return "";
}

function quotescollection_displayquotes_tags($tags = "")
{
	global $wpdb;
	$tags = html_entity_decode($tags);
	if(!$tags)
		return "";
	$taglist = explode(',', $tags);
	foreach($taglist as $tag) {
		$tag = trim($tag);
		if($sql_condition) $sql_condition .= " OR ";
		$sql_condition .= "tags = '{$tag}' OR tags LIKE '{$tag},%' OR tags LIKE '%,{$tag},%' OR tags LIKE '%,{$tag}'";
	}
	$sql = "SELECT quote_id, quote, author, source
		FROM " . $wpdb->prefix . "quotescollection 
		WHERE visible = 'yes' AND ({$sql_condition})";
	$quotes = $wpdb->get_results($sql, ARRAY_A);
	if ( !empty($quotes) ) {
		foreach($quotes as $quote_data) {
			$quote_data = quotescollection_txtfmt($quote_data);
			$display .= "<blockquote class=\"quotescollection\"><q>".$quote_data['quote']."</q>";
			$cite = "";
			if($quote_data['author'])
				$cite = $quote_data['author'];
			if($quote_data['source']) {
				if($cite) $cite .= ", ";
				$cite .= $quote_data['source'];
			}
			if($cite) $cite = " <cite>&mdash;&nbsp;{$cite}</cite>"; 
			$display .= $cite."</blockquote>";
		}
		return $display;
	}
	else
		return "";
}

function quotescollection_inpost( $text ) {
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
    $text = preg_replace( "/\[quote\|all\]/ie", "quotescollection_displayquotes()", $text );
  }
	$start = strpos($text,"[quote|author=");
	if($start !== FALSE) {
		$text = preg_replace("/\[quote\|author=(.{1,})?\]/ie", "quotescollection_displayquotes(\"\\1\")", $text);
	}
	$start = strpos($text,"[quote|source=");
	if($start !== FALSE) {
		$text = preg_replace("/\[quote\|source=(.{1,})?\]/ie", "quotescollection_displayquotes(\"\\1\")", $text);
	}
	$start = strpos($text,"[quote|tags=");
	if($start !== FALSE) {
		$text = preg_replace("/\[quote\|tags=(.{1,})?\]/ie", "quotescollection_displayquotes_tags(\"\\1\")", $text);
	}	return $text;
}

function quotescollection_css_head() 
{

	if ( !defined('WP_PLUGIN_URL') )
		$wp_plugin_url = get_bloginfo( 'url' )."/wp-content/plugins";
	else
		$wp_plugin_url = WP_PLUGIN_URL;
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo $wp_plugin_url; ?>/quotes-collection/quotes-collection.css"/>
	<?php
}


add_action('wp_head', 'quotescollection_css_head' );


add_filter('the_content', 'quotescollection_inpost', 7);
add_filter('the_excerpt', 'quotescollection_inpost', 7);
register_activation_hook( __FILE__, 'quotescollection_install' );
add_action('admin_menu', 'quotescollection_admin_menu');
add_action('plugins_loaded', 'quotescollection_init');
?>
