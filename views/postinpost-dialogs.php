<?php if( ! defined( 'ABSPATH' ) ) exit; ?>
<div style="display:none">

	<div id='postinpost-browse' class="wp-dialog postinpost-dialog" title="Post In Post">
			<form>
			<input type='hidden' name="post_type" id="post_type" value="" />
			<?php if (count($post_types) == 0) : ?>
				<div class="postinpost-dialog-content">
					<p>There seems to be a problem with your configuration (no post types available).</p>
				</div>
			<?php else: ?>
				<div class='postinpost-dialog-header'>
					<ul class='tabs'>
							<?php 
							foreach($post_types as $index=>$post_type):
							?>
							<li><a class="<?php if ($index==0) echo 'current';?>" data-post-type='<?php echo $post_type->name;?>' data-post-label='<?php echo $post_type->labels->name;?>'><?php echo $post_type->labels->name;?></a></li>
							<?php	
							endforeach;
							?>
					</ul>
				</div>
				<div class="postinpost-dialog-content">
					<div class="postinpost-dialog-message">
						<p>Loading <?php echo $post_types[0]->labels->name;?>... </p>
					</div>
		
					<div class="postinpost-dialog-entries">
						
					</div>
				</div>
				
				<div class="postinpost-dialog-footer">
					<div class='major'>
						<input name="insert" type="submit" class="button-primary" id="postinpost-insert" value="Insert">
					</div>
					<div class='minor'>
						Insert as:  
						<label><input type='radio' name="insert_as" value="inline" checked="checked" /> Inline</label>
						<label><input type='radio' name="insert_as" value="shortcode" /> Shortcode</label>
						/
						Length:  
						<label><input type='radio' name="insert_length" value="full"/> Full</label>
						<label><input type='radio' name="insert_length" value="excerpt" checked="checked" /> Excerpt</label>
						
					</div>
				</div>

			<?php endif;?>
	
	
			</form>		
		</div>
	</div>

</div>

