<?php if( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
	<div class="icon32" id="icon-options-general"><br /></div>
	<h2><?php _e( 'Post in Post Settings', 'postinpost' ); ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'postinpost_options' ); ?>

		<h3><?php _e( 'General Settings', 'postinpost' ); ?></h3>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><?php _e( 'Popup Post Types', 'postinpost' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php _e( 'Popup Post Types', 'postinpost' ); ?></span></legend>
							<?php foreach($post_types as $post_type):
								if (in_array($post_type->name,$skip_post_types))
									continue;
							?>
							<label>
								<input type="checkbox" value="<?php echo $post_type->name;?>" name="postinpost_options[postinpost_post_types][]" <?php if (in_array($post_type->name, $options['post_types'])) echo "checked='checked'"; ?> /> <?php echo $post_type->labels->name;?> (<?php echo $post_type->name;?>)
							</label><br/>
							<?php endforeach;?>
							<p class="description"><?php _e( 'Select the post types available in Post In Post popup.', 'postinpost' ); ?></p>
							
						</fieldset>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Active Post Types Editors', 'postinpost' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php _e( 'Active Post Types Editors', 'postinpost' ); ?></span></legend>
							<?php foreach($post_types as $post_type):
								if (in_array($post_type->name,$skip_post_types))
									continue;
							?>
							<label>
								<input type="checkbox" value="<?php echo $post_type->name;?>" name="postinpost_options[postinpost_show_in][]" <?php if (in_array($post_type->name, $options['show_in'])) echo "checked='checked'"; ?> /> <?php echo $post_type->labels->name;?> (<?php echo $post_type->name;?>)
							</label><br/>
							<?php endforeach;?>
							<p class="description"><?php _e( 'Select the post types where the extension should be loaded.', 'postinpost' ); ?></p>
							
						</fieldset>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Inception Resolution (Shortcodes only)', 'postinpost' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php _e( 'Inception Resolution', 'postinpost' ); ?></span></legend>
							<label for="postinpost_inception">
								<select id="postinpost_inception" name="postinpost_options[postinpost_inception]">
									<option value="link" <?php selected( 'link', $options['inception_behaviour'] ); ?>>Insert as Link</option>
									<option value="ignore" <?php selected( 'ignore', $options['inception_behaviour'] ); ?>>Leave blank</option>
								</select>
								
							</label><br/>
							<p class="description"><?php _e( 'What to do when infinite nesting of entries happen.', 'postinpost' ); ?></p>
						</fieldset>
					</td>
				</tr>

			</tbody>
		</table>


		<p class="submit">
			<input type="submit" value="<?php esc_attr_e( 'Save Changes', 'postinpost' ); ?>" class="button-primary" name="Submit">
		</p>
	</form>
</div>