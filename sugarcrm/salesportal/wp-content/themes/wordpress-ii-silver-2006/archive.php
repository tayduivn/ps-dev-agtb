<?php get_header(); ?>
<?php get_sidebar(); ?>

<div id="content" style="margin-left: 230px;">
 <?php if (have_posts()) : ?>

		 <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
	   <?php /* If this is a category archive */ if (is_category()) { ?>
		<!-- <h2 class="pagetitle">Archive for the &#8216;<?php echo single_cat_title(); ?>&#8217; Category</h2> -->

	<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
		<h2 class="pagetitle">Archive for <?php the_time('F jS, Y'); ?></h2>

	<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
		<h2 class="pagetitle">Archive for <?php the_time('F, Y'); ?></h2>

	<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
		<h2 class="pagetitle">Archive for <?php the_time('Y'); ?></h2>

	<?php /* If this is an author archive */ } elseif (is_author()) { ?>
		<h2 class="pagetitle">Author Archive</h2>

	<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
		<h2 class="pagetitle">Blog Archives</h2>

	<?php } ?>
	
  	<!-- <div class="navigation">
			<div style="float:right" class="alignright"><?php previous_post_link('&laquo; %link') ?></div>
			<div class="alignleft"><?php next_post_link('%link &raquo;') ?></div>
		</div> -->
	

	<?php while (have_posts()) : the_post(); ?>
	<div class="entry entry-<?php echo $postCount ;?>">

		<div class="entrytitle">
			<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="Link to <?php the_title(); ?>" style="font-size:16px;font-weight:bold;text-decoration:none;"><?php the_title(); ?></a></h2>
		  <p><i>Posted by <?php the_author() ?> - <?php the_time('F jS, Y') ?></i></p>
    </div>
		<div class="entrybody">
      <?php the_content('Read the rest of this entry &raquo;'); ?>
		</div>
		
    <div style="border-bottom:1px solid #ccc;">
    </div>
		
    <div class="entrymeta">
		  <!-- <div class="feedback">
			 <?php comments_popup_link('No Response', '1 Pings', '% Pings', 'commentslink'); ?>
			</div>	--> 	
		</div>
	

	</div>

		<?php endwhile; ?>

		<div class="navigation">
			<div style="float:right" class="alignright"><?php previous_post_link('&laquo; %link') ?></div>
			<div class="alignleft"><?php next_post_link('%link &raquo;') ?></div>
		</div>

	<?php else : ?>

		<h2>Not Found</h2>
		<div class="entrybody">Sorry, but you are looking for something that isn't here.</div>

	<?php endif; ?>
	
</div>

<?php get_footer(); ?>
