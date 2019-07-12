<?php
/**
 * Plugin Name: Access Demo Importer
 * Plugin URI: https://wordpress.org/plugins/access-demo-importer
 * Description: The plugin is used for importing demos on the themes.
 * Version: 1.0.0
 * Author: AccessPress Themes
 * Author URI:  https://accesspressthemes.com/
 * Text Domain: access-demo-importer
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die();
}

define( 'ADI_VERSION', '1.0.0' );

define( 'ADI_FILE', __FILE__ );
define( 'ADI_PLUGIN_BASENAME', plugin_basename( ADI_FILE ) );
define( 'ADI_PATH', plugin_dir_path( ADI_FILE ) );
define( 'ADI_URL', plugins_url( '/', ADI_FILE ) );

define( 'ADI_ASSETS_URL', ADI_URL . 'inc/assets/' );


if ( !class_exists( 'Access_Demo_Importer' ) ) {

    /**
     * Sets up and initializes the plugin.
     */
    class Access_Demo_Importer {

      

        /**
         * A reference to an instance of this class.
         *
         * @since  1.0.0
         * @access private
         * @var    object
         */
        private static $instance = null;

        /**
         * Plugin version
         *
         * @var string
         */
        private $version = ADI_VERSION;

        /**
         * Returns the instance.
         *
         * @since  1.0.0
         * @access public
         * @return object
         */
        public static function get_instance() {
            // If the single instance hasn't been set, set it now.
            if ( null == self::$instance ) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * Sets up needed actions/filters for the plugin to initialize.
         *
         * @since 1.0.0
         * @access public
         * @return void
         */
        public function __construct() {


            // Load translation files
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

            // Load necessary files.
            add_action( 'plugins_loaded', array( $this, 'init' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
            add_action('adi_display_demos',array($this,'adi_display_demos') );
            add_action( 'admin_menu', array( $this, 'adi_register_menu' ) );
        }

        /**
         * Loads the translation files.
         *
         * @since 1.0.0
         * @access public
         * @return void
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain( 'access-demo-importer', false, basename( dirname( __FILE__ ) ) . '/languages' );

        }

        /**
         * Returns plugin version
         *
         * @return string
         */
        public function get_version() {
            return $this->version;
        }

        /**
         * Manually init required modules.
         *
         * @return void
         */
        public function init() {

            require( ADI_PATH . 'inc/demo-functions.php' );
            
        }

        /**
         * Load scripts
         *
         */
        public static function scripts( $hook_suffix ) {

            if ( ('appearance_page_demo-importer' == $hook_suffix) || ('appearance_page_welcome-page' == $hook_suffix) ) {

                // CSS
                wp_enqueue_style( 'adi-demos-style', ADI_ASSETS_URL. 'css/demo-styles.css' );

                // JS
                wp_enqueue_script( 'adi-demos-js', ADI_ASSETS_URL. 'js/demos.js', array( 'jquery', 'wp-util', 'updates' ), ADI_VERSION, true );

                wp_localize_script( 'adi-demos-js', 'accessLoc', array(
                    'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
                    'demo_data_nonce'           => wp_create_nonce( 'get-demo-data' ),
                    'adi_import_data_nonce'     => wp_create_nonce( 'adi_import_data_nonce' ),
                    'content_importing_error'   => esc_html__( 'There was a problem during the importing process resulting in the following error from your server:', 'access-demo-importer' ),
                    'button_activating'         => esc_html__( 'Activating', 'access-demo-importer' ) . '&hellip;',
                    'button_active'             => esc_html__( 'Active', 'access-demo-importer' ),
                    'button_activated'          => esc_html__( 'Activated', 'access-demo-importer' ),
                ) );

            }

        }


        /*
         *  Display the available demos
         */

        function adi_display_demos() {

          $demos = ADI_Demos::get_demos_data();
          
          $prev_text    = esc_html__('Preview','access-demo-importer');
          $install_text = esc_html__('Import','access-demo-importer');
        
        ?>
        <div class="demos-wrapper clearfix">
            <div class="demos-top-title-wrapp">
                <p><?php esc_html_e('Choose the template you like to start with and publish your website within a moment.') ?></p>
            </div>
        <?php 
            if( empty($demos)){
                return;
            }
          foreach( $demos as $key => $demo ){
          ?>    
          <div class="demo">
            <div class="img-wrapp">
                <img src="<?php echo esc_url($demo['screen']);?>">
            </div>
            <div class="demo-btn-wrapp">
               <h4 class="demo-title"><?php echo esc_html($key); ?></h4> 
               <div class="buttons-wrapp">
                    <a href="#" class="button install-btn install-demo-btn-step adi-open-popup" data-demo-id="<?php echo esc_attr($key); ?>"><?php echo $install_text; ?></a>
                    <a href="<?php echo esc_url($demo['preview_url']);?>" class="button preview-btn button-primary" target="_blank"><?php echo esc_html($prev_text); ?></a>
               </div>
            </div>
          </div>
        <?php }
        echo '</div>';

        }

        /**
        * Register menu
        *
        */
        public function adi_register_menu() {
             $title = esc_html__('Install Demos','access-demo-importer');
            add_theme_page( $title, $title , 'edit_theme_options', 'demo-importer', array( $this, 'adi_display_demos' ));
        }



    }

}

if ( !function_exists( 'access_demo_instance' ) ) {

    /**
     * Returns instanse of the plugin class.
     *
     * @since  1.0.0
     * @return object
     */
    function access_demo_instance() {
        return Access_Demo_Importer::get_instance();
    }

}

access_demo_instance();
