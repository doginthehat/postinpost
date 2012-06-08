<?php  if ( ! defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Post In Post
Plugin URI: https://github.com/doginthehat/postinpost
Description: Import posts in other posts.
Author: Doginthehat
Version: 0.1.4
Author URI: http://doginthehat.com.au/
*/

/*
   Post In Post
   
   Copyright (c) 2012 Dog in the hat

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License, version 2, as 
   published by the Free Software Foundation.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
   
   Acknowledgements
   
   * This plugin structure is inspired by the clean plugin structure from Mute Screamer by Ampt and re-uses its view loading mechanism (released under MIT license).
     (http://ampt.github.com/mute-screamer)
     Thanks for showing the way to clean plugins!
   
   * The TinyMCE editor icon is adapted from the famous FamFamFam Silk icon library by Mark James (http://www.famfamfam.com/lab/icons/silk/) released under the Creative Commons Attribution 2.5 License
   
   * Thanks to Gozer Studio (http://gozer.com.au) for the opportunity to develop this plugin.
   
   * Thanks also to Johan Steen (http://johansteen.se/) for some inspiration taken from his Post Snippets plugin (http://wpstorm.net/wordpress-plugins/post-snippets/).
 */


if ( ! class_exists( 'PostInPost' ) ) :

define( 'PIP_PATH', dirname( __FILE__ ) );

set_include_path( get_include_path() . PATH_SEPARATOR . PIP_PATH . '/libraries' );

require_once 'Utils.php';

class PostInPost {
	
	
	/**
	 * Main instance of this class
	 *
	 * @var object
	 */
	private static $instance = null;

	public static function get() { return self::$instance; }

	
	/**
	 * Base url for plugin resources
	 *
	 * @var string
	 */
	public $base_url = '';

	/**
	 * Plugin options
	 *
	 * @var array
	 */
	public $options = null;
	
	/**
	 * Allowed post types
	 *
	 * @var array
	 */
	public $post_types = null;
	
	/**
	 * Behaviour for inception (infinite nesting)
	 *
	 * @var string
	 */
	public $inception_behaviour = null;
	
	/**
	 * Where to add the tinymce extension
	 *
	 * @var string
	 */
	public $show_in = null;

	

	public function __construct() {

		$success = true;
		
		// Require PHP 5.2.
		if (version_compare(PHP_VERSION, '5.2', '<'))
		{
			add_action( 'admin_notices', array($this, 'php_version_error') );
			$success = false;
		}

		// Require WP 3.3 (could work on old version but who wants to do that kind of testing, really).
		if (version_compare(get_bloginfo('version'), '3.3', '<'))
		{
			add_action( 'admin_notices', array($this, 'wp_version_error') );
			$success = false;
		}

		

		if (!$success)
			return;;

		self::$instance = $this;

		$this->init();
		
		$this->base_url = plugins_url( '/', __FILE__ );

	}
	
	function php_version_error() {
		echo '<div class="error"><p><strong>';
		printf('Error: Post In Post requires PHP version 5.2 or greater.<br/>Your installed PHP version: %1$s', PHP_VERSION);
		echo '</strong></p></div>';
	}

	function wp_version_error() {
		echo '<div class="error"><p><strong>';
		printf('Error: Post In Post requires WordPress version 3.3 or greater.<br/>Your current Wordpress version: %1$s', get_bloginfo('version') );
		echo '</strong></p></div>';
	
	}

	/**
	 * Initialise Post In Post
	 *
	 * @return void
	 */
	private function init() {

		$this->init_options();

		// Load textdomain
		load_plugin_textdomain( 'postinpost', false, PIP_PATH.'/languages' );

		add_shortcode( 'postinpost', array($this,'shortcode_postinpost') );

		// WP Admin?
		if ( is_admin() ) {
			require_once 'postinpost-admin.php';
			new PostInPost_Admin($this);
			
		}
	}
	
	/**
	 * Initialise options
	 *
	 * @return void
	 */
	private function init_options() {

		$options = get_option( 'postinpost_options' );

		if ($options === false || $options == '')
			$options = array();
		
		$default_options = self::default_options();
		
		// Fallback to default options if the options don't exist in
		// the database (kind of like a soft upgrade).
		// Automatic plugin updates don't call register_activation_hook.
		$this->options = array_merge($default_options, $options);
		
		if (!isset($this->options['post_types']) || !is_array($this->options['post_types']))
		{
			$this->options['post_types'] = array();			
		}
				
		$this->post_types = apply_filters('postinpost_post_types', $this->options['post_types']);
		
		if (!isset($this->options['show_in']) || !is_array($this->options['show_in']))
		{
			$this->options['show_in'] = array();			
		}
				
		$this->show_in = apply_filters('postinpost_show_in', $this->options['show_in']);

		$this->inception_behaviour = apply_filters('postinpost_inception_behaviour', $this->options['inception_behaviour']);
		

	}
	
	/**
	 * Default options
	 *
	 * @return array
	 */
	public static function default_options() {

		return array(
			'post_types' => array('page','post'),
			'show_in' => array('page','post'),
			'inception_behaviour' => 'link'
		);
	}
	
	
	/**
	 * url(), shortcut for plugin resource urls
	 *
	 * @return string
	 */
	public function url($path = '')
	{
		return plugins_url( $path , __FILE__ );		
	}
	
	
	public function shortcode_postinpost($attributes)
	{

		global $post;
		
		static $inception = array();
		
		if (count($inception)==0)
		{
			$inception[] = $post->ID;
		}
		
		$current_post = $post;

		ob_start();
		
		$id = isset($attributes['id']) ? $attributes['id'] : false;
		$length = isset($attributes['length']) ? $attributes['length'] : 'full';
		
		if ($id && ($tmp = get_post($id)))
		{
			$post = $tmp;
			
			// thanks LG
			setup_postdata($tmp);
		
			if (in_array($id,$inception))
			{
				//Oh oh, we have inception
				if (defined('WP_DEBUG') && WP_DEBUG == true )
				{
					$top = $inception[count($inception)-1];
					echo "<p><strong>Warning: inception while loading post entry {$id} in entry {$top}</strong></p>";					
				}
				
				if ($this->inception_behaviour=='link')
				{
					?>
					<p><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', get_template() ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></p>
					<?php
				}
				elseif ($this->inception_behaviour=='ignore')
				{
					//duh
				}
				
			}
			else
			{
				$inception[] = $id;				
				
				self::render_the_post($post, $length);
								
				array_pop($inception);
			}
		
			
				

		}
		else if (defined('WP_DEBUG') && WP_DEBUG == true ){
			echo "<p><strong>Warning: missing post in post entry ({$id})</strong></p>";
		}
		
			
		$content = ob_get_contents();
		ob_end_clean();

		array_pop($inception);
		
		$post = $current_post;
		setup_postdata($current_post);
		
		
		return $content;
		
	}

	
	public static function render_the_post($post, $length)
	{
		// cheap cache
		static $override_template = null;
		
		if ($override_template===null)
			$override_template = locate_template('postinpost-item.php', false );
		

		if ($override_template)
			include($override_template);
		else
			PIP_Utils::view('postinpost-render',array('post'=>$post, 'length'=>$length ));
	}
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {

	add_action( 'init', create_function( '','new PostInPost();' ) );
}


endif;


