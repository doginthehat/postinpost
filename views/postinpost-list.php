<?php if( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ($query->have_posts()):?>
<ul>
<?php
while($query->have_posts()): 
	$query->the_post();
	$has_post_thumbnail = has_post_thumbnail();
	?>
	<li class="<?php echo $has_post_thumbnail ? 'thumbnail' : 'no-thumbnail';?>">
		
		<?php 
		if ($has_post_thumbnail)
			the_post_thumbnail();
		?>
		<h3><label><input type='checkbox' name='postinpost_id[]' value='<?php the_ID();?>' /> <?php the_title();?></label></h3>
		<p><?php the_excerpt();?></p>
		<?php 
		if ($hierarchical)
		{
			$args = array(
				'post_type'		=> $post_type,
				'posts_per_page'	=> -1,
				'order'		=> 'ASC',
				'orderby'		=> 'menu_order ID',
				'post_parent'	=> get_the_ID()
			);
			$subquery = new WP_Query($args);
			PIP_Utils::view('postinpost-list',array('query'=>$subquery, 'post_type'=>$post_type, 'hierarchical'=>$hierarchical ) );


		}
		?>
	</li>
	<?php
endwhile;
?>
</ul>	
<?php endif;?>