=== Quotes Collection ===
Contributors: SriniG
Tags: quotes collection, quotes, quotations, random quote, sidebar, widget, ajax
Requires at least: 2.2
Tested up to: 3.0-RC3
Stable tag: trunk

Quotes Collection plugin with Ajax powered Random Quote sidebar widget helps you collect and display your favourite quotes on your WordPress blog.

== Description ==

Quotes Collection plugin with Ajax powered Random Quote sidebar widget helps you collect, manage and display your favourite quotations on your WordPress blog. All quotes or a set of quotes can also be displayed on a page using a `[quote]` shortcode.

Features and notes:

* Random Quote sidebar widget with Ajax refresh feature -- you will be able to get another random quote on the same space without refreshing the web page. Following is the list of options in the widget control panel:
	* Widget title
	* Option to show/hide quote author
	* Option to show/hide quote source
	* Turn on/off the ajax refresh feature
	* Choose random or sequential order for refresh
	* Option to refresh the quote automatically
	* Show only quotes with certain tags
	* Specify a character limit and filter out bigger quotes
* The template function `quotescollection_quote()` can be used to display a random quote in places other than sidebar. See [other notes](http://wordpress.org/extend/plugins/quotes-collection/other_notes/) for details.
* Quotes can be displayed in a page by placing a shortcode such as the ones below.
	* Placing `[quote|all]` in the page displays all quotes.
	* `[quote|author=Somebody]` displays quotes authored by Somebody.
	* `[quote|source=Something]` displays quotes with source as 'Something'
	* `[quote|tags=sometag]` displays quotes tagged sometag 
	* `[quote|tags=tag1,tag2,tag3]` displays quotes tagged tag1 or tag2 or tag3, one or more or all of these
	* `[quote|id=3]` displays quote with ID 3
	* `[quote|random]` displays a random quote
* The plugin comes with a nice admin interface to add, edit and manage quotes.
* The plugin suppports localization. See [other notes](http://wordpress.org/extend/plugins/quotes-collection/other_notes/) for the full list of available languages and the respective translators. 

== Installation ==
1. Upload `quotes-collection` directory to the `/wp-content/plugins/` directory
1. Activate the 'Quotes Collection' plugin through the 'Plugins' menu in WordPress
1. Add and manage the quotes through the 'Quotes Collection' menu in the WordPress admin area
1. To display a random quote in the sidebar, go to 'Widgets' menu and drag the 'Random Quote' widget into the sidebar

== Frequently Asked Questions ==

= How to get rid of the quotation marks that surround the random quote? =

Open the quotes-collection.css file that comes along with the plugin, scroll down and look towards the bottom.

= The 'Next quote »' link is not working. Why? =

You have to check a couple of things,

1. Make sure your theme's header.php file has the code `<?php wp_head(); ?>` just before `</head>`.

2. Make sure the plugin files are uploaded in the correct location. The files should be uploaded in a location as follows
<pre>	wp-content/
	|-- plugins/
		|-- quotes-collection/
    		|-- quotes-collection.php
    		|-- quotes-collection.js
    		|-- quotes-collection.css
    		|-- quotes-collection-ajax.php</pre>
        
If you still experience the problem even after the above conditions are met, [contact](http://srinig.com/contact/) the plugin author.


= How to hide the 'Next quote »' link? = 

You can do this by turning off the 'Ajax Refresh feature' in widget options.

= How to change the link text from 'Next quote »' to something else? =

Change the value of the variable `$quotescollection_next_quote` on line 16 of the quotes-collection.php file.

= What are the parameters that can be passed on to  `quotescollection_quote()` template function? =

Please refer [other notes](http://wordpress.org/extend/plugins/quotes-collection/other_notes/)

= How about a feature to backup/export/import the bulk of quotes in CSV/text format? =

Such a feature will be available in a future version of the plugin, though no promises can be made as to when it will be available!

= How to change the admin access level setting for the quotes collection admin page? =

Change the value of the variable `$quotescollection_admin_userlevel` on line 26 of the quotes-collection.php file. Refer [WordPress documentation](http://codex.wordpress.org/Roles_and_Capabilities) for more information about user roles and capabilities.

= I have a long list of quotes, and `[quote|all]` puts all of the quotes in a single page. Is there a way to introduce pagination and break the long list of quotes into different pages? =

Inbuilt pagination support may be introduced in a future version. As of now, you can separate different set of quotes based on author name `[quote|author=]` or tags `[quote|tags=]` and introduce a [`<!--nextpage-->`](http://codex.wordpress.org/Styling_Page-Links) in between.

== Screenshots ==

1. Admin interface (in WordPress 3.0)
2. 'Random Quote' widget options (WordPress 3.0)
3. An example of the random quote displayed on a sidebar

== The quotescollection_quote() template function ==

The quotescollection_quote() template function can be used to display a random quote in places other than sidebar.

Usage: `<?php quotescollection_quote('arguments'); ?>`

The list of parameters (arguments) that can be passed on to this function:

* **show_author** *(boolean)*
	* To show/hide the author name
		* 1 - shows the author name (default)
		* 0 - hides the author name

* **show_source** *(boolean)*
	* To show/hide the source field
		* 1 - shows the source 
		* 0 - hides the source (default)

* **ajax_refresh** *(boolean)*
	* To show/hide the 'Next quote' refresh link
		* 1 - shows the refresh link (default)
		* 0 - hides the hides the refresh link
		
* **random** *(boolean)*
	* Refresh the quote in random or sequential order
		* 1 - random refresh (default)
		* 0 - sequential, with the latest quote first
		
* **auto_refresh** *(integer)*
	* To refresh the quote automatically. Time interval is the number assigned to this parameter, in seconds. For example, `<?php quotescollection_quote('auto_refresh=5'); ?>` will refresh the quote every 5 seconds. Set the value of auto_refresh to 0 to disable this functionality, which is default.
	
* **tags** *(string)*
	* Comma separated list of tags. Only quotes with one or more of these tags will be shown.
 
* **char_limit** *(integer)*
	* Quotes with number of characters more than this value will be filtered out. This is useful if you don't want to display long quotes using this function. The default value is 500.

* **echo** *(boolean)*
	* Toggles the display of the random quote or return the quote as an HTML text string to be used in PHP. The default value is 1 (display the quote). Valid values:
		* 1 (true) - default
		* 0 (false) 

**Example usage:**

* `<?php quotescollection_quote(); ?>`

	* Uses the default values for the parameters. Shows author, hides source, shows the 'Next quote' link, no tags filtering, no character limit, displays the quote.

* `<?php quotescollection_quote('show_author=0&show_source=1&tags=fun,fav'); ?>`

	* Hides author, shows source, only quotes tagged with 'fun' or 'fav' or both are shown. 'Next quote' link is shown (default) and no character limit (default).

* `<?php quotescollection_quote('ajax_refresh=0&char_limit=300'); ?>`

	* The 'Next quote' link is not shown, quotes with number of characters greater that 300 are left out.
	
== Localization ==

Versions 1.1 and greater support localization. As of the current version, localization is available in the following languages (code / language / author):

* `ar` / Arabic / [Ahmed Alharfi](http://www.alharfi.com/)
* `bg_BG` / Bulgarian / [Martin Petrov](http://mpetrov.net/)
* `bs_BA` / Bosnian / Vukasin Stojkov
* `by_BY` / Belarusian / [Илья](http://www.fatcow.com/)
* `cs_CZ` / Czech / Josef Ondruch
* `da_DK` / Danish / [Rune Clausen](http://www.runemester.dk/)
* `de_DE` / German / [Tobias Koch](http://tobias.kochs-online.net/2008/05/multilingual-blogging-using-wordpress/)
* `es_ES` / Spanish / [Germán L. Martínez (Gershu)](http://www.gershu.com.ar/)
* `fa_IR` / Persian / [Ehsan SH](http://mastaneh.ir/)
* `fi_FI` / Finnish / [Jussi Ruokomäki](http://jussi.ruokomaki.fi/)
* `fr_FR` / French / [psykotik](http://www.ikiru.ch/blog)
* `hi_IN` / Hindi / [Ashish J.](http://outshinesolutions.com/)
* `hr_HR` / Croatian / [1984da](http://faks.us/)
* `hu_HU` / Hungarian / [KOOS, Tamas](http://www.koosfoto.hu/)
* `it_IT` / Italian / [Gianni Diurno  (aka gidibao)](http://gidibao.net/index.php/2008/05/26/quotes-collection-in-italiano/)
* `ja` / Japanese / [Urepko Asaba](http://sourceforge.jp/projects/wppluginsj/downloads/36796/quotes-collection%201.2.3%20Lang-ja.zip)
* `lt_LT` / Lithuanian / Lulilo
* `lv_LV` / Latvian / [Maris Svirksts](http://www.moskjis.com/)
* `nl_NL` / Dutch / [Kristof Vercruyssen](http://www.simplit.be/)
* `pl_PL` / Polish / Marcin Gucia
* `pt_BR` / Brazilian Portugese / Tzor More
* `pt_PT` / Portugese / [Djamilo Jacinto](http://www.maxibim.net/)
* `ru_RU` / Russian / Andrew Malarchuk
* `sr_RS` / Serbian / Vukasin Stojkov
* `sv_SE` / Swedish / [Julian Kommunikation](http://julian.se/)
* `ta_IN` / Tamil / [Srini](http://srinig.com/)
* `tr_TR` / Turkish / [Gürkan Gür](http://seqizz.net/)
* `uk_UA` / Ukrainian / Stas
* `zh_CN` / Simplified Chinese / [天毅许](http://www.yylz.com/)

You can translate the plugin in your language if it's not done already. The localization template file (quotes-collection.pot) can be found in the 'languages' folder of the plugin. After translating send the localized files to the [plugin author](http://srinig.com/contact/) so that it's included in the next update. If you are not sure how to go about translating, contact the [plugin author](http://srinig.com/contact/).

==Changelog==
* **2010-06-17: Version 1.4**
	* Added ability to refresh quotes sequentially in the order added instead of random refresh.
	* Added ability to refresh quotes automatically in a specified time interval
	* The widget has two additional options (random refresh and auto refresh (+ time interval))
	* 'Quotes Collection' admin panel is now listed as a first-level menu from being a sub-menu under 'Tools' 
	* Other minor fixes, changes and improvements
	
* **2010-06-06: Version 1.3.8**
	* Fix for the backslashes issue.

* **2010-03-02: Version 1.3.7**
	* Localization in Hindi added.
	
* **2009-11-10: Version 1.3.6**
	* Localization in Bulgarian and Czech languages added.

* **2009-09-22: Version 1.3.5**
	* Brazilian Portugese localization added.
	* Modifications in quotes-collection.js (for better debugging in case of error)
	
* **2009-08-24: Version 1.3.4**
	* Finnish localization added.
	* FAQ updated.

* **2009-08-12: Version 1.3.3**
	* Localization in Simplified Chinese added.

* **2009-06-12: Version 1.3.2**
	* Latvian translation added. Hungarian translation updated.

* **2009-05-29: Version 1.3.1**
	* Bug fix (URL parsing issue)
	* Lithuanian translation added. Spanish and Russian updated

* **2009-05-28: Version 1.3**
	* Uses jQuery instead of SACK library for the AJAX refresh functionality
	* New widget option to filter based on tags
	* New widget option to set character limit for the random quote
	* Template function changed to `quotescollection_quote()`. The old function `quotescollection_display_randomquote()` will still work.
	* Parameters now passed in string format in the template function
	* Hungarian, Belarusian translations added. Swedish, Italian, Croatian, Turkish, Japanese, Persian, French and Tamil updated.
	* If you insert a url in quote, author, source, it becomes clickable in the random quote and  in quotes pages.
	* Other minor improvements
	
* **2009-04-20: Version 1.2.8**
    * Correcting a mistake in the previous update.

* **2009-04-20: Version 1.2.7**
    * Added localization in Portugese language
    * Fix to handle directory paths in windows servers

* **2009-04-14: Version 1.2.6**
    * Added localization in Serbian, Bosnian, Dutch and Persian languages

* **2009-02-27: Version 1.2.5**
    * Added localization in Swedish language
    * Minor tweaks and fixes

* **2009-02-04: Version 1.2.4**
	* Added translation in Danish, Croatian and Japanese languages
    * Minor fixes
    * FAQ section added in readme.txt to answer the frequently asked questions.

* **2008-11-08: Version 1.2.3**
    * Added Ukrainian translation (thanks to Stas for the translation)
    * Tested the plugin for the new admin interface that comes with WordPress 2.7 and a few tweaks. The plugin will work just fine in older WP versions

* **2008-10-06: Version 1.2.2**
    * Security fix, HTML tidy fix, other fixes
    * Updated Turkish trasnlation

* **2008-09-24: Version 1.2.1**
    * Arabic translation added
    * Minor fix (quotes-collection.js: errotext -> errortext)

* **2008-09-22: Version 1.2**
    * All javascript code moved to quotes-collection.js. This makes the code neater.
    * Translations for French, Polish and Turkish languages added.
    * Italian and Russian translations updated.
    * A few minor fixes and small improvements.

* **2008-07-02: Version 1.1.4**
    * Bug fixes. The plugin was not handling properly apostrophes in author and source fields. This is fixed now.
    * Other small fixes.

* **2008-06-05: Version 1.1.3.1**
    * Added Spanish translation.
    * Updated Italian translation.

* **2008-06-01: Version 1.1.3**
    * Improvements
    * Updated German translation
    * Added Russian translation

* **2008-05-28: Version 1.1.2.1**
    * VARCHAR(256) -> VARCHAR(255) (VARCHAR(256) doesnt work with MySQL 4.0)

* **2008-05-28: Version 1.1.2**
    * Modifications in the automatic database update functionality
    * Fixed problem with German translation
    * Added Italian translation

* **2008-05-25: Version 1.1.1**
    * security fix

* **2008-05-25: Version 1.1**
    * Tagging feature
    * Internationalization
    * Fixes and improvements

* **2008-03-11: Version 1.0**
    * Compatible with WordPress 2.5
    * Bug fixes and various other improvements

* **2008-02-06: Version 0.9.5**
    * Fixed problem with non English characters in author names while using the tag [quote|author=]

* **2008-01-16: Version 0.9.4**
    * Support for utf-8 characters
    * Fixed problem with linebreaks

* **2007-12-19: Version 0.9.3**
    * Fixed a JavaScript issue
    * Removed unnecessary `<h2></h2>` tags above random quote when title field is left blank in widget control options. `<h2>` tags displayed only when there is a title.

* **2007-12-18: Version 0.9.2**
    * Provision to add random quote anywhere in the template.

* **2007-12-16: Version 0.9.1**
    * Bug fix

* **2007-12-15: Version 0.9**
    * Initial release
