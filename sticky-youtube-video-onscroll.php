<?php
/**
 * Plugin Name:       Sticky Youtube Video Onscroll
 * Plugin URI:        https://aarambhathemes.com/sticky-youtube-video-onscroll/
 * Description:       This plugin is perfect to make your YouTube Video sticky on scroll with the play of youtube videos available at your WordPress Posts/pages. Moreover, you can get and manage the video position sticky on the left, right, or center of the window if the user scrolls away from the video viewport.
 * Version:           1.0.2
 * Author:            Aarambha Themes
 * Author URI:        https://aarambhathemes.com/
 * Text Domain:       sticky-youtube-video-onscroll
 * Domain Path:       /languages
 */

if( !defined('ABSPATH')){
	exit;
}

class StickyYoutubeVideoOnscroll{

	public $plugin;

	function __construct(){
		$this->plugin = plugin_basename( __FILE__ );
	}

	public function init(){		
		add_action( 'init', array($this,'constants'));
		add_action( 'init', array( $this, 'includes'));

		/**
		 * generating setting links in plugin list
		 */

		add_filter( "plugin_action_links_$this->plugin", array( $this,'setting_link' ) );
		/**
		 * enqueue scripts and styles
		 */
		add_action('wp_enqueue_scripts', array( $this,'enqueue'));
	}

	public function setting_link( $links ){
		$setting_link = '<a href="options-general.php?page=syvos" >'.esc_html__('Settings','sticky-youtube-video-onscroll').'</a>' ;
		array_push( $links , $setting_link );
		return $links;
	}

	public function constants(){
		defined( 'SYVOS_DIR' ) || define( 'SYVOS_DIR', plugin_dir_path( __FILE__ ) );
		defined( 'SYVOS_URL' ) || define( 'SYVOS_URL', plugin_dir_url( __FILE__ ) );

		defined( 'SYVOS_ADMIN' ) || define( 'SYVOS_ADMIN' , SYVOS_DIR.'admin/' );
		defined( 'SYVOS_INC' ) || define( 'SYVOS_INC', SYVOS_DIR.'includes/' );
	}
	public function includes(){

		require_once SYVOS_INC.'plugin-functions.php';
		/**
		 * Global options
		 */
		require_once SYVOS_ADMIN.'admin-options.php';
		/**
		 * singel page and post options
		 */
		require_once SYVOS_ADMIN.'single-page-post-options.php';
	}

	public static function plugin_activation(){
		flush_rewrite_rules();
	}

	public static function plugin_deactivation(){	
		// Clear up our settings
		$syvos_defiend_options = array('admin_syvos_enable_disable','admin_syvos_width','admin_syvos_height','admin_syvos_video_position');		
		foreach($syvos_defiend_options as $optionName) {
		    delete_option($optionName);
		}

		// delete post meta
		$plugin_post_args 	= array(
								'posts_per_page' => -1,
								'post_type'    => array('page','post')
							);
		$plugin_posts 		= get_posts($plugin_post_args);
		foreach ($plugin_posts as $post) {
			delete_post_meta($post->ID, 'syvos_enable_youtube_sticky');
		}		
		flush_rewrite_rules();
	}	

	public function enqueue(){		
		wp_enqueue_style( 'svyo-style', plugins_url( 'syvo-style.css', __FILE__ ));
		wp_enqueue_script( 'svyo-script', plugins_url( 'assets/js/svyo-script.js', __FILE__ ));	
	}

}	

if( class_exists('StickyYoutubeVideoOnscroll') ){
	$syvos = new StickyYoutubeVideoOnscroll();
	$syvos -> init();
}	
/**
* 
*	Class activation
*/	
register_activation_hook( __FILE__ , array( 'StickyYoutubeVideoOnscroll', 'plugin_activation' ));

/**
 *  Class deactivation
 */
 register_deactivation_hook( __FILE__ , array( 'StickyYoutubeVideoOnscroll' , 'plugin_deactivation' ));
	

