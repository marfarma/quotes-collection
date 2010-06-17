var quotcoll_requrl, quotcoll_nextquote, quotcoll_loading, quotcoll_errortext, quotcoll_auto_refresh_max;
var quotcoll_auto_refresh_count = 0;

function quotescollection_init(requrl, nextquote, loading, errortext, auto_refresh_max)
{
	quotcoll_requrl = requrl;
	quotcoll_nextquote = nextquote;
	quotcoll_loading = loading;
	quotcoll_errortext = errortext;
	quotcoll_auto_refresh_max = auto_refresh_max;
}

function quotescollection_timer(instance, current, show_author, show_source, filter_tags, char_limit, auto_refresh, random_refresh)
{
	var time_interval = auto_refresh * 1000;
	if( (quotcoll_auto_refresh_max == 0) || (quotcoll_auto_refresh_count < quotcoll_auto_refresh_max) ) {
		setTimeout("quotescollection_refresh("+instance+", "+current+", "+show_author+", "+show_source+", '"+filter_tags+"', "+char_limit+", "+auto_refresh+", "+random_refresh+")", time_interval);
		quotcoll_auto_refresh_count += 1;
	}
}



function quotescollection_refresh(instance, current, show_author, show_source, filter_tags, char_limit, auto_refresh, random_refresh)
{
	jQuery("#quotescollection_nextquote-"+instance).html(quotcoll_loading);
	jQuery.ajax({
		type: "POST",
		url: quotcoll_requrl,
		data: "refresh="+instance+"&current="+current+"&show_author="+show_author+"&show_source="+show_source+"&char_limit="+char_limit+"&tags="+filter_tags+"&auto_refresh="+auto_refresh+"&random_refresh="+random_refresh,
		success: function(response) {
			jQuery("#quotescollection_randomquote-"+instance).hide();
			jQuery("#quotescollection_randomquote-"+instance).html( response );
			jQuery("#quotescollection_randomquote-"+instance).fadeIn("slow");	
		},
		error: function(xhr, textStatus, errorThrown) {
//			alert(textStatus+' '+xhr.status+': '+errorThrown);
			if(auto_refresh == 0)
				jQuery("#quotescollection_nextquote-"+instance).html('<a class=\"quotescollection_refresh\" style=\"cursor:pointer\" onclick=\"quotescollection_refresh('+instance+', '+exclude+', '+show_author+', '+show_source+', \''+filter_tags+'\', '+char_limit+')\">'+quotcoll_nextquote+'</a>');
		}	
	});
}

