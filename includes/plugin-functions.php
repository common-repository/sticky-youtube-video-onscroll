<?php
/**
 * @package function
 */
class SvyoOption{

	public function __construct(){
		global $block_content, $block;
	}

	public function register(){	
		
		add_filter( 'render_block', array( $this,'custom_youtube_block'), 11 , 2);		
		add_action( 'wp_head',array($this,'youtube_custom_scripts'));
		add_action( 'wp_enqueue_scripts', array( $this,'svyo_custom_inline_style'));
		
	}	

	public function custom_youtube_block( $block_content, $block ) {

		$admin_syvos_width 				= esc_attr( get_option( 'admin_syvos_width',280 ));
		$admin_syvos_height 			= esc_attr( get_option( 'admin_syvos_height',160 ));
		$front_syvos_syvos_width 		= $admin_syvos_width + absint( 470 );
		$front_syvos_video_height 		= $admin_syvos_height + absint( 262 );		
		$syvos_enable_youtube_sticky 	= esc_attr(get_post_meta( get_the_ID(), 'syvos_enable_youtube_sticky', TRUE ));
		
	  	// use blockName to only affect the desired block 'core-embed/youtube', main

	  	if( $syvos_enable_youtube_sticky == 'no' ){
	  		return $block_content;
	  	}

	  	if( "core/embed" !== $block['blockName'] ) {
	    	return $block_content;
	  	}

	  	if( 'video' !== $block['attrs']['type'] && 'youtube' !== $block['attrs']['providerNameSlug']) {
	  		return $block_content;
	  	}

	  	$url = $block['attrs']['url'];
	  	$parts = explode('?v=', $url);
	  	
	  	$youtube_video_id = $this -> get_YoutubeVideoIdFromUrl( $url );	

		$position_class = '';
		$admin_syvos_video_position 	= get_option( 'admin_syvos_video_position',3 );
		if( $admin_syvos_video_position == 1 ){
			$position_class = 'left-position-class';
		}elseif( $admin_syvos_video_position == 2 ){
			$position_class = 'center-position-class';
		}else{
			$position_class = 'right-positon-class';
		}

	  	$content = '';
	  	ob_start();
	  		$content .= '<section class="videowrapper ytvideo '.esc_attr($position_class).' ">';
	  	  	$content .= '<a href="javascript:void(0);" class="close-button"></a>';
	  	  	$content .=	'<i class="fa fa-arrows-alt" aria-hidden="true"></i>';
	  		$content .= '<div class="gradient-overlay"></div>';	
	  		$content .='<iframe width="'.esc_attr( $front_syvos_syvos_width ).'" height="'.esc_attr($front_syvos_video_height).'" src="https://www.youtube.com/embed/'.esc_attr($youtube_video_id).'?enablejsapi=1&amp;?rel=0&amp;controls=1&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
	  		$content .= '</section>';
	  		$content .= ob_get_clean();
	  		return $content;
	}

	public function get_YoutubeVideoIdFromUrl( $url = '' ){
		global $post;
	    $regs = array();
	    $id = '';
	    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match); 
	    $id = isset($match[1]) ? $match[1]: '';
	    return $id;
	}

	public function svyo_custom_inline_style(){
		wp_enqueue_style( 'svyo-style', plugins_url( 'syvo-style.css', __FILE__ ));

		$admin_syvos_width 				= esc_attr( get_option( 'admin_syvos_width',280 ));
		$admin_syvos_height 			= esc_attr( get_option( 'admin_syvos_height',160 ));		
		$admin_syvos_cross_position 	= $admin_syvos_height - absint( 5 );

		$syvos_custom_css = ".ytvideo .is-sticky, .is-sticky{ 
								width : {$admin_syvos_width}px;
								height : {$admin_syvos_height}px;
	                		}
	                		.close-button{
	                			bottom : {$admin_syvos_cross_position}px;
	                		}";

        wp_add_inline_style( 'svyo-style', $syvos_custom_css );
	}

	public function youtube_custom_scripts(){
		$admin_syvos_enable_disable 	= esc_attr( get_option( 'admin_syvos_enable_disable',1 ) );

		if( $admin_syvos_enable_disable == 1 ){
			?>
			<script type="text/javascript">			

				var ytIframeList, videoList, currentPlayer, closeButton, gradientOverlay, fullScreenIcon;
				var inViewPortBol 	= false;
				var ytIframeIdList 	= [];
				var ytVideoId 		= [];
				var ytPlayerId 		= [];
				
				var events = new Array("ended", "pause", "playing");

				document.addEventListener('DOMContentLoaded', function () {

				    /*Adding Youtube Iframe API to body*/
				    var youTubeVideoTag = document.createElement('script');
				    youTubeVideoTag.src = "//www.youtube.com/iframe_api";
				    var firstScriptTag 	= document.getElementsByTagName('script')[0];
				    document.body.appendChild(youTubeVideoTag, firstScriptTag);

				    /*Getting all the iframe from the Page and finding out valid URL and ID. Then creating instance of players*/
				    ytIframeList = document.getElementsByTagName("iframe");				  

				    for (i = 0; i < ytIframeList.length; i++) {
				        if (new RegExp("\\b" + "enablejsapi" + "\\b").test(ytIframeList[i].src)) {
				            var url = ytIframeList[i].src.replace(/(>|<)/gi, '').split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);

				            if (url[2] !== undefined) {
				                ID = url[2].split(/[^0-9a-z_\-]/i);
				                ID = ID[0];
				                ytIframeIdList.push(ID);
				                ytIframeList[i].id = "iframe" + i;
				                ytVideoId.push("ytVideoId" + i);
				                ytVideoId[i] = document.getElementById(ytIframeList[i].id);
				                ytPlayerId.push("player" + i);

				            }
				        }
				    }
				    closeButton 	= document.querySelector("a.close-button");
				    gradientOverlay = document.querySelector(".gradient-overlay");
				    fullScreenIcon 	= document.querySelector("i.fa.fa-arrows-alt");
				    fullScreenPlay();
				});

				window.onYouTubeIframeAPIReady = function () {
				    for (i = 0; i < ytIframeIdList.length; i++) {
				        ytPlayerId[i] = new YT.Player(ytIframeList[i].id, {				        	
				            events: {
				                "onStateChange": onPlayerStateChange
				            }
				        });
				    }
				};

				function onPlayerStateChange(event) {
				    /*Play Rules*/
				    for (i = 0; i < ytPlayerId.length; i++) {
				        if (ytPlayerId[i].getPlayerState() === 1) {
				            currentPlayer = ytVideoId[i];	        
				            ytVideoId[i].classList.remove("is-paused");
				            ytVideoId[i].classList.add("is-playing");
				            break;
				        }
				    }
				    for (i = 0; i < ytVideoId.length; i++) {
				        if (currentPlayer == ytVideoId[i]) {
				            continue;
				        }
				        ytVideoId[i].classList.remove("is-playing");
				        ytVideoId[i].classList.add("is-paused");
				    }
				    /*Pause Rules*/
				    for (i = 0; i < ytPlayerId.length; i++) {
				        if (ytPlayerId[i].getPlayerState() === 2) {
				            ytVideoId[i].classList.add("is-paused");
				            ytVideoId[i].classList.remove("is-playing");
				            ytPlayerId[i].pauseVideo();
				        }
				    }
				    /*Sticky Rules*/
				    for (i = 0; i < ytPlayerId.length; i++) {
				        if (ytVideoId[i].classList.contains("is-sticky")) {
				            ytPlayerId[i].pauseVideo();
				            ytVideoId[i].classList.remove("is-sticky");
				            fullScreenIcon.style.display = "none";
				        }
				    }
				    /*End Rule*/
				    for (i = 0; i < ytPlayerId.length; i++) {
				        if (ytPlayerId[i].getPlayerState() === 0) {
				            ytVideoId[i].classList.remove("is-playing");
				            ytVideoId[i].classList.remove("is-paused");
				        }
				    }
				    videohandler();
				}

				function videohandler() {
				    if (currentPlayer) {
				        if (closeButton) {
				        	closeFloatVideo();
				            closeButton.addEventListener("click", function (e) {
				            	
				                if (currentPlayer.classList.contains("is-sticky")) {	
				                    currentPlayer.classList.remove("is-sticky");
				                    closeFloatVideo();
				                    for (i = 0; i < ytVideoId.length; i++) {
				                        if (currentPlayer == ytVideoId[i]) {
				                            ytPlayerId[i].pauseVideo();		                           
				                        }
				                    }		                    
				                } else {
				                    for (i = 0; i < ytVideoId.length; i++) {
				                        if (currentPlayer != ytVideoId[i]) {
				                            ytVideoId[i].classList.remove("is-sticky");
				                            closeFloatVideo();
				                        }
				                    }		                   
				                }
				            });
				        }
				    }
				}

				window.addEventListener('scroll', function () {
				    inViewPortBol = inViewPort();
				  //  console.log( inViewPortBol );
				    if (currentPlayer) {
				        if (!inViewPortBol && currentPlayer.classList.contains("is-playing")) {
				            for (i = 0; i < ytVideoId.length; i++) {
				                if (currentPlayer != ytVideoId[i]) {
				                    ytVideoId[i].classList.remove("is-sticky");
				                }
				            }		           
				            currentPlayer.classList.add("is-sticky");
				            openFloatVideo();
				        } else {
				            if (currentPlayer.classList.contains("is-sticky")) {
				                currentPlayer.classList.remove("is-sticky");
				                closeFloatVideo();
				            }
				        }
				    }
				});

				function fullScreenPlay() {
				    if (fullScreenIcon) {
				        fullScreenIcon.addEventListener("click", function () {
				            if (currentPlayer.requestFullscreen) {
				                currentPlayer.requestFullscreen();
				            } else if (currentPlayer.msRequestFullscreen) {
				                currentPlayer.msRequestFullscreen();
				            } else if (currentPlayer.mozRequestFullScreen) {
				                currentPlayer.mozRequestFullScreen();
				            } else if (currentPlayer.webkitRequestFullscreen) {
				                currentPlayer.webkitRequestFullscreen();
				            }
				        });
				    }
				}

				function inViewPort() {
				    if (currentPlayer) {
				        var videoParentLocal = currentPlayer.parentElement.getBoundingClientRect();
				    //    console.log( videoParentLocal );				    	

				        return videoParentLocal.bottom > 0 &&
				            videoParentLocal.right > 0 &&
				            videoParentLocal.left < (window.innerWidth || document.documentElement.clientWidth) &&
				            videoParentLocal.top < (window.innerHeight || document.documentElement.clientHeight);
				    }
				}

				function openFloatVideo() {
				    closeButton.style.display 		= "block";
				    closeButton.style.zIndex 		= 99999;
				    gradientOverlay.style.display 	= "block";
				    fullScreenIcon.style.display 	= "block";
				}

				function closeFloatVideo() {
				    closeButton.style.display 		= "none";
				    closeButton.style.zIndex 		= 99999;
				    gradientOverlay.style.display 	= "none";
				    fullScreenIcon.style.display 	= "none";
				}
			</script>    
		    <?php
		}
		
	}

}

if( class_exists('SvyoOption') ){
	$SvyoOption = new SvyoOption();
	$SvyoOption	-> register();
}
