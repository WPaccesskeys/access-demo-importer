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
            add_action( 'admin_footer', array( $this, 'adi_display_demo_iframe') );
            add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
            add_action('adi_display_demos',array($this,'adi_display_demos') );
            add_action( 'admin_menu', array( $this, 'adi_register_menu' ) );

            add_filter( 'pt-ocdi/import_files', array( $this, 'adi_ocdi_import_files') );
            add_action( 'pt-ocdi/after_import', array( $this, 'adi_ocdi_after_import') );
            
            

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

            require_once( ADI_PATH .'/inc/importers/class-helpers.php' );
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
                    'plugin_activate_info'      => esc_html__( 'Please install & activate all plugins first', 'access-demo-importer' ),
                ) );

            }

        }


        /*
         *  Display the available demos
         */

        function adi_display_demos() {

            $demos = ADI_Demos::get_demos_data();
            if( empty($demos)){
                esc_html_e('No demos are configured for this theme, please contact the theme author','access-demo-importer');
                return;
            }


            $prev_text      = esc_html__('Preview','access-demo-importer');
            $install_text   = esc_html__('Import','access-demo-importer');
            $pro_text       = esc_html__('Pro','access-demo-importer');
            $pro_upgrage    = esc_html__('Buy Now','access-demo-importer');

            ?>
            <div class="demos-wrapper clearfix">
                <div class="demos-top-title-wrapp">
                    <h3><?php esc_html_e('Choose one of the starter sites (demos) below, checkout preview and once you decide to install - go for it!','access-demo-importer'); ?></h3>
                    <p><?php esc_html_e('However please consider the following points before installing any demos: ','access-demo-importer'); ?></p>
                    <div class="info-wrapper">
                        <ul>
                            <li><?php esc_html_e('If your site already has content or is already in use, importing one of these demos is not suggested. The demo content and your existing content might mix up.','access-demo-importer'); ?></li>
                            <li><?php esc_html_e('Import demo on a FRESH WordPress installation to experience the exact demo you\'ve seen on our demo pages.','access-demo-importer'); ?> </li>
                            <li> <?php esc_html_e('Installing demo, will also install all the required plugins and activate them for you.','access-demo-importer'); ?> </li>
                            <li><?php esc_html_e('None of your existing content (page, post etc) will be deleted. ','access-demo-importer'); ?></li>
                            <li><?php esc_html_e('Demo installation will take some time, have patience. Reset the demo installation (option given at the bottom of this page) to start a new demo installation.','access-demo-importer'); ?> </li>

                        </ul>
                    </div>

                </div>

                <div class="demo-content-wrapper">
                    <?php 

                    foreach( $demos as $key => $demo ){ 

                        if( $key != 'premium_demos' ){
                            $demo_name = $demo['demo_name'];
                            ?>

                            <div class="demo">
                                <div class="img-wrapp">
                                    <a href="<?php echo esc_url($demo['preview_url']);?>" class="adi-preview-url">
                                        <span class="preview-text"><?php echo esc_html($prev_text); ?></span>
                                        <img src="<?php echo esc_url($demo['screen']);?>">
                                    </a>
                                </div>
                                <div class="demo-btn-wrapp">
                                    <h4 class="demo-title"><?php echo esc_html($demo_name); ?></h4> 
                                    <div class="buttons-wrapp">
                                        <a href="#" class="button install-btn install-demo-btn-step adi-open-popup" data-demo-id="<?php echo esc_attr($key); ?>"><?php echo $install_text; ?></a>
                                        <a href="<?php echo esc_url($demo['preview_url']);?>" class="button preview-btn button-primary" target="_blank"><?php echo esc_html($prev_text); ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php } }

//pro demos 
                        $pro_demos = isset($demos['premium_demos']) ? $demos['premium_demos'] : '';

                        if( $pro_demos ):

                            foreach( $pro_demos as $pro_demo ){  ?>

                                <div class="demo pro-demo">
                                    <div class="img-wrapp">
                                        <a href="<?php echo esc_url($pro_demo['preview_url']);?>">
                                            <span class="preview-text"><?php echo esc_html($prev_text); ?></span>
                                            <img src="<?php echo esc_url($pro_demo['screen']);?>">
                                        </a>
                                    </div>
                                    <div class="demo-btn-wrapp">
                                        <h4 class="demo-title"><?php echo esc_html($pro_demo['demo_name']); ?></h4> 
                                        <div class="buttons-wrapp">
                                            <a href="<?php echo esc_url($pro_demo['upgrade_url']);?>" class="button " data-demo-id="<?php echo esc_attr($key); ?>" target="_blank"><?php echo $pro_upgrage; ?></a>
                                            <a href="<?php echo esc_url($pro_demo['preview_url']);?>" class="button preview-btn button-primary" target="_blank"><?php echo esc_html($prev_text); ?></a>
                                        </div>
                                    </div>
                                    <span class="pro-text"><?php echo esc_html($pro_text); ?></span>
                                </div>

                            <?php }
                        endif; 
                        ?>

                    </div>
                    
                </div>
                <?php $this->adi_demo_data_reset(); 
            }

            public function adi_display_demo_iframe(){ ?>
                <div  class="adi-popup-preview import-php hidden">                   
                    <div class="close-popup">
                        <i class="dashicons dashicons-no-alt"></i>
                        <span class="prev-close-info"><?php esc_html_e('Close Preview','access-demo-importer'); ?></span>
                    </div>
                    <div class="updating-message"></div>
                    <iframe id="adi-popup-preview" src="" width="100%" height="100%"></iframe>
                </div>

                <div class="adi-demo-confirm-message">
                    <div class="adi-msg-wrapp">
                        <div class="conf-msg">
                            <?php esc_html_e('Are you sure? This will reset your databse and the process can\'t  be reversed.','access-demo-importer' ); ?>
                        </div>
                        <div class="adi-confirm">
                            <a href="#" class="adi-reset-confrm"><?php esc_html_e('Confirm','access-demo-importer'); ?></a>
                            <a href="#" class="adi-reset-cancel"><?php esc_html_e('Cancel','access-demo-importer'); ?></a>
                        </div>
                    </div>
                </div>

                <?php
            }


            //database reset
            public function adi_demo_data_reset(){
                ?>
                <div class="adi-reset-database-wrapper">
                    <div class="inner-wrapp">
                        <a href="javascript:void(0)" class="button button-primary adi-db-reset">
                            <?php esc_html_e('Reset Database','access-demo-importer'); ?>
                        </a>
                        <?php  esc_html_e( 'Reset Your Site ? This will reset your site to default again. ', 'access-demo-importer' ); ?>
                    </div>
                </div>
                <?php
            }


     //compatible for OCDI 
            public function adi_ocdi_import_files() {

                $demos = ADI_Demos::get_demos_data();
                if( empty($demos)){
                    return;
                }

                $demos_data = array();
                foreach( $demos as $demo ){

                    $screen         = isset( $demo['screen'] )            ? $demo['screen']           : '';
                    $demo_name      = isset( $demo['demo_name'] )         ? $demo['demo_name']        : '';
                    $preview_url    = isset( $demo['preview_url'] )       ? $demo['preview_url']      : '';
                    $xml_file       = isset( $demo['xml_file'] )          ? $demo['xml_file']         : '';
                    $theme_settings = isset( $demo['theme_settings'] )    ? $demo['theme_settings']   : '';
                    $widgets_file   = isset( $demo['widgets_file'] )      ? $demo['widgets_file']     : '';
                    $rev_slider     = isset( $demo['rev_slider'] )        ? $demo['rev_slider']       : '';
                    $import_redux   = isset( $demo['import_redux'] )      ? $demo['import_redux']     : '';
                    $redux_array    = '';
                    if( $import_redux ){
                        $option_filepath    = isset( $import_redux['file_url'] ) ? $import_redux['file_url'] : '';
                        $option_name        = isset( $import_redux['option_name'] ) ? $import_redux['option_name'] : '';

                        $redux_array =  array(array(
                            'file_url'    => $option_filepath,
                            'option_name' => $option_name,
                        ));
                    }

                    $demos_data[] =
                    array(
                        'import_file_name'           => $demo_name,
                        'import_file_url'            => $xml_file,
                        'import_widget_file_url'     => $widgets_file,
                        'import_customizer_file_url' => $theme_settings,
                        'import_redux'               => $redux_array,
                        'import_preview_image_url'   => $screen,
                        'preview_url'                => $preview_url,
                    );


                    if( $import_redux ){
                        $demos_data['import_redux']  = $redux_array;
                    }



                }
                return $demos_data;

            }

            public function adi_ocdi_after_import( $selected_import ) {

                $demos = ADI_Demos::get_demos_data();
                if( empty($demos)){
                    return;
                }

                foreach( $demos as $demo ){
                    $demo_name       = isset( $demo['demo_name'] )         ? $demo['demo_name']        : '';
                    $menus           = isset( $demo['menus'] )              ? $demo['menus']             : '';
                    $home_title      = isset( $demo['home_title'] )         ? $demo['home_title']        : '';

                    if( $selected_import['import_file_name'] == $demo_name ){

                        foreach( $menus as $key => $menu ){
                            $main_menu = get_term_by( 'name', $menus, 'nav_menu' );

                            set_theme_mod( 'nav_menu_locations', array(
                                $key => $main_menu->term_id,
                            ));    
                        }

                        $front_page_id = get_page_by_title( $home_title );

                        update_option( 'show_on_front', 'page' );
                        update_option( 'page_on_front', $front_page_id->ID );

                    }
                }


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
