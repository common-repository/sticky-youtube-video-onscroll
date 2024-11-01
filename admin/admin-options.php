<?php
/**
 * @package Global options  * 
 */

class SyvosGlobaloption{
	public function register(){
		add_action('admin_menu', array($this, 'admin_options_page' ));
		add_action( 'admin_init',array($this,'syvos_data_settings'));
	}

	public function admin_options_page(){
		add_options_page(
			esc_html__( 'Sticky Youtube Video On Scroll','sticky-youtube-video-onscroll' ),
			esc_html__( 'Sticky Youtube Video on Scroll','sticky-youtube-video-onscroll' ),
			'manage_options', 
			'syvos',
			array($this,'syvos_admin_settings'), 
			2
		);
	}

	public function syvos_admin_settings(){
		if( !current_user_can( 'manage_options' ) ){
			return;
		}
		echo '<div class="wrap">
			<h1>'.esc_html__('Sticky Youtube Video On Scroll',"sticky-youtube-video-onscroll").'</h1>
			<form method="post" action="options.php">';
	 
			settings_fields( 'syvos_settings' );
			do_settings_sections( 'syvos' );
			submit_button(esc_html__( 'Save Changes','sticky-youtube-video-onscroll' ));

		echo '</form></div>';

	}

	public function syvos_data_settings(){
		add_settings_section(
			'syvos_settings_section_id',
			esc_html__('Settings',"sticky-youtube-video-onscroll"), 
			'', 
			'syvos' 
		);

		$admin_settings = array(
			'syvos_enable_disable'	=> esc_html__('Enable/Disable','sticky-youtube-video-onscroll'),	
			'syvos_width'			=> esc_html__('Width (px)','sticky-youtube-video-onscroll'),
			'syvos_height'			=> esc_html__('Height (px)','sticky-youtube-video-onscroll'),
			'syvos_video_position'	=> esc_html__('Position','sticky-youtube-video-onscroll'),
		);	
	
		foreach ($admin_settings as $key => $value) {
			add_settings_field( 
				'admin_'.$key,
				$value,
				array($this,'admin_'.$key.'_callback'),
				'syvos',
				'syvos_settings_section_id',
				array( 
					'label_for' => $key,
					'class' => 'syvos-row',
					)
			);
			register_setting('syvos_settings','admin_'.$key);
		}

	}
	public function admin_syvos_enable_disable_callback(){
		$admin_syvos_enable_disable = get_option( 'admin_syvos_enable_disable',1 );	
		?>
		<div class="admin-enable-disable-options">
			
			<div id="admin-enable-disable-mode-center">
				<input type="radio" name="admin_syvos_enable_disable" value="2" <?php checked(2, esc_attr( $admin_syvos_enable_disable ), true); ?> class="admin-disable" />		
				<?php echo esc_html__( 'Disable', 'sticky-youtube-video-onscroll' ); ?>
			</div><br>

			<div id="admin-enable">
				<input type="radio"  name="admin_syvos_enable_disable" value="1" <?php checked(1, esc_attr($admin_syvos_enable_disable), true); ?> class="admin-enable" />
				<?php echo esc_html__( 'Enable', 'sticky-youtube-video-onscroll' ); ?>				
			</div><br>		

		</div>				
		<?php
	}


	public function admin_syvos_height_callback(){
		$admin_syvos_height = get_option( 'admin_syvos_height',160 );	
 
		printf(
			'<input type="text" id="admin_syvos_height" name="admin_syvos_height" value="%s" />',
			esc_attr( $admin_syvos_height )
		);
	}

	public function admin_syvos_width_callback(){

		$admin_syvos_width = get_option( 'admin_syvos_width', 280 );
		?>
		<div class="video-width">			
			<input type="text" id="admin_syvos_width" name="admin_syvos_width" value="<?php echo esc_attr( $admin_syvos_width ); ?>" />
		</div>
		<?php

	}

	public function admin_syvos_video_position_callback(){
		$admin_syvos_video_position = get_option( 'admin_syvos_video_position',3 );
		?>	
		<div class="admin-display-options">
			<div id="admin-display-mode-left">
				<input type="radio"  name="admin_syvos_video_position" value="1" <?php checked(1, esc_attr($admin_syvos_video_position), true); ?> class="admin-display-left" />
				<?php echo esc_html__( 'Left', 'sticky-youtube-video-onscroll' ); ?>				
			</div><br>

			<div id="admin-display-mode-center">
				<input type="radio" name="admin_syvos_video_position" value="2" <?php checked(2, esc_attr($admin_syvos_video_position ), true); ?> class="admin-display-center" />		
				<?php echo esc_html__( 'Center', 'sticky-youtube-video-onscroll' ); ?>
			</div><br>

			<div id="admin-display-mode-right">
				<input type="radio" name="admin_syvos_video_position" value="3" <?php checked(3, esc_attr($admin_syvos_video_position) , true); ?> class="admin-display-right" />		
				<?php echo esc_html__( 'Right', 'sticky-youtube-video-onscroll' ); ?>
			</div>			
		</div>				
		<?php
	}
	

}

if( class_exists('SyvosGlobaloption') ){
	$SyvoGlobaloption = new SyvosGlobaloption();
	$SyvoGlobaloption -> register();
} 