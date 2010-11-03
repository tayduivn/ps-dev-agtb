<?php get_header(); ?>

<!-- Dee CUSTOMIZATION 080630: Do not show sidebar if I am on the home page -->
<?php if(!empty($_REQUEST['page_id'])) { ?>
<?php get_sidebar(); ?>
<div id="content" style="margin-left: 230px;">
<?php  } else {
	 $_REQUEST['page_id'] = null;
	 ?>
<div id="content" style="margin-left: 20px;">
<?php } ?>
<!-- end Dee CUSTOMIZATION -->
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="post" id="post-<?php the_ID(); ?>">
<!-- Liliya CUSTOMIZATION 080630: Remove page title from the home page -->
		<?php if(!empty($_REQUEST['page_id']) && isset($_REQUEST['page_id'])) { ?>
<h2><?php the_title(); ?></h2>
<?php } ?>
<!-- end Liliya CUSTOMIZATION -->
		<div class="entrybody">
        	<?php if($_REQUEST['page_id'] != 63 || $_REQUEST['page_id'] != 71) { ?>
			<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); }
         			if($_REQUEST['page_id'] == 63) {
            			include 'submit_ref.php';
         			}
         			if($_REQUEST['page_id'] == 71)
         			{
            				include 'request_ref.php';
         			}
        	?>
		<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
		</div>
	</div>
		<?php endwhile; endif; ?>
		<?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
<!-- </div> -->
</div>

<?php get_footer(); ?>
