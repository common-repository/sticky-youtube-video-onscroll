<?php
/**
 * Base meta box class.
 *
 * @package Syvos
 */
if ( ! class_exists( 'Syvos_Meta_Box' ) ) {
	/**
	 * Class Syvos_Meta_Box
	 */
	class Syvos_Meta_Box {
		/**
		 * Instance
		 *
		 * @var $instance
		 */
		private static $instance;
		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}		
		/**
		 * Hook into the appropriate actions when the class is constructed.
		 */
		public function __construct() {
			if ( is_admin() ) {
				add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
				add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
			}
		}
		/**
		 * Adds the meta box container.
		 */
		public function init_metabox() {
			add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
			add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );
		}
		public function add_metabox() {
			// Get all public posts.
			$post_types = get_post_types(
				array(
					'public' => true,
				)
			);

			// Enable for all posts.
			foreach ( $post_types as $type ) {
				if ( 'attachment' !== $type ) {
					add_meta_box(
						'syvos_plugin_setting',
						esc_html__( 'Enable Sticky Youtube Video', 'sticky-youtube-video-onscroll' ),
						array( $this, 'render_metabox' ),
						$type,
						'side',
						'default'
					);
				}
			}
		}
		public function render_metabox( $post ) {
			$meta = get_post_meta( $post->ID );
			
			$syvos_enable_youtube_sticky = ( isset( $meta['syvos_enable_youtube_sticky'][0] ) &&  'no' === $meta['syvos_enable_youtube_sticky'][0] ) ? 'no' : 'yes';		
			
			wp_nonce_field( 'syvos_control_meta_box', 'syvos_control_meta_box_nonce' ); 

			?>
			<div class="post_meta_extras">
				
				<p>
					<label><input type="checkbox" name="syvos_enable_youtube_sticky" value="yes" <?php checked( $syvos_enable_youtube_sticky, 'yes' ); ?> /><?php esc_html_e( 'Enable', 'sticky-youtube-video-onscroll' ); ?></label>
				</p>			
				
			</div>							
			<?php			
		}
		public function save_metabox( $post_id, $post ) {
			/*
			 * We need to verify this came from the our screen and with proper authorization,
			 * because save_post can be triggered at other times. Add as many nonces, as you
			 * have metaboxes.
			 */
			if ( ! isset( $_POST['syvos_control_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['syvos_control_meta_box_nonce'] ), 'syvos_control_meta_box' ) ) { // Input var okay.
				return $post_id;
			}
			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) { // Input var okay.
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			} else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return $post_id;
				}
			}
			/*
			 * If this is an autosave, our form has not been submitted,
			 * so we don't want to do anything.
			 */
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}
			
			$syvos_enable_youtube_sticky = ( isset( $_POST['syvos_enable_youtube_sticky'] ) && 'yes' === $_POST['syvos_enable_youtube_sticky'] ) ? 'yes' : 'no'; // Input var okay.
			update_post_meta( $post_id, 'syvos_enable_youtube_sticky',  $syvos_enable_youtube_sticky );	

		}
	}
}
/**
 * Kicking this off by calling 'get_instance()' method
 */
Syvos_Meta_Box::get_instance();