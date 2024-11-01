<?php
/*
Plugin Name: Wizzart - Recent Comments
Description: Offers you a highly customizable widget to show recent comments of your blog in your sidebars. Multiple widgets are natively supported and you can change all the settings for every single widget in use. There is no extra plugin page because I think you dont need to polute the backend to configure a great widget!
Author: Dominik Guzei
Version: 1.3.4
Author URI: http://wizzart.at
Plugin URI: http://wizzart.at/development/plugin-wizzart-recent-comments/
*/

/*  Copyright 2010 Dominik Guzei  (email : dominik_guzei@gmx.at)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Load WP Sidebar Widget */
if (class_exists('WP_Widget')) {
	include(WP_PLUGIN_DIR.'/wizzart-recent-comments/Wizzart_Recent_Comments_Widget.php');
}

?>