=== Wizzart - Recent Comments ===
Contributors: Dominik Guzei
Tags: recent, comments, configurable, customizable, widget, format, wizzart, trackbacks, pingbacks, admin, category, filter, page, styles, format, user, excerpt, posts, gravatar, date, sidebar, Post, plugin, images, links
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 1.3.4

Display your recent comments the way YOU like and use fantastic looking comments by default!

== Description ==

This plugin offers a highly customizable widget to show recent comments of your visitors in your sidebars. The output is completely under your control for every widget with stunning features like custom tags and output styles!

= Special Features: =

* Widget Styles (NEW!): You can define css rules for each and every widget you placed in your sidebars. No need to edit external css files on the server. The widget comes with a beautiful styling by default!
* Comment Filters: Filter which comments you want to output (all, current category, single post/page comments, specific page), and a lot more to come!
* Comment Types: Choose which types of comments are displayed and even mix them (or not): user, admin, trackbacks, pingbacks!
* Output Html: Control exactly how you want your widget to output your comments with html.
* Custom Tags: You can mix your html with a huge number of custom tags that provide you with often needed properties of the comments, authors, posts, categories and so on..
* Gravatar enabled: This Plugin supports the built in gravatar function of wordpress and gives you the possibilty to use it in your way for the comment display.
* Multiple widgets: (in multiple sidebars) are natively supported and you can change all the settings for every single widget in use. There is no extra plugin page because I think you dont need to polute the backend to be able to configure a great widget!

Important Links:  
[Plugin Homepage](http://wizzart.at/development/plugin-wizzart-recent-comments/)  
[Tutorial: How to customize the output](http://wizzart.at/tutorials/customiz-wizzart-recent-comments/)  
[Showreel: Great examples of the plugin in use](http://wizzart.at/development/great-examples-of-wizzart-recent-comments/)  

Feel free to leave comments and ask questions on the plugin website!

== Installation ==
1. Upload the `wizzart-recent-comments` folder to the `/wp-content/plugins/` directory. 
2. Activate `Wizzart - Recent Comments` through the 'Plugins' menu in WordPress.
3. Go to the `Widgets` control panel, and drag the `Wizzart - Recent Comments` button into your sidebar list, and configure the options to your liking.  If you want to keep the defaults for the widget, just leave everything as-is.

== Frequently Asked Questions ==
= What's supposed to go into the gravatar default url? =
The gravatar default url field is optional and provides the possibility to link to a static image on your server.
This is might be important if you have many comments and are concerned about the page loading times because the gravatar feature loads the default image from the gravatar servers which can slow down the progress.

= How can I style the output? =
You have to know a bit about html and css to style the output of the plugin. There are two options:
You can use inline styles directly in the output format field (with: style="...") or you can use your standard wordpress css files and add rules at the end of them to modify the appearance of the output (this might be complicated for beginners). I am working hard to provide you with an easier solution as soon as possible.

== Screenshots ==
1. Widget Configuration Form
2. Sample Output from the authors website

== Changelog ==

= 1.3.4 =
Bugfix: Solved a problem on servers that dont provide mb_strlen and mb_substr functions for php.

= 1.3.3 =
Bugfix: The plugin caused an error when used on blogs that throw an error when retrieving the category link - these blogs can't use this feature (for the moment)

 = 1.3.2 =
Bugfix: The plugin caused an error when used with PHP 4 because of non supported access modifiers used for OOP in PHP 5.
* The plugin is now usable with PHP 4

= 1.3.1 =
* There is one new feature: You can now choose to exclude admin comments
* Bug fix (thanks to Massa P) : The gravatar %gravatar_size tag now represents just the number you entered in the field. The default markup reflects this change by adding width="%gravatar_size" and height="%gravatar_size".
* Feature fix: Daniel pointed out that the current category feature does not work on single posts, this works now -> but just the first category of the post is used!

= 1.3 =
* Output Styles (NEW!): Now there is a new option 'output styles' where you can define arbitrary css rules for every widget you use! The plugin comes with an advanced css styling by default.
* minor bug fixes on gravatar image sizing.

= 1.2.2 =
* Specific Page Filter: It is possible to show only comments that are posted to a specific wordpress page.
* minor bug fixes

= 1.2.1 =
* Important bugfix for pages - There occured several errors when using the plugin on a wordpress page because pages are not assigned to categories which was not considered in the code.
* the positive side effect: You can now use the plugin to show comments of the current single page!

= 1.2 =
* (NEW) Comment Types: You can choose what types of comments you want to display (user, trackback, pingback). So it's possible to seperate user comments and style them different as your trackbacks.
* Minor bugfixes in the configuration gui

= 1.1 =
* Important security update: This update fixes a security issue were any visitor could put %custom_tags into the comment body and thus output the same information available to the blog admin.
* Gravatar disabled bug: Fixes a bug that produced an error if the gravatar feature of wordpress was turned off.

= 1.0 =
* First release of the Wizzart - Recent Comments plugin.

== Upgrade Notice ==

= 1.3.4 =
Bugfix: Solved a problem on servers that dont provide mb_strlen and mb_substr functions for php.

= 1.3.3 =
Bugfix: The plugin caused an error when used on blogs that throw an error when retrieving the category link - these blogs can't use this feature (for the moment)

= 1.3.2 =
Bugfix: The plugin caused an error when used with PHP 4 because of non supported access modifiers used for OOP in PHP 5.

= 1.3.1 =
This is an important bug fixing update and there is one requested feature in the box!
* new feature: You can now choose to exclude admin comments
* Bug fix (thanks to Massa P) : The gravatar %gravatar_size tag now represents just the number you entered in the field. The default markup reflects this change by adding width="%gravatar_size" and height="%gravatar_size".
* Feature fix: Daniel pointed out that the current category feature does not work on single posts, this works now -> but just the first category of the post is used!

= 1.3 =
New special feature: Output Styles - Now there is a new option 'output styles' where you can define arbitrary css rules for every widget you use! The plugin comes with an advanced css styling by default.

= 1.2.2 =
New Feature: Specific Page Filter!
Now its possible to display only comments that were posted on a specific page, have fun!

= 1.2.1 =
Important bugfix for pages - There occured several errors when using the plugin on a wordpress page this is solved now.
the positive side effect: You can also use the plugin to show comments of the current single page!

= 1.2 =
New Feature: Comment Types!
You can choose what types of comments you want to display (user, trackback, pingback). 
So it's possible to seperate user comments and style them different as your trackbacks.

= 1.1 =
Important security update:
Fixed bug in comment parsing, this affected comments where custom tags were in the comment body.
This update is highly recommended because any visitor can use custom tags to show information about the comments!

= 1.0 =
Not upgrades available yet.