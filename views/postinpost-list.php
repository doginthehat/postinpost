<?php if( ! defined( 'ABSPATH' ) ) exit; ?>
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
	</li>
	<?php
endwhile;
?>
</ul>	