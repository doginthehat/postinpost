<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Post In Post admin class
 */
class PostInPost_Admin {
	
	const TINYMCE_PLUGIN_NAME = 'postinpost';

	/**
	 * Main instance of the plugin class
	 *
	 * @var object
	 */
	private $plugin; 
	

	/**
	 * Constructor
	 */
	public function __construct($plugin) 
	{
	
		$this->plugin = $plugin;
	
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		
		add_action( 'admin_menu', array( $this, 'admin_menu') );
		
		// Seemed to be the most relevant event that would pass the post and post type being edited..
		add_action('add_meta_boxes', array( $this, 'load_resources'), 10, 2);
		
		$this->registerAjaxHandlers();
		
	}
	
	public function admin_init()
	{
		register_setting( 'postinpost_options', 'postinpost_options', array( $this, 'options_validate' ) );

		

	}
	
	public function admin_menu()
	{
		if ( current_user_can('manage_options') ) {
			add_options_page( 'Post in Post Settings', 'Post in Post Settings', 'activate_plugins', 'postinpost-options' , array($this, 'options_page') );
		}
				
	}
	
	public function registerAjaxHandlers()
	{
		$methods = get_class_methods($this);
		
		foreach($methods as $method)
		{
			if (substr_compare($method,'ajax_',0,5)===0)
			{
				add_action('wp_ajax_'.substr($method,5), array(&$this, $method) );
			}
		}

		
	}
	
	public function load_resources($post_type, $post)
	{
		if (!in_array($post_type, $this->plugin->show_in))
			return;
	
		$this->addResources();

		$this->extendTinyMCE();
				
		add_filter('admin_footer',	array($this, 'templates') );
		
	}
	
	public function extendTinyMCE()
	{
		if ( get_user_option('rich_editing') != 'true')
			return;

		add_filter('mce_external_plugins',	array($this, 'tinymce_plugin') );
		add_filter('mce_buttons', 		array($this, 'tinymce_button') );
		

	}
	
	public function addResources()
	{
		wp_enqueue_script('postinpost', $this->plugin->url('/resources/postinpost.js'), array(), '1.0');
		wp_enqueue_style( 'postinpost', $this->plugin->url('/resources/postinpost-style.css'), array(), '1.0');
				
	}
	
	public function tinymce_button( $buttons )
	{
		array_push( $buttons, 'separator', self::TINYMCE_PLUGIN_NAME );
		return $buttons;
	}

	public function tinymce_plugin( $plugins )
	{
		$plugins[self::TINYMCE_PLUGIN_NAME] = $this->plugin->url('/resources/tinymce/editor_plugin.js?ver=1.0', __FILE__ );

		return $plugins;
	}
	
	public function templates()
	{
		global $wp_post_types;
	
		
		$post_types = array();
		
	
		foreach($this->plugin->post_types as $option)
		{
			$info = get_post_type_object($option);
			
			if ($info)
			{
				$post_types[] = $info;					
			}
		}

	
		PIP_Utils::view('postinpost-dialogs',array('plugin'=>$this->plugin, 'post_types'=>$post_types));
		
	}
	
	
	public function ajax_postinpost_load_type()
	{
		$ret = array('success'=>false, 'error' => 'Unknown Error');
		
		try
		{

			if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') )
				throw new Exception("You are not authorized to do this.");

			$post_type = isset($_POST['post_type']) ? $_POST['post_type'] : false;
			
			if (!$post_type || !in_array($post_type, $this->plugin->post_types))	
				throw new Exception("There is nothing to load.");
				

			$post_type_obj = get_post_type_object( $post_type );
			
			if (!$post_type_obj->hierarchical)
			{
				$args = array(
							'post_type'		=> $post_type,
							'posts_per_page'	=> -1,
							'order'		=> 'DESC',
							'orderby'		=> 'post_date ID'
						);
					
			}
			else
			{
				
				$args = array(
							'post_type'		=> $post_type,
							'posts_per_page'	=> -1,
							'order'		=> 'ASC',
							'orderby'		=> 'menu_order ID',
							'post_parent'	=> 0
						);
					
			}
			
			$query = new WP_Query($args);
			
			remove_all_filters( 'excerpt_more' );
			
			$output = PIP_Utils::view('postinpost-list',array('query'=>$query, 'post_type'=>$post_type, 'hierarchical'=>$post_type_obj->hierarchical ), true);

			$ret = array('success'=>true, 'output'=>$output);
			
		}
		catch(Exception $ex)
		{
			$ret = array('success'=>false, 'error'=>$ex->getMessage());
		}
		
		echo json_encode($ret);
	
		
		exit;		
	}
	
	public function ajax_postinpost_insert()
	{
		$ret = array('success'=>false, 'error' => 'Unknown Error');
		
		try
		{

			if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') )
				throw new Exception("You are not authorized to do this.");

			$post_type = isset($_POST['post_type']) ? $_POST['post_type'] : false;
			
			$ids = isset($_POST['ids']) ? $_POST['ids'] : false;
			
			if (!$ids)	
				throw new Exception("There is nothing to insert.");

			$insert_as = isset($_POST['insert_as']) ? $_POST['insert_as'] : 'inline';
			$length = isset($_POST['insert_length']) ? $_POST['insert_length'] : 'full';
			
			$ids = explode(',',$ids);
			$ids = array_map('intval',$ids);
			
						
			$query = new WP_Query(array(
								'post__in'		=> $ids,
								'post_type'		=> $post_type,
								'order'		=> 'ASC',
								'orderby'		=> 'title ID'
							));
			
			
			global $post;
			ob_start();
			
			while($query->have_posts()): 
				$query->the_post();

				if ($insert_as == 'shortcode')
				{
					echo "[postinpost id='{$post->ID}' length='{$length}']\n";
				}
				else
				{
					PostInPost::render_the_post($post, $length);
				
											
					
				}
				
			endwhile;
			$output = ob_get_contents();
			ob_end_clean();
			
			$ret = array('success'=>true, 'output'=>$output);

						
		}
		catch(Exception $ex)
		{
			$ret = array('success'=>false, 'error'=>$ex->getMessage());
		}
		
		echo json_encode($ret);
	
		
		exit;		
	}

	
	public function options_page()
	{
		$post_types = get_post_types(array(),'objects');
		
		$skip_post_types = array('attachment','revision','nav_menu_item');
	
		PIP_Utils::view( 'postinpost-options', array('options'=>$this->plugin->options, 'post_types'=>$post_types, 'skip_post_types'=>$skip_post_types ) );
	}
	
	public function options_validate($input = array())
	{
	
		$options = array('postinpost_post_types', 'postinpost_show_in', 'postinpost_inception');

		foreach ( $options as $key ) {
			
			if ( ! isset( $input[$key] ) ) {
				continue;
			}
			
			$option_key = str_replace('postinpost_','',$key);
			
			$this->plugin->options[$option_key] = $input[$key];
			
		}
			
		return $this->plugin->options;
				
	}
	
}




