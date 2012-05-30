<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Utils functions extracted from Mute Screamer utils class
 */
class PIP_Utils {

	/**
	 * Load a template file
	 *
	 * @return void|string
	 */
	public static function view( $view, $vars = array(), $return = false ) {
		$found = false;

		// Look in Post In Post views and the current Wordpress theme directories
		for ( $i = 1; $i < 3; $i++ ) {
			$path = ($i % 2) ? PIP_PATH . '/views/' : TEMPLATEPATH . '/';
			$view_path = $path . $view . '.php';

			// Does the file exist?
			if ( file_exists( $view_path ) ) {
				$found = true;
				break;
			}
		}

		if ( $found === true ) {
			extract( $vars );
			ob_start();

			include( $view_path );

			$output = ob_get_contents();
			@ob_end_clean();

			// Return the data if requested
			if ( $return === true ) {
				return $output;
			}
			
			echo $output;

		} else if ( defined( 'WP_DEBUG' ) && WP_DEBUG == true ) {
			trigger_error( __( 'Unable to load the requested view.', 'postinpost' ), E_USER_ERROR );
		}
	}

	/**
	 * Create pagination links
	 *
	 * @return string
	 */
	public static function pagination( $current_page = 1, $total_pages = 0, $per_page = 0, $count = 0 )
	{
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'prev_text' => __( 'previous-arrow', 'postinpost' ),
			'next_text' => __( 'next-arrow', 'postinpost' ),
			'total' => $total_pages,
			'current' => $current_page,
		) );

		if ( !$page_links ) {
			return '';
		}

		$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', 'postinpost' ) . '</span>%s',
			number_format_i18n( ( $current_page - 1 ) * $per_page + 1 ),
			number_format_i18n( min( $current_page * $per_page, $count ) ),
			number_format_i18n( $count ),
			$page_links
		);

		return "<div class='tablenav-pages'>{$page_links_text}</div>";
	}


	/**
	 * Fetch item from the GET array
	 *
	 * @param string
	 * @return mixed
	 */
	public static function get( $index = '' ) {
		return self::_fetch_from_array( $_GET, $index );
	}

	/**
	 * Fetch item from the POST array
	 *
	 * @param string
	 * @return mixed
	 */
	public static function post( $index = '' ) {
		return self::_fetch_from_array( $_POST, $index );
	}

	/**
	 * Fetch item from the SERVER array
	 *
	 * @param string
	 * @return mixed
	 */
	public static function server( $index = '' ) {
		return self::_fetch_from_array( $_SERVER, $index );
	}

	/**
	 * Fetch items from global arrays
	 *
	 * @param array
	 * @param string
	 * @return mixed
	 */
	private static function _fetch_from_array( $array, $index = '' ) {
		if ( ! isset( $array[$index] ) )
			return false;

		return $array[$index];
	}

	/**
	 * Is the current page post.php?
	 *
	 * @return boolean
	 */
	public static function is_post_edit() {
		return ( strpos( $_SERVER['REQUEST_URI'], 'post.php' ) !== false );
	}
}
