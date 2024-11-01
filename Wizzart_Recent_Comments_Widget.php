<?php 

if (class_exists('WP_Widget')) {
	
	class Wizzart_Recent_Comments_Widget extends WP_Widget {
		
		var $format_default = '<li class="recent-comment">
			<a class="comment-link" href="%post_link" title="Read the post: %post_title, written on %post_date">
			<img src="%gravatar_url" class="comment-author-gravatar" width="%gravatar_size" height="%gravatar_size" alt="Gravatar icon of %comment_author"/>
			<span class="comment-meta">
			<span class="comment-author">%comment_author</span>
			<br />
			<span class="comment-date-time">%comment_date (%comment_time)</span>
			</span>
			<span class="comment-post-title">%post_title</span>
			<span class="comment-text">%comment_excerpt</span>
			</a>
			</li>';
			
		var $styles_default = '.recent-comment a.comment-link {
	 text-decoration: none;
	 display:block;
	 padding: 8px;
	 border: 1px dashed #e8cdab;
}

.recent-comment a.comment-link:hover {
	 background-color: #f2e7cd;
}

.recent-comment .comment-author-gravatar {
	 float:left;
     margin-right: 10px;
}

.recent-comment .comment-author {
	 font-size: 1em;
}

.recent-comment .comment-meta {
	 line-height: 1.2em;
	 text-decoration: none;
}

.recent-comment .comment-date-time {
	 color: #666;
	 font-size: 0.9em;
}

.recent-comment .comment-post-title {
     margin: 25px 0 0.5em 0;
	 text-decoration:none;
	 font-size: 0.9em;
	 font-weight: bold;
	 display:block;
         width: 100%;
}

.recent-comment .comment-text {
	 font-family: Georgia,"Times New Roman",Times,serif;
	 font-style: italic;
	 font-size: 0.9em;
	 color: #888;
}';

		/** constructor */
		function Wizzart_Recent_Comments_Widget() {
			/* widget properties*/
			$widget_ops = array('classname' => 'wizzart_recent_comments_widget', 'description' => __( "Shows recent comments of your blog.") );
            
			/* Widget control settings. */
			$control_ops = array('width' => 650, 'height' => 510);
			
			/* initialize the parent widget class */
			$this->WP_Widget('wizzart-recent-comments', __('Wizzart - Recent Comments'), $widget_ops, $control_ops);
			$this->alt_option_name = 'wizzart_recent_comments_widget';
			
			/* tell wordpress to flush the cache on actions that affect comments */
			add_action( 'comment_post', array(&$this, 'flush_widget_cache') );
			add_action( 'transition_comment_status', array(&$this, 'flush_widget_cache') );
			
			add_action('wp_head', array(&$this, 'addStyles'));
		}
	
		/** @see WP_Widget::widget */
		function widget($args, $instance) {	
			
			global $wpdb;
			
			/* cache management */
			
			$cache = wp_cache_get($this->alt_option_name, 'widget');
			
			if ( !is_array($cache) ) 
				$cache = array();
			
			if ( isset($cache[$args['widget_id']]) ) {
				echo "CACHED !!!";
				echo $cache[$args['widget_id']];
				return;
			}
	
			ob_start(); // start output buffering -> save to cache later
			extract($args); // make arguments available
			
			$widget_title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Comments') : $instance['title']);
			//$comments_admin = apply_filters('comments_admin', empty($instance['comments_admin']) ? 'true' : $instance['comments_admin']);
			
			/* set the number of shown comments */
			
			if ( !$number = (int) $instance['number'] )
				$number = 5;
			elseif( $number < 1 )
				$number = 1;
			
			$output_format = apply_filters('output_format', empty($instance['output_format']) ? $this->format_default : $instance['output_format']);
			
			/* set size of avatar image */
			
			if ( !$gravatar_size = (int) $instance['gravatar_size'] )
				$gravatar_size = 32;
			elseif($gravatar_size < 32)
				$gravatar_size = 32;
			elseif($gravatar_size > 512)
				$gravatar_size = 512;
			
			/* Retrieve Comments from database */
			
			// filter the comment types (user, trackback, pingback)
			
			if(isset($instance['comments_user']) && $instance['comments_user'] == 'true') { $user = true; }
			else { $user = false; }
			
			if(isset($instance['comments_trackback']) && $instance['comments_trackback'] == 'true') { $trackbacks = true; }
			else { $trackbacks = false; }
			
			if(isset($instance['comments_pingback']) && $instance['comments_pingback'] == 'true') { $pingbacks = true; }
			else { $pingbacks = false; }
			
			if(isset($instance['comments_admin']) && $instance['comments_admin'] == 'true') { $comments_admin = true; }
			else { $comments_admin = false; }
			
			$comments_types = '';
			if($user) {
				$comments_types = "AND (" . 
					($trackbacks ? "1=1" : "comment_type != 'trackback'") . 
					" AND " . 
					($pingbacks ? "1=1" : "comment_type != 'pingback'") . ")";
			}
			if(!$user) {
				$comments_types = "AND (" . 
					($trackbacks ? "comment_type = 'trackback'" : "1=0" ) . 
					" OR " . 
					($pingbacks ? "comment_type = 'pingback'" : "1=0" ) . ")"; 	
			}
			
			// filter the comments visibility (whole blog, category, single page)
			
			$comments_filter = '';
			switch($instance['comments_filter']) {
				case 'category':
					if(is_category()) {
						
						$title = single_cat_title('', false);
						$cat_id = get_cat_ID($title);
						
					} else if(is_single()) {
						global $post;
						// get the category
						$categories = get_the_category($post->ID); // returns and ARRAY of categories
						$category = $categories[0]; // use first category to fetch comments
						$cat_id = $category->cat_ID;
						
					} else { // TODO: echo error message if this is used in wrong context?
						$comments_filter = "WHERE 1 ";
					}
					
					if($cat_id) {
							
						$term_rel = $wpdb->term_relationships;
						$comments_filter = "JOIN (".$term_rel." JOIN ".$wpdb->term_taxonomy." ON (".$wpdb->term_taxonomy.".term_taxonomy_id=".$term_rel.".term_taxonomy_id))";
						$comments_filter .= "ON (".$wpdb->comments.".comment_post_ID=".$term_rel.".object_ID)";
						$comments_filter .= "WHERE ".$wpdb->term_taxonomy.".term_id=".$cat_id." AND ".$wpdb->term_taxonomy.".taxonomy='category' ";
					}
				break;
				case 'single':
					if(is_single() || is_page()) {
						
						global $post; // retrieve global single post
						
						$comments_filter = "WHERE comment_post_ID = $post->ID "; 
						
					} else { // TODO: echo error message if this is used in wrong context?
						$comments_filter = "WHERE 1 ";
					}					
				break;
				case 'specific_page':
					$comments_filter = "WHERE comment_post_ID = '" . $instance['specific_page_id'] . "'";
				break;
				default: 
				// select all
					$comments_filter = "WHERE 1 ";
			}
			
			// filter admin
			$filter_admin = "";
			if(!$comments_admin) {
				$filter_admin = " AND user_ID != '1'";
			}
			
			$comments_filter .= $comments_types;
			$query = "SELECT * FROM $wpdb->comments "; 
			$query .= $comments_filter; 
			$query .= " AND comment_approved='1'$filter_admin";
			$query .= "ORDER BY comment_date DESC LIMIT $number"; 
			
			/* do one single query to retrieve all needed comments */
			$comments = $wpdb->get_results($query);
			
			if(count($comments)) { // if there are comments fetched (else dont show the widget)
				
				$output = "";
				
				foreach($comments as $comment) {
					$html = $output_format;
					
					/* customize the comments with format control tags */
					
					// get the post
					$post = get_post($comment->comment_post_ID); // returns an object with properties
					
					if($post->post_type == 'post') { // pages dont have categories assigned
						// get the category
						$categories = get_the_category($comment->comment_post_ID); // returns and ARRAY of categories
						$category = $categories[0]; // use first category for most properties
					}
					
					// get the gravatar & url
					if($instance['gravatar_default'] != '') {
						$gravatar = get_avatar($comment->comment_author_email, $gravatar_size, $instance['gravatar_default']);
					} else {
						$gravatar = get_avatar($comment->comment_author_email, $gravatar_size);
					}
					
					$matches = array();
					preg_match('/src\s*=\s*\'(\S+)\'/', $gravatar, $matches); // extrude the src (url) of wordpress gravatar
					if(isset($matches[1])) {
						$gravatar_url = $matches[1];
					} else {
						$gravatar_url = '';
					}
					
					// make comment excerpt
					$comment_excerpt = $this->truncate($comment->comment_content, $instance['excerpt_length'], $instance['excerpt_trailing']);
					
					$post_link    = get_permalink($post->ID);
					$comment_link = $post_link . "#comment-$comment->comment_ID";
					if (!$comment->comment_author) {
						$comment->comment_author = "Anonymous";
					}
								
					//$html = str_replace( "%time_since", $comment->comment_author, $html);
					
					/* author properties */
					$html = str_replace( "%comment_author_email", 	$comment->comment_author_email, $html);
					$html = str_replace( "%comment_author_url", 	$comment->comment_author_url, 	$html);
					$html = str_replace( "%comment_author", 		$comment->comment_author, 		$html);
					
					$html = str_replace( "%gravatar_url", 	$gravatar_url, $html);
					$html = str_replace( "%gravatar_size", 	$gravatar_size, $html);
					$html = str_replace( "%gravatar", 	$gravatar, $html);
					
					/* post properties */
					$html = str_replace( "%post_link", 		$post_link, 			$html);
					$html = str_replace( "%post_author", 	$post->post_author, 	$html);
					$html = str_replace( "%post_title", 	$post->post_title, 		$html);
					$html = str_replace( "%post_excerpt", 	$post->post_excerpt,	$html); 
					$html = str_replace( "%post_date", 		mysql2date($instance['date_format'],$post->post_date), $html);
					$html = str_replace( "%post_time", 		mysql2date($instance['time_format'],$post->post_date), $html);
					
					if($post->post_type == 'post') { // pages dont have categories assigned
					
						/* category properties */
						$catLinkString = get_category_link($category->cat_ID);
						
						if(is_string($catLinkString)) {
							$html = str_replace( "%category_link", 		get_category_link($category->cat_ID), 	$html);
						}
						
						$html = str_replace( "%category_name", 			$category->cat_name, 					$html);
						$html = str_replace( "%category_description", 	$category->category_description, 		$html);
						// support for category icons plugin
						if (function_exists("get_cat_icon")) {
							$category_icon = get_cat_icon("cat=".$category->cat_ID."&echo=0&link=false");
							$html = str_replace( "%category_icon", $category_icon, $html);
						}
					}
					
					/* comment properties have to be last so that the excerpt text is not parsed!! */
					$html = str_replace( "%comment_id", $comment->comment_ID, $html);
					$html = str_replace( "%comment_link", $comment_link, $html);
					$html = str_replace( "%comment_date", mysql2date($instance['date_format'],$comment->comment_date), $html);
					$html = str_replace( "%comment_time", mysql2date($instance['time_format'],$comment->comment_date), $html);
					$html = str_replace( "%comment_excerpt", $comment_excerpt, $html);
					
					$output .= $html; // append customized html code to the output
				}
			
				/* output the formated widget */
				echo $before_widget;
				if ( $widget_title ) { echo $before_title . $widget_title . $after_title; }
				echo '<' . $instance['list_type'] . ' class="' . $instance['list_class'] . '">'. $output . '</'. $instance['list_type'] . '>';
				echo $after_widget;
				
			} // end if there are comments fetched
						
			/* save data to cache */
			
			$cache[$args['widget_id']] = ob_get_flush(); // set output to cache
			wp_cache_set($this->alt_option_name, $cache, 'widget'); // save the cache to wordpress
			
		}
	
		/** @see WP_Widget::update */
		function update($new_instance, $old_instance) {				
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
            $instance['number'] = (int) $new_instance['number'];
			$instance['excerpt_length'] = (int) $new_instance['excerpt_length'];
			$instance['excerpt_trailing'] = strip_tags($new_instance['excerpt_trailing']);
			$instance['comments_filter'] = $new_instance['comments_filter'];
			$instance['specific_page_id'] = $new_instance['specific_page_id'];
			$instance['comments_user'] = $new_instance['comments_user'];
			$instance['comments_trackback'] = $new_instance['comments_trackback'];
			$instance['comments_pingback'] = $new_instance['comments_pingback'];
			$instance['comments_admin'] = $new_instance['comments_admin'];
			$instance['list_type'] = $new_instance['list_type'];
			$instance['list_class'] = strip_tags($new_instance['list_class']);
 			$instance['output_format'] = $new_instance['output_format'];
 			$instance['output_styles'] = $new_instance['output_styles'];
			$instance['gravatar_default'] = $new_instance['gravatar_default'];
			$instance['gravatar_size'] = (int) $new_instance['gravatar_size'];
			$instance['date_format'] = strip_tags($new_instance['date_format']);
			$instance['time_format'] = strip_tags($new_instance['time_format']);
			
			$this->flush_widget_cache();

			$alloptions = wp_cache_get( 'alloptions', 'options' );
			if ( isset($alloptions[$this->alt_option_name]) ) {
				delete_option($this->alt_option_name);
			}
			
            return $instance;
		}
		
		function flush_widget_cache() {
			wp_cache_delete($this->alt_option_name, 'widget');
		}
	
		/** @see WP_Widget::form */
		function form($instance) {				
			$defaults = array(
				'title' => 'Recent Comments',
            	'number' => 5,
				'excerpt_length' => 100,
				'excerpt_trailing' => '...',
				'comments_filter' => 'all',
				'comments_user' => 'true',
				'comments_trackback' => 'false',
				'comments_pingback' => 'false',
				'comments_admin' => 'true',
				'list_type' => 'ul',
				'list_class' => 'recent-comments-list',
				'gravatar_default' => '',
				'gravatar_size' => 40,
				'date_format' => 'F j, Y',
				'time_format' => 'g:i',
				'output_format' => $this->format_default,
				'output_styles' => $this->styles_default
			);
			
			$instance = wp_parse_args((array) $instance, $defaults);
			
    		?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><strong><?php _e('Title')?>: </strong></label>
            <input style="width:370px" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
            </p>
            <hr noshade="noshade"/>
            <p><label for="<?php echo $this->get_field_id('number'); ?>"><strong><?php _e('Number of Comments'); ?>: </strong></label>
            <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $instance['number']; ?>" size="3" />
            <small><?php _e('(max 20)')?></small>
            <label for="<?php echo $this->get_field_id('excerpt_length'); ?>"><strong><?php _e('Excerpt Length'); ?>: </strong></label>
            <input id="<?php echo $this->get_field_id('excerpt_length'); ?>" name="<?php echo $this->get_field_name('excerpt_length'); ?>" type="text" value="<?php echo $instance['excerpt_length']; ?>" size="3" />
            
            <label for="<?php echo $this->get_field_id('excerpt_trailing'); ?>"><strong><?php _e('Excerpt Trailing'); ?>: </strong></label>
            <input style="width: 100px;" id="<?php echo $this->get_field_id('excerpt_trailing'); ?>" name="<?php echo $this->get_field_name('excerpt_trailing'); ?>" type="text" value="<?php echo $instance['excerpt_trailing']; ?>" size="3" />
            </p>
            <hr noshade="noshade"/>
            <p>
            <label for="<?php echo $this->get_field_id('comments_filter'); ?>"><strong><?php _e('Filter Comments'); ?>: </strong></label>
            <select id="<?php echo $this->get_field_id('comments_filter'); ?>" name="<?php echo $this->get_field_name('comments_filter'); ?>">
                <option value="all">All Comments</option>
                <option <?php if($instance['comments_filter'] == 'category') echo 'selected'?> value="category">Current Category</option>
                <option <?php if($instance['comments_filter'] == 'single') echo 'selected'?> value="single">Current Post/Page</option>
                <option <?php if($instance['comments_filter'] == 'specific_page') echo 'selected'?> value="specific_page">Specific Page</option>
            </select>
            <select  id="<?php echo $this->get_field_id('specific_page_id'); ?>" name="<?php echo $this->get_field_name('specific_page_id'); ?>">
			 <?php 
              $pages = get_pages(); 
              foreach ($pages as $page) {
				$selected = "";
				if($page->ID == intval($instance['specific_page_id'])) {
					$selected = 'selected';
				}
                $option = '<option value="' . $page->ID . '" ' . $selected . '>';
                $option .= $page->post_title;
                $option .= '</option>';
                echo $option;
              }
             ?>
            </select>
            <script type='text/javascript'>
			/* <![CDATA[ */
				var filterDropdown = document.getElementById("<?php echo $this->get_field_id('comments_filter'); ?>");
				if(filterDropdown.options[filterDropdown.selectedIndex].value != 'specific_page') {
					var pageDropdown = document.getElementById("<?php echo $this->get_field_id('specific_page_id'); ?>");
					pageDropdown.style.display = 'none';
				}
				function onFilterChange() {
					if ( filterDropdown.options[filterDropdown.selectedIndex].value == 'specific_page' ) {
						pageDropdown.style.display = '';
					} else {
						pageDropdown.style.display = 'none';	
					}
				}
				filterDropdown.onchange = onFilterChange;
			/* ]]> */
			</script>
            </p>
            <hr noshade="noshade"/>
            <p>
            <label for="<?php echo $this->get_field_id('comments_user'); ?>"><strong><?php _e('User Comments'); ?>: </strong></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('comments_user'); ?>" name="<?php echo $this->get_field_name('comments_user'); ?>" value="true" <?php if($instance['comments_user'] == 'true') echo 'checked="checked"'?>>
            <label for="<?php echo $this->get_field_id('comments_trackback'); ?>"><strong><?php _e('Trackbacks'); ?>: </strong></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('comments_trackback'); ?>" name="<?php echo $this->get_field_name('comments_trackback'); ?>" value="true" <?php if($instance['comments_trackback'] == 'true') echo 'checked="checked"'?>>
            <label for="<?php echo $this->get_field_id('comments_pingback'); ?>"><strong><?php _e('Pingbacks'); ?>: </strong></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('comments_pingback'); ?>" name="<?php echo $this->get_field_name('comments_pingback'); ?>" value="true" <?php if($instance['comments_pingback'] == 'true') echo 'checked="checked"'?>>
            <label for="<?php echo $this->get_field_id('comments_admin'); ?>"><strong><?php _e('comments by admin'); ?>: </strong></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('comments_admin'); ?>" name="<?php echo $this->get_field_name('comments_admin'); ?>" value="true" <?php if($instance['comments_admin'] == 'true') echo 'checked="checked"'?>>
            </p>
            <hr noshade="noshade"/>
            <label for="<?php echo $this->get_field_id('list_type'); ?>"><strong><?php _e('List Type'); ?>: </strong></label>
            <select id="<?php echo $this->get_field_id('list_type'); ?>" name="<?php echo $this->get_field_name('list_type'); ?>">
                <option value="ul">unordered list (ul)</option>
                <option <?php if($instance['list_type'] == 'ol') echo 'selected'?> value="ol">ordered list (ol)</option>
                <option <?php if($instance['list_type'] == 'div') echo 'selected'?> value="div">container (div)</option>
            </select>
            
            <label for="<?php echo $this->get_field_id('list_class'); ?>"><strong><?php _e('List Classes (css)'); ?>: </strong></label>
            <input style="width: 280px;" id="<?php echo $this->get_field_id('list_class'); ?>" name="<?php echo $this->get_field_name('list_class'); ?>" type="text" value="<?php echo $instance['list_class']; ?>" size="3" />
            
            </p>
            <hr noshade="noshade"/>
            <p>
            <label for="<?php echo $this->get_field_id('gravatar_default'); ?>"><strong><?php _e('Gravatar Default Image (URL)'); ?>: </strong></label>
            <input style="width: 250px;" id="<?php echo $this->get_field_id('gravatar_default'); ?>" name="<?php echo $this->get_field_name('gravatar_default'); ?>" type="text" value="<?php echo $instance['gravatar_default']; ?>" size="3" />
            
            <label for="<?php echo $this->get_field_id('gravatar_size'); ?>"><strong><?php _e('Image Size'); ?>: </strong></label>
            <input id="<?php echo $this->get_field_id('gravatar_size'); ?>" name="<?php echo $this->get_field_name('gravatar_size'); ?>" type="text" value="<?php echo $instance['gravatar_size']; ?>" size="3" />
			<small><?php _e('(min 32, max 512)')?></small>
            </p>
            <hr noshade="noshade"/>
            <p><label for="<?php echo $this->get_field_id('date_format'); ?>"><strong><?php _e('Date Format'); ?>: </strong></label>
            <input style="width: 80px" id="<?php echo $this->get_field_id('date_format'); ?>" name="<?php echo $this->get_field_name('date_format'); ?>" type="text" value="<?php echo $instance['date_format']; ?>" />
			
            <label for="<?php echo $this->get_field_id('time_format'); ?>"><strong><?php _e('Time Format'); ?>: </strong></label>
            <input style="width: 80px" id="<?php echo $this->get_field_id('time_format'); ?>" name="<?php echo $this->get_field_name('time_format'); ?>" type="text" value="<?php echo $instance['time_format']; ?>" />
            <small><a href="http://php.net/manual/en/function.date.php"><?php _e('use php date() format for both')?></a></small>
            </p>
            <hr noshade="noshade"/>
            <p><label for="<?php echo $this->get_field_id('output_format'); ?>"><strong><?php _e('Output Format')?>: </strong></label>
            <small>Valid HTML mixed with %tags to customize the comment output. <a href="http://wizzart.at/tutorials/customiz-wizzart-recent-comments/">The list of supported tags</a></small></p>
            <p>
            <textarea style="width: 630px; height:200px" id="<?php echo $this->get_field_id('output_format'); ?>" name="<?php echo $this->get_field_name('output_format'); ?>"><?php echo $instance['output_format']; ?></textarea>
            </p>
            
			<p><label for="<?php echo $this->get_field_id('output_styles'); ?>"><strong><?php _e('Output Styles')?>: </strong></label>
            <small>Here you can define the css styles that are applied to your recent comments markup</a></small></p>
            <p>
            <textarea style="width: 630px; height:200px" id="<?php echo $this->get_field_id('output_styles'); ?>" name="<?php echo $this->get_field_name('output_styles'); ?>"><?php echo $instance['output_styles']; ?></textarea>
            </p>
            <?php
		}
		
		function addStyles() {
			$styles = "";
			$ops = get_option('widget_wizzart-recent-comments');
			foreach($ops as $value) {
				if(is_array($value) && sizeof($value) > 10) {
					$styles .= $value['output_styles'];	
				}
			}
			if($styles != "") { // only display styles when the user has entered any styles!
				$styles = '<style type="text/css">' . $styles;
				$styles .= '</style>';
				echo $styles;
			}
		}
		
		/*
		** truncate function to create comment excerpts
		**
		** $str -String to truncate
		** $length - length to truncate
		** $trailing - the trailing character, default: "..."
		*/
		function truncate ($str, $length, $trailing='...')
		{
			// take off chars for the trailing
			$length-=strlen($trailing);
			if (strlen($str)> $length)
			{
			   // string exceeded length, truncate and add trailing dots
			   return substr($str,0,$length).$trailing;
			}
			else
			{
			   // string was already short enough, return the string
			   $res = $str;
			}
			return $res;
		}
	
	} // class Wizzart_Recent_Comments_Widget
	
	// Add our function to the widgets_init hook.
	add_action('widgets_init', 'WRC_load');
	
	function WRC_load() {
		register_widget('Wizzart_Recent_Comments_Widget');
	}

} // if function exsists WP_Widget
?>