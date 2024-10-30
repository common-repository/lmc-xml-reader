=== LMC XML Reader ===
Contributors: Josef Štěpánek
Donate link: http://josefstepanek.cz/kontakt
Tags: czech, česky, xml, shortcode, lmc, jobs.cz, prace.cz, teamio, reader, parser, feed, external, content
Requires at least: 2.2
Tested up to: 4.8.1
Stable tag: trunk

LMC XML Reader plugin can load, parse and display an external XML file from given URL (LMC.cz, Jobs.cz, Prace.cz, Teamio.com). The parsed data can be displayed using a widget/shortcode.


== Description ==
LMC XML Reader plugin adds widget with a shortcode, which can be inserted in pages, posts or template files.

Main features and notes:

* Loads external XML file from URL and displays the content/data
* Supported XML formats: LMC.cz, Jobs.cz, Prace.cz, Teamio.com


== Installation ==
1. Unzip and upload the `lmc-xml-reader` directory to the `/wp-content/plugins/` directory.
2. Go to `WP Admin » Plugins` and activate the ‘LMC XML Reader’ plugin
3. Go to `WP Admin » Appearance » Widgets` and place the `LMC XML Reader` widget or move it to Inactive widgets
4. (optional) If in Inactive widgets, copy & paste the shortcode wherever you want.
5. (optional) Edit your shortcode and add parameter `related="Your Company Name"` (i.e. `[lmc-xml id="wp_widget_lmcxmlreader" related="JVS GROUP s.r.o."]`. It will display only positions related to the defined company.


== Frequently Asked Questions ==
No questions yet. Feel free to contact me at josef.stepanek@gmail.com


== Screenshots ==
1. Admin widget configuration
2. Display in a page as a shortcode


==Version History==

* **2017-11-06: Version 1.1**
    * Design polish, Related company filter support

* **2017-08-18: Version 1.0**
    * Multiple XML feeds support, Region & Section filter support

* **2016-04-09: Version 0.9**
    * Initial release
