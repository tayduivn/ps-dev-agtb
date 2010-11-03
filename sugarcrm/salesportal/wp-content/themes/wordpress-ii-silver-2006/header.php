<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>
	<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats -->
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php wp_head(); ?>
</head>
<body>

<?php // LILIYA CUSTOMIZATION 063008: Adding search to the top right corner ?>
<div id="head-search">
	<form id="searchform" method="get" action="<?php bloginfo('url'); ?>">
        	<input type="text" name="s" id="s" size="15" />
              	<input type="submit" value="<?php _e('Search'); ?>" />
       </form>			
</div>
<?php if(is_search()) { ?><div id="head-search-query"><p>You are searching for <b><?php the_search_query();  ?></b>.</p></div><?php } ?>
<?php // end LILIYA CUSTOMIZATION ?>


<!-- LOGO -->
<div id="header">
	<a href="index.php"><img src="/salesportal/wp-content/themes/wordpress-ii-silver-2006/images/header-logo.png" alt="Sugar Sales Portal" border="0" width="300" height="25" /></a>
<!-- <p class="description"> --><?php /* bloginfo('description'); */ ?><!-- </p> -->
</div>
<!-- end LOGO -->


<!-- TOP NAVIGATION -->
<div id="menu">
	<ul>
		<!-- <li><a href="<?php //echo get_option('home'); ?>/">Home</a></li>  -->
		<?php wp_list_pages('exclude=4&depth=1&sort_column=menu_order&title_li='); ?>
	</ul>
</div>
<!-- end TOP NAVIGATION -->

<div id="wrap">
