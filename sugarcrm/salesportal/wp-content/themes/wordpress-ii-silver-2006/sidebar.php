	<div id="sidebar">
		<ul>	
			<!-- LEFT NAVIGATION: SEARCH -->
			<!-- Liliya CUSTOMIZATION 080630 - removed Search from left bar: -->
			<!-- 
			<li id="search"><h3><?php _e('Search'); ?></h3>
				<form id="searchform" method="get" action="<?php bloginfo('url'); ?>">
					<input type="text" name="s" id="s" size="15" />
					<input type="submit" value="<?php _e('Search'); ?>" />
				</form>
			</li> 
			-->
			<!-- end SEARCH -->

			<!-- Liliya CUSTOMIZATION 080630 - removed RESOURCES -->

			<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar() ) : else : ?>
				<!-- <li>
					<?php //include (TEMPLATEPATH . '/searchform.php'); ?>
				</li> --> 

			<!-- DEE CUSTOMIZATION: LEFT NAVIGATION: AUTHOR: removing author information as we not require it -->
			<!-- Author information is disabled per default. Uncomment and fill in your details if you want to use it.
			<li><h3>Author</h3>
			<p>A little something about you, the author. Nothing lengthy, just an overview.</p>
			</li>-->
			<!-- end AUTHOR -->

      			<?php
      				/* DEE CUSTOMIZATION:  OLD LIST SIDEBAR */
      				//wp_list_pages('depth=3&sort_column=menu_order&title_li=');
      				/* END DEE CUSTOMIZATION:  OLD LIST SIDEBAR */
			?>

			<!-- LEFT NAVIGATION: PAGES -->
      			<?php
        			/* DEE CUSTOMIZATION: LIST SIDEBAR - LEFT NAVIGATION - PARENT CHILD RELATIONSHIP */
			$page_id = $_REQUEST['page_id'];
				//if(empty($page_id) || !isset($page_id) || $page_id == '3')
				//{
       		 			get_pagelist($page_id);
				//}
				/* END DEE CUSTOMIZATION */
      			?>
			<!-- end PAGES -->

      			
 

      			<!-- LEFT NAVIGATION: NEWS ARCHIVES 
				<li><h3>News Archives</h3></li> -->
				<?php //wp_get_archives(); ?>
			<!-- end NEWS ARCHIVES -->

			<?php 
				/* If this is the frontpage */ 
				if ( is_home() || is_page() ) { 
					//wp_list_bookmarks(); 
				}
			?>
			<?php endif; ?>
		</ul>
	</div>
