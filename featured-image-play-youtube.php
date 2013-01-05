<?php
/**
 * Plugin Name: Featured Image as YouTube Player
 * Plugin URI: http://wordpress.stackexchange.com/q/78140/12615
 * Description: Swaps the Featured Image by a YouTube player (click to load/play). 
   Needs a custom field with a YouTube video ID '_yt_id' 
 * Version: 1.1
 * Author: brasofilo
 * Author URI: http://wordpress.stackexchange.com/users/12615/brasofilo
 * Licence: GPLv2 or later
 * Notes: 
   Plugin skeleton from Plugin Class Demo (https://gist.github.com/3804204), by toscho. 
   CSS Overlay images (http://stackoverflow.com/q/403478), by Tim K. 
   Extracting image attributes from Html (http://stackoverflow.com/a/10131137), by hackre.
   Grabbing ID from YouTube URL (http://stackoverflow.com/a/6556662), by hackre.

   ADJUST THE CLASS .centered IN style.css
   width AND height SHOULD BE **HALF** OF THE IMAGE play.png
 */

 
/**
 * Prevent loading this file directly
 * Busted!
 */
!defined( 'ABSPATH' ) AND exit(
                "<pre>Hi there! I'm just part of a plugin, <h1>&iquest;what exactly are you looking for?"
);


// INSTANTIATE
$BL_fi_instance = BL_Featured_Youtube_Thumb::get_instance();


// DEFINE CUSTOM PARAMS
// check $video_params for details
$BL_fi_instance->set_video_params( 
    array(
          'theme'    =>'light'
        , 'controls' => '0'
        , 'showinfo' => '0' 
    ) 
);


// CUSTOM PLUGIN TRANSLATION, NO PO, SORRY FRIENDS
$BL_fi_instance->set_help_text( 'Enter a video ID or URL' );

// ACTION!
if( function_exists( 'add_action' ) )
    add_action(
	'plugins_loaded',
	array ( $BL_fi_instance, 'plugin_setup' )
    );

// THE DUDE
class BL_Featured_Youtube_Thumb
{
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_path = '';

	/**
	 * YouTube parameters. Comments indicate official parameters.
	 * Docs: https://developers.google.com/youtube/player_parameters
         * 
	 * @type string
	 */
	public $video_params = array(
                // Values: 2 (default), 1, and 0
                'autohide' => '1', 
                
                // Values: 0 (default) or 1
                'autoplay' => '1', 

                // Values red (default) and white. White disables modestbranding(!)
                'color' => 'white', 

                // Values: 0, 1 (default), or 2
                'controls' => '2',

                // Values: 0 or 1. Variable default. Full screen
                'fs' => '1', 

                // Values: 1 (default) or 3. Video annotations. 3 = hide
                'iv_load_policy' => '3',

                // Values: 0 (default) or 1.
                'loop' => '0', 

                // Set the parameter value to 1 to prevent the YouTube logo 
                // from displaying in the control bar. No default given.
                'modestbranding' => '1', 

                // Values: 0, 1 (default). Related videos.
                'rel' => '0',

                // Values: 0, 1 (default). Related videos.
                'showinfo' => '1',

                // Values: dark (default) and light.
                'theme' => 'dark',
                 
        );

        /**
         * CUSTOM PLUGIN TRANSLATION, NO PO, SORRY FRIENDS
         */
        public $translation = "Enter a video ID or URL (and we'll grab it).";
        
        
	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @since   2012.09.13
	 * @return  object of this class
	 */
	public static function get_instance()
	{
            NULL === self::$instance and self::$instance = new self;
            return self::$instance;
	}
        
        /**
         * Define parameters of the plugin instance
         * @param type $params
         */
        public function set_video_params( $params )
        {
            $this->video_params = array_merge( $this->video_params, $params );
        }
        
        /**
         * Define parameters of the plugin instance
         * @param type $params
         */
        public function set_help_text( $text )
        {
            $this->translation = $text;
        }
        
	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook plugins_loaded
	 * @since   2012.09.10
	 * @return  void
	 */
	public function plugin_setup()
	{
		$this->plugin_url    = plugins_url( '/', __FILE__ );
		$this->plugin_path   = plugin_dir_path( __FILE__ );

		add_filter( 
			'post_thumbnail_html', 
			array( $this, 'thumbnail_to_youtube' ) , 
			10, 5 
		);
                add_filter(
                        'admin_post_thumbnail_html', 
                        array( $this, 'add_field_to_feat_img' ), 
                        10, 2
                );
                add_action( 
                        'save_post', 
                        array( $this, 'save_postdata' ) 
                );
		add_action( 
			'wp_enqueue_scripts', 
			array( $this, 'print_css' ) 
		);
                foreach( array( 'post.php', 'post-new.php' ) as $hook )
                    add_action( 
                            'admin_print_styles-' . $hook, 
                            array( $this, 'admin_css' )
                    );
	}

	/**
	 * Constructor. Intentionally left empty and public.
	 *
	 * @see plugin_setup()
	 * @since 2012.09.12
	 */
	public function __construct() {}

	/**
	 * Filters post_thumbnail_html
	 * If the post contains a Custom Field ('_yt_id') with a video ID, replacement is done
	 */
	public function thumbnail_to_youtube( $html, $post_id, $thumb_id, $size, $attr )
	{	
		$yt = get_post_meta( $post_id, '_yt_id', true );

		// Post without YT ID, exit earlier
		if( !$yt || empty( $html ) )
			return $html;
                
		// Extract info from the html source
		$atts = $this->get_html_img_attributes( $html );
		
		// Overlay for Featured Image
		$click_to_play = $this->plugin_url . 'img/play.png';
		
		// Render final output
		$output = $this->get_featured_yt_thumbnail( 
                        $html, 
                        $click_to_play, 
                        $atts, 
                        $post_id, 
                        $yt 
                );

		return $output;
	}

        /**
         * Hijack filter and inject custom field into Featured Image meta box
         * 
         * @param type $content
         * @param type $post_id
         * @return type
         */
        public function add_field_to_feat_img( $content, $post_id )
        {
            $nonce = wp_nonce_field( 
                    plugin_basename( __FILE__ ), 
                    '_yt_id_noncename', 
                    true, 
                    false // don't print it out
            );

            $saved = get_post_meta( $post_id, '_yt_id', true );
            if( !$saved )
                $saved = '';
            else
                $saved = esc_attr( $saved );
            
            $extra = "<div id='block-yt'>
                <hr class='hr-yt' />
                <label class='label-yt'>YouTube video ID: </label>
                <input type='text' name='_yt_id' value='{$saved}' />
                <p class='help-yt'>{$this->translation}</p>
                </div>";

            return $content . $extra . $nonce;
        }

        public function save_postdata( $post_id ) 
        {
            // verify if this is an auto save routine. 
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
                return;
            
            // Creating a new post, do nothing
            if ( !isset( $_POST['_yt_id_noncename'] ) ) 
                 return;

            // verify this came from the our screen and with proper authorization
                if ( !wp_verify_nonce( 
                        $_POST['_yt_id_noncename'], 
                        plugin_basename( __FILE__ ) ) 
                    )
                    return;



            if ( isset( $_POST['_yt_id'] ) && '' != $_POST['_yt_id'] )
            {
                $length = strlen( $_POST['_yt_id'] );

                // Not enough chars
                if( $length < 11)
                    return;

                if ( $length == 11 )
                {
                    update_post_meta( $post_id, '_yt_id', $_POST['_yt_id'] );
                    return;
                }
                
                $parse = $this->youtube_id_from_url( $_POST['_yt_id'] );

                if( $parse ) 
                    update_post_meta( $post_id, '_yt_id', $parse );
                
            }
        }
        
        /**
         * Get youtube video ID from URL
         *
         * @author hackre
         * @uri http://stackoverflow.com/a/6556662/1287812
         * @param string $url
         * @return string Youtube video id or FALSE if none found. 
         */
        private function youtube_id_from_url( $url ) 
        {
            $pattern = 
                '%^# Match any youtube URL
                (?:https?://)?  # Optional scheme. Either http or https
                (?:www\.)?      # Optional www subdomain
                (?:             # Group host alternatives
                  youtu\.be/    # Either youtu.be,
                | youtube\.com  # or youtube.com
                  (?:           # Group path alternatives
                    /embed/     # Either /embed/
                  | /v/         # or /v/
                  | /watch\?v=  # or /watch\?v=
                  )             # End path alternatives.
                )               # End host alternatives.
                ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
                $%x'
                ;
            $result = preg_match($pattern, $url, $matches);
            if (false !== $result) {
                return $matches[1];
            }
            return false;
       }
        
	/**
	 * Print frontend CSS
	 */
	public function print_css()
	{
            wp_register_style( 'print-fi-yt', $this->plugin_url . 'css/style.css' );
            wp_enqueue_style( 'print-fi-yt' );
	}
	
	/**
	 * Print frontend CSS
	 */
	public function admin_css()
	{
            wp_register_style( 'print-admin-fi-yt', $this->plugin_url . 'css/admin.css' );
            wp_enqueue_style( 'print-admin-fi-yt' );
	}
	
	/**
	 * Generate the Html for the Featured Image thumbnail.
	 * Prints one javascript line per thumbnail (not sure if the best method)
	 */
	private function get_featured_yt_thumbnail( $html, $img, $atts, $post_id, $yt )
	{
            // Builds a URL query from an associative array
            $player_params = http_build_query( $this->video_params );
            loga($html);
            // Used by JS to replace 'videocontainer-POST-ID'
            $iframe = '<iframe title="" class="youtube-player" type="text/html" width="' 
                                    . $atts['width'] 
                                    . '" height="' 
                                    . $atts['height'] 
                                    . '" src="http://www.youtube.com/embed/' 
                                    . $yt 
                                    . '?'
                                    . $player_params
                                    . '" frameborder="0" allowFullScreen></iframe>';


            $return = '
    <div id="videocontainer-' . $post_id . '">
        <a href="javascript:void(0)" onclick="document.getElementById(\'videocontainer-' 
                    . $post_id
                    . '\').innerHTML = embedCode_'
                    .$post_id.';" class="gallerypic" title="">
            <img src="' 
                    . $atts['src'] 
                    . '" height="' 
                    . $atts['height'] 
                    . '" width="' 
                    . $atts['width'] 
                    . '" alt="' 
                    . $atts['alt'] 
                    . '" class="pic" />
            <span class="zoom-icon centered">
                <img src="' . $img . '" alt="Zoom">
            </span>
        </a>
    </div>
    <script type="text/javascript">var embedCode_' 
        . $post_id 
        . '  = \''
        . $iframe 
        . '\';</script>
            ';

            return $return;
	}
	
	/**
	 * Extract image attributes from Html
	 * @author hackre
	 * @url	   http://stackoverflow.com/a/10131137/1287812
	 */
	private function get_html_img_attributes( $html )
	{
		$xpath = new DOMXPath( @DOMDocument::loadHTML( $html ) );
		$src = $xpath->evaluate( "string(//img/@src)" );
		$alt = $xpath->evaluate( "string(//img/@alt)" );
		$width = $xpath->evaluate( "string(//img/@width)" );
		$height = $xpath->evaluate( "string(//img/@height)" );
		return array( 
				'src' => $src
			,	'alt' => $alt
			, 	'width' => $width
			, 	'height' => $height
			 );
	}
}