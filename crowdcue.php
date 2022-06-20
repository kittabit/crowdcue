<?php
/**
 * @wordpress-plugin
 * Plugin Name: Crowdcue
 * Plugin URI: https://github.com/kittabit/crowdcue
 * Description: Crowdcue allows you to easily output a beautiful and simple events page without any coding using OccasionGenius.
 * Version: 1.3.0
 * Author: Nicholas Mercer (@kittabit)
 * Author URI: https://kittabit.com
 */

defined( 'ABSPATH' ) or die( 'Direct Access Not Allowed.' );

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Carbon_Fields\Container;
use Carbon_Fields\Block;
use Carbon_Fields\Field;

define( 'Carbon_Fields\URL', plugin_dir_url( __FILE__ ) . "vendor/htmlburger/carbon-fields" );

if (!class_exists("Crowdcue")) {

    class Crowdcue {

        protected $OG_WIDGET_PATH;
        protected $OG_ASSET_MANIFEST;
        protected $OG_DB_VERSION;

        /**
        * Basic Constructor
        *
        * @since 0.1.0
        */
        function __construct() {

            $this->OG_WIDGET_PATH = plugin_dir_path( __FILE__ ) . '/og-events';
            $this->OG_ASSET_MANIFEST = $this->OG_WIDGET_PATH . '/build/asset-manifest.json';
            $this->OG_DB_VERSION = "1.3.0";

            register_activation_hook( __FILE__, array($this, 'og_install') );
            add_action( 'init', array($this, 'og_pretty_urls') );
            if(!is_admin()):
                add_filter( 'script_loader_tag', array($this, "script_loader_og_widget_js"), 10, 2);
                add_action( 'wp_enqueue_scripts', array($this, "enqueue_og_widget_js"));
            endif; 
            add_shortcode( 'occasiongenius_events', array($this, "shortcode_occasiongenius_events"));
            add_action( 'carbon_fields_register_fields', array($this, 'plugin_settings_page_and_blocks') );
            add_action( 'init', array($this, 'register_post_types') );
            add_action( 'after_setup_theme', array($this, 'load_carbon_fields') );
            add_action( 'carbon_fields_register_fields', array($this, 'posttype_meta_fields') );
            add_action( 'plugins_loaded', array($this, 'update_db_check') );
            add_action( 'admin_enqueue_scripts', array($this, 'admin_css_enqueue') );
            add_action( 'init', array($this, 'debug_plugin_functions'), 999 );

            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/events', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_response_data'),
                ));
            });
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/event/(?P<slug>\S+)', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_single_response_data'),
                ));
            });      
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/flag/(?P<id>\S+)', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_category_response_data'),
                ));
            });     
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/flags', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_category_list_response_data'),
                ));
            });     
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/areas', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_area_list_response_data'),
                ));
            });                 
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/venue/(?P<uuid>\S+)', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_venue_list_response_data'),
                ));
            });  
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/suggested/(?P<id>\S+)', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_related_and_suggested_response_data'),
                ));
            });    
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/personalized', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_personalized_response_data'),
                ));
            });             
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/bucket', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_bucket_response_data'),
                ));
            });                                      
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/event_flags', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_event_flags_response_data'),
                ));
            });
            add_action( 'rest_api_init', function () {
                register_rest_route( 'occasiongenius/v1', '/nearby/(?P<id>\S+)', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'api_nearby_response_data'),
                ));
            });                
            add_action( 'wp', array($this, 'og_scheduled_tasks') );
            add_action( 'og_sync_events', array($this, 'import_events') );
            add_action( 'og_purge_events', array($this, 'purge_events') );            
            add_action( 'admin_menu', array($this, 'og_nav_cleanup') );
            add_action( 'admin_notices', array($this, 'og_admin_notice') );

        }


        /**
        * Setup Scheduled Task to Sync Events
        *
        * @since 0.1.0
        */
        function og_scheduled_tasks(){

            if ( !wp_next_scheduled( 'og_sync_events' ) ) {
                wp_schedule_event(time(), 'hourly', 'og_sync_events');
            }

            if ( !wp_next_scheduled( 'og_purge_events' ) ) {
                wp_schedule_event(time(), 'hourly', 'og_purge_events');
            }            

        }


        /**
        * Load & Enable Cardon Fields Support
        *
        * @since 0.1.0
        */
        function load_carbon_fields(){

            \Carbon_Fields\Carbon_Fields::boot();

        }


        /**
        * Checks Database & Sets Up Data Store (on activation/install)
        *
        * @since 0.1.0
        */
        function og_install(){

            global $wpdb;
            $installed_ver = get_option( "wc_og_version" );
            $og_token = get_option( '_og-token-key' );

            if($og_token):
                if ( $installed_ver != $this->OG_DB_VERSION ):
                    update_option("wc_og_version", $this->OG_DB_VERSION);
                    $this->og_api_flags();
                    $this->og_api_areas();
                    $this->import_events();
                    flush_rewrite_rules();
                endif;
            endif;

        }

        
        /**
        * Checks Database & Sets Up Data Store (for upgrades versus activation/installation)
        *
        * @since 0.3.0
        */
        function update_db_check(){

            if ( get_site_option( 'wc_og_version' ) != $this->OG_DB_VERSION ):
                $this->og_install();
            endif;

        }


        /**
        * Setup Administration Panel Options, Blocks, Settings
        *
        * @since 0.1.0
        */
        function plugin_settings_page_and_blocks(){

            $flags = $this->og_api_flags();
            if(!is_array($flags)): $flags = array(); endif; 

            $areas = $this->og_api_areas();
            if(!is_array($areas)): $areas = array(); endif; 

            $time_formats = array(
                "F j, Y, g:i a" => date("F j, Y, g:i a")
            );

            $timezones = array(
                'Pacific/Midway'       => "(GMT-11:00) Midway Island",
                'US/Samoa'             => "(GMT-11:00) Samoa",
                'US/Hawaii'            => "(GMT-10:00) Hawaii",
                'US/Alaska'            => "(GMT-09:00) Alaska",
                'US/Pacific'           => "(GMT-08:00) Pacific Time (US & Canada)",
                'America/Tijuana'      => "(GMT-08:00) Tijuana",
                'US/Arizona'           => "(GMT-07:00) Arizona",
                'US/Mountain'          => "(GMT-07:00) Mountain Time (US & Canada)",
                'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
                'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
                'America/Mexico_City'  => "(GMT-06:00) Mexico City",
                'America/Monterrey'    => "(GMT-06:00) Monterrey",
                'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
                'US/Central'           => "(GMT-06:00) Central Time (US & Canada)",
                'US/Eastern'           => "(GMT-05:00) Eastern Time (US & Canada)",
                'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
                'America/Bogota'       => "(GMT-05:00) Bogota",
                'America/Lima'         => "(GMT-05:00) Lima",
                'America/Caracas'      => "(GMT-04:30) Caracas",
                'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
                'America/La_Paz'       => "(GMT-04:00) La Paz",
                'America/Santiago'     => "(GMT-04:00) Santiago",
                'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
                'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
                'Greenland'            => "(GMT-03:00) Greenland",
                'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
                'Atlantic/Azores'      => "(GMT-01:00) Azores",
                'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
                'Africa/Casablanca'    => "(GMT) Casablanca",
                'Europe/Dublin'        => "(GMT) Dublin",
                'Europe/Lisbon'        => "(GMT) Lisbon",
                'Europe/London'        => "(GMT) London",
                'Africa/Monrovia'      => "(GMT) Monrovia",
                'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
                'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
                'Europe/Berlin'        => "(GMT+01:00) Berlin",
                'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
                'Europe/Brussels'      => "(GMT+01:00) Brussels",
                'Europe/Budapest'      => "(GMT+01:00) Budapest",
                'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
                'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
                'Europe/Madrid'        => "(GMT+01:00) Madrid",
                'Europe/Paris'         => "(GMT+01:00) Paris",
                'Europe/Prague'        => "(GMT+01:00) Prague",
                'Europe/Rome'          => "(GMT+01:00) Rome",
                'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
                'Europe/Skopje'        => "(GMT+01:00) Skopje",
                'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
                'Europe/Vienna'        => "(GMT+01:00) Vienna",
                'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
                'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
                'Europe/Athens'        => "(GMT+02:00) Athens",
                'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
                'Africa/Cairo'         => "(GMT+02:00) Cairo",
                'Africa/Harare'        => "(GMT+02:00) Harare",
                'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
                'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
                'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
                'Europe/Kiev'          => "(GMT+02:00) Kyiv",
                'Europe/Minsk'         => "(GMT+02:00) Minsk",
                'Europe/Riga'          => "(GMT+02:00) Riga",
                'Europe/Sofia'         => "(GMT+02:00) Sofia",
                'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
                'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
                'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
                'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
                'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
                'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
                'Europe/Moscow'        => "(GMT+03:00) Moscow",
                'Asia/Tehran'          => "(GMT+03:30) Tehran",
                'Asia/Baku'            => "(GMT+04:00) Baku",
                'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
                'Asia/Muscat'          => "(GMT+04:00) Muscat",
                'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
                'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
                'Asia/Kabul'           => "(GMT+04:30) Kabul",
                'Asia/Karachi'         => "(GMT+05:00) Karachi",
                'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
                'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
                'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
                'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
                'Asia/Almaty'          => "(GMT+06:00) Almaty",
                'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
                'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
                'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
                'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
                'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
                'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
                'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
                'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
                'Australia/Perth'      => "(GMT+08:00) Perth",
                'Asia/Singapore'       => "(GMT+08:00) Singapore",
                'Asia/Taipei'          => "(GMT+08:00) Taipei",
                'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
                'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
                'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
                'Asia/Seoul'           => "(GMT+09:00) Seoul",
                'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
                'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
                'Australia/Darwin'     => "(GMT+09:30) Darwin",
                'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
                'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
                'Australia/Canberra'   => "(GMT+10:00) Canberra",
                'Pacific/Guam'         => "(GMT+10:00) Guam",
                'Australia/Hobart'     => "(GMT+10:00) Hobart",
                'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
                'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
                'Australia/Sydney'     => "(GMT+10:00) Sydney",
                'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
                'Asia/Magadan'         => "(GMT+12:00) Magadan",
                'Pacific/Auckland'     => "(GMT+12:00) Auckland",
                'Pacific/Fiji'         => "(GMT+12:00) Fiji",
            );

            $grid_options = array(
                "5" => "5", 
                "8" => "8", 
                "10" => "10", 
                "12" => "12"
            );

            $conditional_logic = array(
                'relation' => 'AND',
                array(
                    'field' => 'og-token-key',
                    'value' => array(''),
                    'compare' => 'NOT IN',
                )
            );     
            
            Container::make( 'theme_options', 'Crowdcue' )->set_page_parent("options-general.php")->add_fields( array(
                Field::make( 'separator', 'og_basic_settings', 'Basic Settings & Information' )->set_classes( 'og-admin-heading' ),
                Field::make( 'text', 'og-token-key', "OccasionGenius Token Key"),        
                Field::make( 'select', 'og-time-format', 'Time Format' )->add_options( $time_formats )->set_default_value('F j, Y, g:i a')->set_width( 50 )->set_conditional_logic( $conditional_logic ),
                Field::make( 'select', 'og-time-zone', 'Time Zone' )->add_options( $timezones )->set_default_value('US/Eastern')->set_width( 50 )->set_conditional_logic( $conditional_logic ),
                Field::make( "multiselect", "og-disabled-flags", "Disabled Flags" )->add_options( $flags )->set_width( 50 )->set_conditional_logic( $conditional_logic ),
                Field::make( "multiselect", "og-disabled-areas", "Disabled Areas" )->add_options( $areas )->set_width( 50 )->set_conditional_logic( $conditional_logic ),
                Field::make( "multiselect", "og-featured-flags", "Featured Flags" )->add_options( $flags )->set_width( 100 )->set_conditional_logic( $conditional_logic ),
                Field::make( 'separator', 'og_map_options', 'Google Maps Settings' )->set_classes( 'og-admin-heading' )->set_conditional_logic( $conditional_logic ),
                Field::make( 'text', 'og-google-maps-api-key', "API Key")->set_conditional_logic( $conditional_logic ),      
                Field::make( 'separator', 'og_design_options', 'Basic Design Settings' )->set_classes( 'og-admin-heading' )->set_conditional_logic( $conditional_logic ),
                Field::make( 'select', 'og-design-per-page-limit', "Events Per Page (Archive Pages)")->add_options( $grid_options )->set_default_value( "12" )->set_conditional_logic( $conditional_logic ),
                Field::make( 'separator', 'og_design_hp_options', 'Events Homepage Settings' )->set_classes( 'og-admin-heading' )->set_conditional_logic( $conditional_logic ),
                Field::make( 'image', 'og-design-header-image-1', "Events Header Image #1")->set_value_type( 'url' )->set_width( 33 )->set_conditional_logic( $conditional_logic ),
                Field::make( 'image', 'og-design-header-image-2', "Events Header Image #2")->set_value_type( 'url' )->set_width( 33 )->set_conditional_logic( $conditional_logic ),
                Field::make( 'image', 'og-design-header-image-3', "Events Header Image #3")->set_value_type( 'url' )->set_width( 33 )->set_conditional_logic( $conditional_logic ),                
                Field::make( 'text', 'og-design-heading', "Events Headline")->set_conditional_logic( $conditional_logic ),  
                Field::make( 'text', 'og-design-subheading', "Events Subheader")->set_conditional_logic( $conditional_logic ), 
                Field::make( 'text', 'og-design-hp-btn-text', "Call To Action Button Text")->set_width( 50 )->set_conditional_logic( $conditional_logic ),  
                Field::make( 'text', 'og-design-hp-btn-url', "Call To Action Button URL")->set_width( 50 )->set_conditional_logic( $conditional_logic ),  
                Field::make( 'separator', 'og_developer_settings', 'Developer Settings' )->set_classes( 'og-admin-heading' ),
                Field::make( 'text', 'og-developer-security-key', "Developer Security Key")->set_default_value( md5(rand() . "-og-" . time() )),
                Field::make( 'separator', 'og_analytics_settings', 'Analytics Settings' )->set_classes( 'og-admin-heading' ),
                Field::make( 'text', 'og-analytics-ua-id', "Google Analytics UA ID")->set_conditional_logic( $conditional_logic ) 
            ));

            $this->register_events_block();

        }


        /**
        * Custom Admin CSS for Options
        *
        * @since 0.1.0
        */
        function admin_css_enqueue(){

            wp_enqueue_style( 'occasiongenius-admin-css', plugin_dir_url( __FILE__ ) . '/public/css/admin.css' );

        }
        

        /**
        * Custom Admin Notices
        *
        * @since 0.5.0
        */
        function og_admin_notice(){

            $og_token = get_option( '_og-token-key' );
            
            if(!$og_token):
                $og_settings_url = admin_url('options-general.php?page=crb_carbon_fields_container_occasiongenius.php');
                echo "<div class='notice notice-warning'>
                    <p>In order to use OccasionGenius, you'll need to finish your <a href='{$og_settings_url}'>setup process</a>.</p>
                </div>";
            endif;

        }

        
        /**
        * Registering Event Custom Post Type
        *
        * @since 0.1.0
        */
        function register_post_types(){

            register_post_type( 'og_events',
                array(
                    'labels' => array(
                        'name' => __( 'Events' ),
                        'singular_name' => __( 'Event' )
                    ),
                    'public' => true,
                    'has_archive' => false,
                    'rewrite' => array('slug' => 'events'),
                    'supports' => array( 'title' ) ,
                    'show_in_rest' => true,
                    'query_var' => false
                )
            );

        }


        /**
        * Setup Meta Fields & Options for Post Type
        *
        * @since 0.1.0
        */
        function posttype_meta_fields(){
            
            Container::make( 'post_meta', 'OccasionGenius Basic Information' )->show_on_post_type('og_events')->add_fields( array(
                Field::make( 'text', 'og-event-name', "Event Name"),
                Field::make( 'text', 'og-event-uuid', "UUID")->set_width( 50 ),
                Field::make( 'text', 'og-event-popularity-score', "Popularity Score")->set_width( 50 ),
                Field::make( 'textarea', 'og-event-description', "Event Description")->set_rows( 4 ),
                Field::make( 'textarea', 'og-event-flags', "Event Flags"),
                Field::make( 'text', 'og-event-start-date', "Start Date")->set_width( 50 ),            
                Field::make( 'text', 'og-event-end-date', "End Date")->set_width( 50 ),
                Field::make( 'hidden', 'og-event-start-date-unix', "Start Date (Unix)")->set_classes( 'og-hidden-label' ),
                Field::make( 'hidden', 'og-event-end-date-unix', "End Date (Unix)")->set_classes( 'og-hidden-label' ),
                Field::make( 'textarea', 'og-event-event-dates', "Event Dates"),
                Field::make( 'text', 'og-event-source-url', "Source URL")->set_width( 33 ),
                Field::make( 'text', 'og-event-image-url', "Image URL")->set_width( 33 ),
                Field::make( 'text', 'og-event-ticket-url', "Ticket URL")->set_width( 33 )            
            ));

            Container::make( 'post_meta', 'OccasionGenius Venue Information' )->show_on_post_type('og_events')->add_fields( array(
                Field::make( 'text', 'og-event-venue-name', "Name"),
                Field::make( 'text', 'og-event-venue-uuid', "UUID"),            
                Field::make( 'text', 'og-event-venue-address-1', "Address 1")->set_width( 50 ),
                Field::make( 'text', 'og-event-venue-address-2', "Address 2")->set_width( 50 ),
                Field::make( 'text', 'og-event-venue-city', "City")->set_width( 25 ),
                Field::make( 'text', 'og-event-venue-state', "State")->set_width( 25 ),
                Field::make( 'text', 'og-event-venue-zip-code', "Zip Code")->set_width( 25 ),
                Field::make( 'text', 'og-event-venue-country', "Country")->set_width( 25 ),
                Field::make( 'text', 'og-event-venue-latitude', "Latitude")->set_width( 50 ),
                Field::make( 'text', 'og-event-venue-longitude', "Longitude")->set_width( 50 )
            ));        

        }


        /**
        * Allows for Making String Names Plural
        *
        * @since 0.6.0
        */
        function og_pluralize($singular) {        

            $skip_it = array(
                "a",
                "g",
                "z",
                "c",
                "y",
                "s",
                "n",
                "o",
                "l",
                "q"
            );

            $last_letter = strtolower($singular[strlen($singular)-1]);
            switch($last_letter) {
                case in_array($last_letter, $skip_it):
                    return $singular;
                case 'y':
                    return substr($singular,0,-1).'ies';
                default:
                    return $singular.'s';
            }

        }


        /**
        * Allows for Flag Name Cleanup
        *
        * @since 0.9.0
        */
        function og_flag_cleanup($flag) {  

            $flag = str_replace("Genre R B", "Genre  R&B", $flag);
            $flag = str_replace("Dont Miss", "Don't Miss", $flag);
            $flag = str_replace("Genre ", "Genre: ", $flag);

            return $flag;

        }


        /**
        * Import Events via API
        *
        * @since 0.1.0
        */
        function import_events($url=NULL){

            require_once( ABSPATH . 'wp-admin/includes/post.php' );

            $og_token = get_option( '_og-token-key' );

            if(!$url){
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d', strtotime("+1 Month"));
                $url = "https://v2.api.occasiongenius.com/api/events/?start_date=" . $start_date . "&end_date=" . $end_date . "&limit=25";
            }
            
            $args = array(
                'headers' => array(
                    "accept" => "application/json",
                    "Authorization" => 'Token ' . $og_token
                )
            );
            $response = wp_remote_get($url, $args);
            $rest = wp_remote_retrieve_body($response);

            $results = json_decode($rest);
            foreach($results->results as $event){

                $event_title = $event->name . " (" . $event->uuid . ")";
                
                $new_event = array(
                    'post_title' => $event_title, 
                    'post_name' => sanitize_title($event->name),
                    'post_type' => 'og_events', 
                    'post_status' => 'publish'
                );
                $event_local_id = post_exists( $event_title ) or wp_insert_post( $new_event );
                echo "#" . $event_local_id . " - " . $event_title . "<br />\n";

                carbon_set_post_meta( $event_local_id, 'og-event-name', $event->name );
                carbon_set_post_meta( $event_local_id, 'og-event-uuid', $event->uuid );
                carbon_set_post_meta( $event_local_id, 'og-event-popularity-score', $event->popularity_score );
                carbon_set_post_meta( $event_local_id, 'og-event-description', $event->description );
                carbon_set_post_meta( $event_local_id, 'og-event-flags', json_encode($event->flags) );
                carbon_set_post_meta( $event_local_id, 'og-event-start-date', $event->start_date );
                carbon_set_post_meta( $event_local_id, 'og-event-end-date', $event->end_date );
                carbon_set_post_meta( $event_local_id, 'og-event-start-date-unix', strtotime($event->start_date) );
                carbon_set_post_meta( $event_local_id, 'og-event-end-date-unix', strtotime($event->end_date) );
                carbon_set_post_meta( $event_local_id, 'og-event-source-url', $event->source_url );
                carbon_set_post_meta( $event_local_id, 'og-event-image-url', $event->image_url );
                carbon_set_post_meta( $event_local_id, 'og-event-ticket-url', $event->ticket_url );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-name', $event->venue->name );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-uuid', $event->venue->uuid );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-address-1', $event->venue->address_1 );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-address-2', $event->venue->address_2 );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-city', $event->venue->city );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-state', $event->venue->region );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-zip-code', $event->venue->postal_code );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-country', $event->venue->country );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-latitude', $event->venue->latitude );
                carbon_set_post_meta( $event_local_id, 'og-event-venue-longitude', $event->venue->longitude );

            }

            if($results->next){
                $this->import_events($results->next);
            }

        }


        /**
        * Purge Past Events
        *
        * @since 0.6.0
        */
        function purge_events(){

            $query_args = array(
                'post_type'=>'og_events',
                'meta_query' => array(
                    array(
                        'key' => 'og-event-start-date-unix',
                        'value' => time(),
                        'compare' => '<'
                    )
                ),
                'posts_per_page' => -1,
            );
            
            $query = new WP_Query( $query_args );
            while($query->have_posts()) :
                $query->the_post();

                wp_delete_post(get_the_ID(), true);
            endwhile;
            wp_reset_query();
            
        }


        /**
        * Get Event Flags via API
        *
        * @since 0.2.0
        */
        function og_api_flags($url=NULL,$flags=NULL){

            $flags_stored = (array) json_decode(get_option("og_api_flags"));

            if( is_array($flags_stored) && count( $flags_stored ) > 0 ):
                return $flags_stored;
            else:
                $og_token = get_option( '_og-token-key' );

                if($og_token):
                    if(!$url){
                        $flags = array();
                        $url = "https://v2.api.occasiongenius.com/api/flag_definitions/";
                    }
                    
                    $args = array(
                        'headers' => array(
                            "accept" => "application/json",
                            "Authorization" => 'Token ' . $og_token
                        )
                    );
                    $response = wp_remote_get($url, $args);
                    $rest = wp_remote_retrieve_body($response);

                    $results = json_decode($rest);
                    if(is_array($results->results)):                    
                        foreach($results->results as $flag){
                            $flags[$flag->id] = $flag->name;
                        }
                    endif; 

                    if($results->next):
                        $this->og_api_flags($results->next, $flags);
                    else:
                        if(is_array($flags) && count($flags) > 0):
                            update_option("og_api_flags", json_encode($flags));
                        endif;                         
                        return $flags;
                    endif;
                endif;
            endif;

        }


        /**
        * Get Areas via API
        *
        * @since 0.2.0
        */
        function og_api_areas($url=NULL,$areas=NULL){

            $area_stored = (array) json_decode(get_option("og_api_areas"));

            if( is_array($area_stored) && count( $area_stored ) > 0 ):
                return $area_stored;
            else:
                $og_token = get_option( '_og-token-key' );

                if($og_token):
                    if(!$url){
                        $areas = array();
                        $url = "https://v2.api.occasiongenius.com/api/areas/";
                    }
                    
                    $args = array(
                        'headers' => array(
                            "accept" => "application/json",
                            "Authorization" => 'Token ' . $og_token
                        )
                    );
                    $response = wp_remote_get($url, $args);
                    $rest = wp_remote_retrieve_body($response);
                    
                    $results = json_decode($rest);
                    if(is_array($results->results)):
                        foreach($results->results as $area){
                            $areas[$area->uuid] = $area->name;
                        }
                    endif;

                    if($results->next):
                        $this->og_api_areas($results->next, $areas);
                    else:
                        if(is_array($areas) && count($areas) > 0):
                            update_option("og_api_areas", json_encode($areas));
                        endif;                
                        return $areas;
                    endif;
                endif;
            endif;

        }


        /**
        * Optimize JS Loading for `og-*` Assets
        *
        * @since 0.1.0
        */
        function script_loader_og_widget_js($tag, $handle){

            if ( ! preg_match( '/^occasiongenius-/', $handle ) ) { return $tag; }
            return str_replace( ' src', ' async defer src', $tag );

        }
        

        /**
        * Load all JS/CSS assets from 'OccasionGenius' React Widget
        *
        * @since 0.1.0
        */
        function enqueue_og_widget_js(){

            $asset_manifest = json_decode( file_get_contents( $this->OG_ASSET_MANIFEST ), true )['files'];
            
            if ( isset( $asset_manifest[ 'main.css' ] ) ) {
                wp_enqueue_style( 'occasiongenius', plugin_dir_url( __FILE__ ) . $asset_manifest[ 'main.css' ] );
            }
        
            wp_enqueue_script( 'occasiongenius-main', plugin_dir_url( __FILE__ ) . $asset_manifest[ 'main.js' ], array(), null, true );
        
            foreach ( $asset_manifest as $key => $value ) {
                if ( preg_match( '@static/js/(.*)\.chunk\.js@', $key, $matches ) ) {
                    if ( $matches && is_array( $matches ) && count( $matches ) === 2 ) {
                        $name = "occasiongenius-" . preg_replace( '/[^A-Za-z0-9_]/', '-', $matches[1] );
                        wp_enqueue_script( $name, plugin_dir_url( __FILE__ ) . $value, array( 'occasiongenius-main' ), null, true );
                    }
                }
        
                if ( preg_match( '@static/css/(.*)\.chunk\.css@', $key, $matches ) ) {
                    if ( $matches && is_array( $matches ) && count( $matches ) == 2 ) {
                        $name = "occasiongenius-" . preg_replace( '/[^A-Za-z0-9_]/', '-', $matches[1] );
                        wp_enqueue_style( $name, plugin_dir_url( __FILE__ ) . $value, array( 'occasiongenius' ), null );
                    }
                }
            }

        }


        /**
        * [occasiongenius_events] Shortcode Output
        *
        * @since 0.1.0
        */
        function shortcode_occasiongenius_events( $atts ){

            $default_atts = array();
            $args = shortcode_atts( $default_atts, $atts );

            ob_start();

            $og_design_image_1 = carbon_get_theme_option( 'og-design-header-image-1' );
            $og_design_image_2 = carbon_get_theme_option( 'og-design-header-image-2' );
            $og_design_image_3 = carbon_get_theme_option( 'og-design-header-image-3' );
            $og_heading = carbon_get_theme_option( 'og-design-heading' );
            $og_subheading = carbon_get_theme_option( 'og-design-subheading' );
            $og_featured_flags = json_encode(carbon_get_theme_option( 'og-featured-flags' ));            
            $og_hp_btn_text = carbon_get_theme_option( 'og-design-hp-btn-text' );
            $og_hp_btn_url = carbon_get_theme_option( 'og-design-hp-btn-url' );
            $og_gmaps_api_key = carbon_get_theme_option( 'og-google-maps-api-key' );
            $og_analytics_id = carbon_get_theme_option( 'og-analytics-ua-id' );
            ?>
            <script>
            window.ogSettings = window.ogSettings || {};
            window.ogSettings = {
                'og_base_url': '<?php echo esc_js(plugin_dir_url( __FILE__ )); ?>',
                'og_design_image_1': '<?php echo esc_js($og_design_image_1); ?>',
                'og_design_image_2': '<?php echo esc_js($og_design_image_2); ?>',
                'og_design_image_3': '<?php echo esc_js($og_design_image_3); ?>',
                'og_heading': '<?php echo esc_js($og_heading); ?>',
                'og_subheading': '<?php echo esc_js($og_subheading); ?>',
                'og_hp_btn_text': '<?php echo esc_js($og_hp_btn_text); ?>',
                'og_hp_btn_url': '<?php echo esc_js($og_hp_btn_url); ?>',
                'og_featured_flags': '<?php echo esc_js($og_featured_flags); ?>',
                'og_gmaps_api_key': '<?php echo esc_js($og_gmaps_api_key); ?>',
                'og_base_date': '<?php echo esc_js(date('Y-m-d')); ?>',
                'og_min_base_date': '<?php echo esc_js(date('Y-m-d', strtotime("+1 Day"))); ?>',
                'og_max_base_date': '<?php echo esc_js(date('Y-m-d', strtotime("+1 Month"))); ?>',
                'og_ga_ua': '<?php echo esc_js( $og_analytics_id ); ?>'
            }
            </script>            
            <div id="App" class="og-root" data-baseurl="/events"></div>
            <?php
            return ob_get_clean();

        }


        /**
        * Gutenberg Block Support
        *
        * @since 0.1.0
        */
        function register_events_block(){

            Block::make( __( 'OccasionGenius Events' ) )->set_mode("preview")->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
                if (strpos($_SERVER['REQUEST_URI'],'carbon-fields') !== false):
                    echo "Notice:  OccasionGenius block only visible on the front end.";
                else:
                    echo do_shortcode("[occasiongenius_events]");
                endif;
            } );        
            
        }


        /**
        * API Response (JSON Data)
        *
        * @since 0.1.0
        */
        function api_response_data(){

            extract($_GET);
            
            $flags = $this->og_api_flags();
            $areas = $this->og_api_areas();

            if(!$page):
                $page = 1;
            endif;

            if(!$limit):
                if( carbon_get_theme_option("og-design-per-page-limit") ):
                    $limit = carbon_get_theme_option("og-design-per-page-limit");
                else:
                    $limit = 24;
                endif;
            endif;        

            $og_time_format = carbon_get_theme_option( 'og-time-format' );
            if(!$og_time_format): $og_time_format = "F j, Y, g:i a"; endif;

            $og_time_zone = carbon_get_theme_option( 'og-time-zone' );
            if(!$og_time_zone): $og_time_zone = "US/Eastern"; endif;

            date_default_timezone_set($og_time_zone);

            $disabled_flags = carbon_get_theme_option( 'og-disabled-flags' );
            $disabled_areas = carbon_get_theme_option( 'og-disabled-areas' );

            $events = array();
            $query_args = array(
                'post_type'=>'og_events',
                'orderby' => 'order_clause',
                'meta_query' => array(
                    'order_clause' => array(
                        'key' => 'og-event-start-date-unix',
                        'type' => 'NUMERIC' 
                    ),
                ),
                'order' => 'asc',
                'posts_per_page' => $limit,
                'paged' => $page,
            );


            if($filter_start):
                $events['info']['filter']['start'] = $filter_start;
                $query_args['meta_query'][] = array(
                    'key' => 'og-event-start-date-unix',
                    'value' => strtotime($filter_start),
                    'compare' => '>='            
                );
            else:
                $query_args['meta_query'][] = array(
                    'key' => 'og-event-start-date-unix',
                    'value' => time(),
                    'compare' => '>='            
                );                
            endif;

            if($filter_end):
                $events['info']['filter']['end'] = $filter_end;
                $query_args['meta_query'][] = array(
                    'key' => 'og-event-start-date-unix',
                    'value' => strtotime($filter_end),
                    'compare' => '<='                             
                );
            endif;            

            if($filter_flags):
                $filter_flags = explode(",", $filter_flags);
                $events['info']['filter']['flags'] = $filter_flags;
                // array(
                //     'relation' => 'OR',
                foreach($filter_flags as $ff):
                    $query_args['meta_query'][] = array(
                        'key' => 'og-event-flags',
                        'value' => '"' . $ff . '"',
                        'compare' => 'LIKE'                             
                    );
                endforeach;
            else:
                if(count($disabled_flags) > 0 && is_array($disabled_flags)):
                    foreach($disabled_flags as $df):
                        $query_args['meta_query'][] = array(
                            'key' => 'og-event-flags',
                            'value' => $flags[$df],
                            'compare' => 'NOT IN'                        
                        );
                    endforeach;
                endif;
            endif;

            if($filter_areas):
                $filter_areas = explode(",", $filter_areas);
                $events['info']['filter']['areas'] = $filter_areas;

                foreach($filter_areas as $fa):
                    $query_args['meta_query'][] = array(
                        'key' => 'og-event-venue-city',
                        'value' => $fa,
                        'compare' => 'LIKE'                             
                    );
                endforeach;
            else:
                if(count($disabled_areas) > 0 && is_array($disabled_areas)):
                    foreach($disabled_areas as $da):
                        $query_args['meta_query'][] = array(
                            'key' => 'og-event-venue-city',
                            'value' => $areas[$da],
                            'compare' => 'NOT IN'                        
                        );
                    endforeach;
                endif;
            endif;

            //print_r($query_args);

            $query = new WP_Query( $query_args );

            $events['info']['limit'] = $limit;
            $events['info']['current_page'] = $page;
            $events['info']['next_page'] = $page + 1;
            $events['info']['max_pages'] = $query->max_num_pages;

            while($query->have_posts()) :
                $query->the_post();
                $post_details = get_post(get_the_ID()); 

                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_end = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
    
                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_date = date('m/d/y g:i a', $og_output_start); 
                $og_gcal_start_date = date("Ymd" . '__' . "Gi", $og_output_start);

                if(carbon_get_the_post_meta( 'og-event-end-date' )):
                    $end_date = date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-end-date' )));
                    $end_date_unix = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
                    if( date('m/d/y', $og_output_start) == date('m/d/y', $end_date_unix)):
                        $og_output_date = date('m/d/y g:i a', $og_output_start) . " - " . date('g:i a', $end_date_unix);
                    else:
                        $og_output_date = date('m/d/y', $og_output_start) . " - " . date('m/d/y', $end_date_unix);
                    endif;
                    $og_gcal_end_date = date("Ymd" . '__' . "Gi", $end_date_unix);
                else:
                    $end_date = "";
                    $end_date_unix = "";
                endif;

                $og_gcal_start_date = str_replace("__", "T", $og_gcal_start_date);
                $og_gcal_end_date = str_replace("__", "T", $og_gcal_end_date);

                $events['events'][] = array(
                    "id" => get_the_ID(),
                    "slug" => $post_details->post_name,
                    "name" => carbon_get_the_post_meta( 'og-event-name' ),
                    "uuid" => carbon_get_the_post_meta( 'og-event-uuid' ),
                    "popularity_score" => carbon_get_the_post_meta( 'og-event-popularity-score' ),
                    "description" => carbon_get_the_post_meta( 'og-event-description' ),
                    "flags" => json_decode(carbon_get_the_post_meta( 'og-event-flags' )),
                    "start_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                    "start_date_month" => date("M", strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                    "start_date_day" => date("j", strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                    "end_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-end-date' ))),
                    "date_formatted" => $og_output_date,
                    "start_date_unix" => carbon_get_the_post_meta( 'og-event-start-date-unix' ),
                    "end_date_unix" => carbon_get_the_post_meta( 'og-event-end-date-unix' ),
                    "source_url" => carbon_get_the_post_meta( 'og-event-source-url' ),
                    "image_url" => carbon_get_the_post_meta( 'og-event-image-url' ),
                    "ticket_url" => carbon_get_the_post_meta( 'og-event-ticket-url' ),
                    "venue_name" => carbon_get_the_post_meta( 'og-event-venue-name' ),
                    "venue_uuid" => carbon_get_the_post_meta( 'og-event-venue-uuid' ),
                    "venue_address_1" => carbon_get_the_post_meta( 'og-event-venue-address-1' ),
                    "venue_address_2" => carbon_get_the_post_meta( 'og-event-venue-address-2' ),
                    "venue_city" => carbon_get_the_post_meta( 'og-event-venue-city' ),
                    "venue_state" => carbon_get_the_post_meta( 'og-event-venue-state' ),
                    "venue_zip" => carbon_get_the_post_meta( 'og-event-venue-zip-code' ),
                    "venue_country" => carbon_get_the_post_meta( 'og-event-venue-country' ),
                    "latitude" => carbon_get_the_post_meta( 'og-event-venue-latitude' ),
                    "longitude" => carbon_get_the_post_meta( 'og-event-venue-longitude' )
                );
            endwhile;
            wp_reset_query();

            return $events;

        }


        /**
        * API Single Event Response (JSON Data)
        *
        * @since 0.4.0
        */
        function api_single_response_data( $data ){

            $slug = $data['slug'];

            $query = new WP_Query( array(
                'post_type'=>'og_events',
                'name' => $slug
            ) );

            if($query->post_count == 0):
                $event_details["event"] = array(
                    "error" => "true"
                );
            else:
                $og_time_format = carbon_get_theme_option( 'og-time-format' );
                if(!$og_time_format): $og_time_format = "F j, Y, g:i a"; endif;

                $og_time_zone = carbon_get_theme_option( 'og-time-zone' );
                if(!$og_time_zone): $og_time_zone = "US/Eastern"; endif;

                date_default_timezone_set($og_time_zone);

                $event_details = array();
                while($query->have_posts()) :
                    $query->the_post();

                    $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                    $og_output_date = date('m/d/y g:i a', $og_output_start); 
                    $og_gcal_start_date = date("Ymd" . '__' . "Gi", $og_output_start);

                    if(carbon_get_the_post_meta( 'og-event-end-date' )):
                        $end_date = date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-end-date' )));
                        $end_date_unix = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
                        if( date('m/d/y', $og_output_start) == date('m/d/y', $end_date_unix)):
                            $og_output_date = date('m/d/y g:i a', $og_output_start) . " - " . date('g:i a', $end_date_unix);
                        else:
                            $og_output_date = date('m/d/y', $og_output_start) . " - " . date('m/d/y', $end_date_unix);
                        endif;
                        $og_gcal_end_date = date("Ymd" . '__' . "Gi", $end_date_unix);
                    else:
                        $end_date = "";
                        $end_date_unix = "";
                    endif;

                    $og_gcal_start_date = str_replace("__", "T", $og_gcal_start_date);
                    $og_gcal_end_date = str_replace("__", "T", $og_gcal_end_date);

                    $event_details["event"] = array(
                        "id" => get_the_ID(),
                        "name" => carbon_get_the_post_meta( 'og-event-name' ),
                        "uuid" => carbon_get_the_post_meta( 'og-event-uuid' ),
                        "popularity_score" => carbon_get_the_post_meta( 'og-event-popularity-score' ),
                        "description" => carbon_get_the_post_meta( 'og-event-description' ),
                        "flags" => json_decode(carbon_get_the_post_meta( 'og-event-flags' )),
                        "start_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                        "end_date" => $end_date,
                        "start_date_unix" => carbon_get_the_post_meta( 'og-event-start-date-unix' ),
                        "end_date_unix" => $end_date_unix,
                        "date_formatted" => $og_output_date,
                        "gcal_start_date" => $og_gcal_start_date,
                        "gcal_end_date" => $og_gcal_end_date,
                        "source_url" => carbon_get_the_post_meta( 'og-event-source-url' ),
                        "image_url" => carbon_get_the_post_meta( 'og-event-image-url' ),
                        "ticket_url" => carbon_get_the_post_meta( 'og-event-ticket-url' ),
                        "venue_name" => carbon_get_the_post_meta( 'og-event-venue-name' ),
                        "venue_uuid" => carbon_get_the_post_meta( 'og-event-venue-uuid' ),
                        "venue_address_1" => carbon_get_the_post_meta( 'og-event-venue-address-1' ),
                        "venue_address_2" => carbon_get_the_post_meta( 'og-event-venue-address-2' ),
                        "venue_city" => carbon_get_the_post_meta( 'og-event-venue-city' ),
                        "venue_state" => carbon_get_the_post_meta( 'og-event-venue-state' ),
                        "venue_zip" => carbon_get_the_post_meta( 'og-event-venue-zip-code' ),
                        "venue_country" => carbon_get_the_post_meta( 'og-event-venue-country' ),
                        "latitude" => carbon_get_the_post_meta( 'og-event-venue-latitude' ),
                        "longitude" => carbon_get_the_post_meta( 'og-event-venue-longitude' )
                    );
                endwhile;

                wp_reset_query();
            endif;

            return $event_details;

        }

        
        /**
        * API Single Category/Flag Response (JSON Data)
        *
        * @since 0.6.0
        */
        function api_category_response_data( $data ){

            extract($_GET);

            $flags = $this->og_api_flags();
            
            $flag_id = $data['id'];
            if(!is_numeric($flag_id)):
                $flag_id = array_search($flag_id, $flags);
            endif;

            $flag_name = strtolower($flags[$flag_id]);
            $flag_output = $this->og_pluralize(ucwords(str_replace("_", " ", $flag_name)));
            $flag_output = $this->og_flag_cleanup($flag_output);

            if(!$page):
                $page = 1;
            endif;

            if(!$limit):
                if( carbon_get_theme_option("og-design-per-page-limit") ):
                    $limit = carbon_get_theme_option("og-design-per-page-limit");
                else:
                    $limit = 24;
                endif;
            endif;        


            $response = array(); $events = array();
            $response['data'] = array(
                "ID" => $flag_id,
                "Name" => $flag_name,
                "Output" => $flag_output
            );

            $query_args = array(
                'post_type'=>'og_events',
                'meta_query' => array(
                    'order_clause' => array(
                        'key' => 'og-event-start-date-unix',
                        'type' => 'NUMERIC' 
                    ),                    
                    array(
                        'key' => 'og-event-start-date-unix',
                        'value' => time(),
                        'compare' => '>='
                    ),
                    array(
                        'key' => 'og-event-flags',
                        'value' => '"' . $flag_name . '"',
                        'compare' => 'LIKE'                        
                    )                    
                ),
                'orderby' => 'order_clause',
                'order' => 'asc',
                'posts_per_page' => $limit,
                'paged' => $page,
            );
            $query = new WP_Query($query_args);

            $response['info'] = array(
                "limit" => $limit,
                "current_page" => $page,            
                "next_page" => $page + 1,
                "max_pages" => $query->max_num_pages,
                "total" => $query->post_count
            );

            $og_time_format = carbon_get_theme_option( 'og-time-format' );
            if(!$og_time_format): $og_time_format = "F j, Y, g:i a"; endif;

            $og_time_zone = carbon_get_theme_option( 'og-time-zone' );
            if(!$og_time_zone): $og_time_zone = "US/Eastern"; endif;

            date_default_timezone_set($og_time_zone);
            while($query->have_posts()) :
                $query->the_post();

                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_end = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
    
                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_date = date('m/d/y g:i a', $og_output_start); 
                $og_gcal_start_date = date("Ymd" . '__' . "Gi", $og_output_start);

                if(carbon_get_the_post_meta( 'og-event-end-date' )):
                    $end_date = date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-end-date' )));
                    $end_date_unix = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
                    if( date('m/d/y', $og_output_start) == date('m/d/y', $end_date_unix)):
                        $og_output_date = date('m/d/y g:i a', $og_output_start) . " - " . date('g:i a', $end_date_unix);
                    else:
                        $og_output_date = date('m/d/y', $og_output_start) . " - " . date('m/d/y', $end_date_unix);
                    endif;
                    $og_gcal_end_date = date("Ymd" . '__' . "Gi", $end_date_unix);
                else:
                    $end_date = "";
                    $end_date_unix = "";
                endif;

                $og_gcal_start_date = str_replace("__", "T", $og_gcal_start_date);
                $og_gcal_end_date = str_replace("__", "T", $og_gcal_end_date);

                $events[] = array(
                    "id" => get_the_ID(),
                    "slug" => get_post_field( 'post_name' ),
                    "name" => carbon_get_the_post_meta( 'og-event-name' ),
                    "uuid" => carbon_get_the_post_meta( 'og-event-uuid' ),
                    "popularity_score" => carbon_get_the_post_meta( 'og-event-popularity-score' ),
                    "description" => carbon_get_the_post_meta( 'og-event-description' ),
                    "flags" => json_decode(carbon_get_the_post_meta( 'og-event-flags' )),
                    "start_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                    "end_date" => $end_date,
                    "start_date_unix" => carbon_get_the_post_meta( 'og-event-start-date-unix' ),
                    "end_date_unix" => $end_date_unix,
                    "date_formatted" => $og_output_date,
                    "source_url" => carbon_get_the_post_meta( 'og-event-source-url' ),
                    "image_url" => carbon_get_the_post_meta( 'og-event-image-url' ),
                    "ticket_url" => carbon_get_the_post_meta( 'og-event-ticket-url' ),
                    "venue_name" => carbon_get_the_post_meta( 'og-event-venue-name' ),
                    "venue_uuid" => carbon_get_the_post_meta( 'og-event-venue-uuid' ),
                    "venue_address_1" => carbon_get_the_post_meta( 'og-event-venue-address-1' ),
                    "venue_address_2" => carbon_get_the_post_meta( 'og-event-venue-address-2' ),
                    "venue_city" => carbon_get_the_post_meta( 'og-event-venue-city' ),
                    "venue_state" => carbon_get_the_post_meta( 'og-event-venue-state' ),
                    "venue_zip" => carbon_get_the_post_meta( 'og-event-venue-zip-code' ),
                    "venue_country" => carbon_get_the_post_meta( 'og-event-venue-country' ),
                    "latitude" => carbon_get_the_post_meta( 'og-event-venue-latitude' ),
                    "longitude" => carbon_get_the_post_meta( 'og-event-venue-longitude' )
                );
                
            endwhile;
            wp_reset_query();

            $response['events'] = $events;
            return $response;

        }

        
        /**
        * API Areas Response (JSON Data)
        *
        * @since 1.1.0
        */
        function api_area_list_response_data( $data ){

            global $wpdb; $areas_tmp = array(); $response = array();
            
            $area_rows = $wpdb->get_results( 'SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE `meta_key` = "_og-event-venue-city"', ARRAY_A);
            foreach($area_rows as $row){  
                $row_name = trim(preg_replace('/(\v|\s)+/', ' ', $row['meta_value']));

                $areas_tmp[] = $row_name;
            }
            $areas_tmp = array_unique($areas_tmp);
            sort($areas_tmp);

            foreach($areas_tmp as $area):
                $response[] = array(
                    "slug" => $area,
                    "output" => $area
                );
            endforeach;

            return $response;

        }

        /**
        * API Flags/Categories Response (JSON Data)
        *
        * @since 0.7.0
        */
        function api_category_list_response_data( $data ){


            $disabled_flags = carbon_get_theme_option( 'og-disabled-flags' );
            $flags = $this->og_api_flags(); $response = array();

            foreach($flags as $key => $val):

                if ( preg_match('/\s/', $val) ):

                else:
                    $flag_name = strtolower($val);
                    $flag_output = ucwords(str_replace("_", " ", $flag_name));
                    $flag_output = $this->og_pluralize($flag_output);
                    $flag_output = $this->og_flag_cleanup($flag_output);

                    $total_events = get_transient( 'og_flags_flag_' . $key . '_total_events' );
                    if( empty( $total_events ) ):
                        $query_args = array(
                            'post_type'=>'og_events',
                            'meta_query' => array(
                                array(
                                    'key' => 'og-event-start-date-unix',
                                    'value' => time(),
                                    'compare' => '>='
                                ),
                                array(
                                    'key' => 'og-event-flags',
                                    'value' => '"' . $flag_name . '"',
                                    'compare' => 'LIKE'                        
                                )                    
                            ),
                            'order' => 'asc',
                            'posts_per_page' => -1
                        );
                        $query = new WP_Query($query_args);
                        $total_events = $query->post_count;
                        $cache_status = "false";
                        set_transient( 'og_flags_flag_' . $key . '_total_events', $total_events, 43200 );
                    else:
                        $cache_status = "true";
                    endif; 

                    if(!in_array($key, $disabled_flags)):
                        $response[] = array(
                            "id" => $key,
                            "slug" => $val,
                            "output" => $flag_output,
                            "total" => $total_events,
                            "cache" => $cache_status
                        );
                    endif;

                endif;

            endforeach;

            $categories = array();
            foreach ($response as $key => $row):
                $categories[$key] = $row['output'];
            endforeach;   
            array_multisort($categories, SORT_ASC, $response);
            
            return $response;

        }


        /**
        * API Event Flags Clean Response (JSON Data)
        *
        * @since 0.8.0
        */
        function api_event_flags_response_data(){

            // TODO:  Hardcoded count (2 currently)
            extract($_GET);

            $response = array();

            if($flags):
                $flags = explode(",", $flags);
                foreach($flags as $flag):
                    $flag_slug = strtolower($flag);
                    $flag_name = ucwords(str_replace("_", " ", $flag_slug));
                    $flag_name = $this->og_pluralize($flag_name);
                    $flag_name = $this->og_flag_cleanup($flag_name);

                    $response[] = array(
                        "slug" => $flag_slug,
                        "name" => $flag_name,
                    );
                endforeach;
            endif;

            $response = array_slice($response, 0, 2);
            return $response;

        }


        /**
        * API Nearby Events (JSON)
        *
        * @since 1.3.0
        */
        function api_nearby_response_data( $data ){

            $selected_event = $data['id'];
            $select_event_lat = get_post_meta( $selected_event, '_og-event-venue-latitude', true );
            $select_event_long = get_post_meta( $selected_event, '_og-event-venue-longitude', true );

            $data = array(); $distances = array();
            $data['base'] = array(
                "base_id" => $selected_event,
                "base_lat" => $select_event_lat,
                "base_long" => $select_event_long,
            );

            $query_args = array(
                'post_type'=>'og_events',
                'meta_query' => array(
                    array(
                        'key' => 'og-event-start-date-unix',
                        'value' => time(),
                        'compare' => '>='
                    )        
                ),
                'order' => 'asc',
                'posts_per_page' => -1
            );
            $query = new WP_Query($query_args);

            $og_time_format = carbon_get_theme_option( 'og-time-format' );
            if(!$og_time_format): $og_time_format = "F j, Y, g:i a"; endif;

            $og_time_zone = carbon_get_theme_option( 'og-time-zone' );
            if(!$og_time_zone): $og_time_zone = "US/Eastern"; endif;

            date_default_timezone_set($og_time_zone);
            while($query->have_posts()) :
                $query->the_post();

                $nearby_lat = carbon_get_the_post_meta( 'og-event-venue-latitude' );
                $nearby_long = carbon_get_the_post_meta( 'og-event-venue-longitude' );

                $theta = $select_event_lat - $nearby_lat;
                $dist = sin(deg2rad($select_event_lat)) * sin(deg2rad($nearby_lat)) +  cos(deg2rad($select_event_lat)) * cos(deg2rad($nearby_lat)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $dist = $dist * 60 * 1.1515;

                if($dist != "8.3420528255350918e-5"):
                    $distances[] = array(
                        "ID" => get_the_ID(),
                        "slug" => get_post_field( 'post_name' ),
                        "latitude" => $nearby_lat,
                        "longitude" => $nearby_long,                    
                        "distance" => $dist
                    );
                endif;

            endwhile;
            wp_reset_query();

            $distance = array();
            foreach ($distances as $key => $row){
                $distance[$key] = $row['distance'];
            }
            array_multisort($distance, SORT_ASC, $distances);
            $distances = array_slice($distances, 0, 25);

            $data['results'] = $distances;

            return $data;

        }
        

        /**
        * API Venue Events Response (JSON Data)
        *
        * @since 0.7.0
        */
        function api_venue_list_response_data( $data ){

            $venue_uuid = $data['uuid'];

            if(!$page):
                $page = 1;
            endif;

            if(!$limit):
                if( carbon_get_theme_option("og-design-per-page-limit") ):
                    $limit = carbon_get_theme_option("og-design-per-page-limit");
                else:
                    $limit = 24;
                endif;
            endif;        

            $response = array(); $events = array();
            
            $query_args = array(
                'post_type'=>'og_events',
                'meta_query' => array(
                    array(
                        'key' => 'og-event-start-date-unix',
                        'value' => time(),
                        'compare' => '>='
                    ),
                    array(
                        'key' => 'og-event-venue-uuid',
                        'value' => $venue_uuid,
                        'compare' => '='                        
                    )                    
                ),
                'order' => 'asc',
                'posts_per_page' => $limit,
                'paged' => $page,
            );
            $query = new WP_Query($query_args);

            $response['info'] = array(
                "limit" => $limit,
                "current_page" => $page,            
                "next_page" => $page + 1,
                "max_pages" => $query->max_num_pages
            );

            $og_time_format = carbon_get_theme_option( 'og-time-format' );
            if(!$og_time_format): $og_time_format = "F j, Y, g:i a"; endif;

            $og_time_zone = carbon_get_theme_option( 'og-time-zone' );
            if(!$og_time_zone): $og_time_zone = "US/Eastern"; endif;

            date_default_timezone_set($og_time_zone);
            while($query->have_posts()) :
                $query->the_post();

                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_end = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
    
                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_date = date('m/d/y g:i a', $og_output_start); 
                $og_gcal_start_date = date("Ymd" . '__' . "Gi", $og_output_start);

                if(carbon_get_the_post_meta( 'og-event-end-date' )):
                    $end_date = date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-end-date' )));
                    $end_date_unix = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
                    if( date('m/d/y', $og_output_start) == date('m/d/y', $end_date_unix)):
                        $og_output_date = date('m/d/y g:i a', $og_output_start) . " - " . date('g:i a', $end_date_unix);
                    else:
                        $og_output_date = date('m/d/y', $og_output_start) . " - " . date('m/d/y', $end_date_unix);
                    endif;
                    $og_gcal_end_date = date("Ymd" . '__' . "Gi", $end_date_unix);
                else:
                    $end_date = "";
                    $end_date_unix = "";
                endif;

                $og_gcal_start_date = str_replace("__", "T", $og_gcal_start_date);
                $og_gcal_end_date = str_replace("__", "T", $og_gcal_end_date);

                $events[] = array(
                    "id" => get_the_ID(),
                    "slug" => get_post_field( 'post_name' ),
                    "name" => carbon_get_the_post_meta( 'og-event-name' ),
                    "uuid" => carbon_get_the_post_meta( 'og-event-uuid' ),
                    "popularity_score" => carbon_get_the_post_meta( 'og-event-popularity-score' ),
                    "description" => carbon_get_the_post_meta( 'og-event-description' ),
                    "flags" => json_decode(carbon_get_the_post_meta( 'og-event-flags' )),
                    "start_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                    "end_date" => $end_date,
                    "start_date_unix" => carbon_get_the_post_meta( 'og-event-start-date-unix' ),
                    "end_date_unix" => $end_date_unix,
                    "date_formatted" => $og_output_date,
                    "source_url" => carbon_get_the_post_meta( 'og-event-source-url' ),
                    "image_url" => carbon_get_the_post_meta( 'og-event-image-url' ),
                    "ticket_url" => carbon_get_the_post_meta( 'og-event-ticket-url' ),
                    "venue_name" => carbon_get_the_post_meta( 'og-event-venue-name' ),
                    "venue_uuid" => carbon_get_the_post_meta( 'og-event-venue-uuid' ),
                    "venue_address_1" => carbon_get_the_post_meta( 'og-event-venue-address-1' ),
                    "venue_address_2" => carbon_get_the_post_meta( 'og-event-venue-address-2' ),
                    "venue_city" => carbon_get_the_post_meta( 'og-event-venue-city' ),
                    "venue_state" => carbon_get_the_post_meta( 'og-event-venue-state' ),
                    "venue_zip" => carbon_get_the_post_meta( 'og-event-venue-zip-code' ),
                    "venue_country" => carbon_get_the_post_meta( 'og-event-venue-country' ),
                    "latitude" => carbon_get_the_post_meta( 'og-event-venue-latitude' ),
                    "longitude" => carbon_get_the_post_meta( 'og-event-venue-longitude' )
                );

                $venue_name = carbon_get_the_post_meta( 'og-event-venue-name' );
                $venue_uuid = carbon_get_the_post_meta( 'og-event-venue-uuid' );
                $venue_address_1 = carbon_get_the_post_meta( 'og-event-venue-address-1' );
                $venue_address_2 = carbon_get_the_post_meta( 'og-event-venue-address-2' );
                $venue_city = carbon_get_the_post_meta( 'og-event-venue-city' );
                $venue_state = carbon_get_the_post_meta( 'og-event-venue-state' );
                $venue_zip = carbon_get_the_post_meta( 'og-event-venue-zip-code' );
                $venue_country = carbon_get_the_post_meta( 'og-event-venue-country' );
                
            endwhile;
            wp_reset_query();

            $response['events'] = $events;
            $response['data'] = array(
                "venue_name" => $venue_name,
                "venue_uuid" => $venue_uuid,
                "venue_address_1" => $venue_address_1,
                "venue_address_2" => $venue_address_2,
                "venue_city" => $venue_city,
                "venue_state" => $venue_state,
                "venue_zip" => $venue_zip,
                "venue_country" => $venue_country,
            );

            return $response;

        }


        /**
        * API Related/Suggested Events Response (JSON Data)
        *
        * @since 0.7.0
        */
        function api_related_and_suggested_response_data( $data ){

            extract($_GET);

            $parent_id = $data['id'];
            $flags = explode(",", $flags);

            if(!$limit):
                $limit = 4;
            endif;

            $response = array(); $events = array();
            $response['data'] = array(
                "parent_id" => $parent_id,
                "related_flags" => $flags
            );

            $dynamic_flags_query_args = array(
                "relation" => "OR"
            );
            foreach($flags as $flag):
                $dynamic_flags_query_args[] = array(
                    'key' => 'og-event-flags',
                    'value' => '"' . $flag . '"',
                    'compare' => 'LIKE'    
                );
            endforeach;

            $query_args = array(
                'post_type'=>'og_events',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'og-event-start-date-unix',
                        'value' => time(),
                        'compare' => '>='
                    ),
                ),
                'order' => 'asc',
                'posts_per_page' => $limit,
                'post__not_in' => array($parent_id)
            );

            $query_args['meta_query'][] = $dynamic_flags_query_args;
            $query = new WP_Query($query_args);

            $og_time_format = carbon_get_theme_option( 'og-time-format' );
            if(!$og_time_format): $og_time_format = "F j, Y, g:i a"; endif;

            $og_time_zone = carbon_get_theme_option( 'og-time-zone' );
            if(!$og_time_zone): $og_time_zone = "US/Eastern"; endif;

            date_default_timezone_set($og_time_zone);
            while($query->have_posts()) :
                $query->the_post();

                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_end = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
    
                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_date = date('m/d/y g:i a', $og_output_start); 
                $og_gcal_start_date = date("Ymd" . '__' . "Gi", $og_output_start);

                if(carbon_get_the_post_meta( 'og-event-end-date' )):
                    $end_date = date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-end-date' )));
                    $end_date_unix = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
                    if( date('m/d/y', $og_output_start) == date('m/d/y', $end_date_unix)):
                        $og_output_date = date('m/d/y g:i a', $og_output_start) . " - " . date('g:i a', $end_date_unix);
                    else:
                        $og_output_date = date('m/d/y', $og_output_start) . " - " . date('m/d/y', $end_date_unix);
                    endif;
                    $og_gcal_end_date = date("Ymd" . '__' . "Gi", $end_date_unix);
                else:
                    $end_date = "";
                    $end_date_unix = "";
                endif;

                $og_gcal_start_date = str_replace("__", "T", $og_gcal_start_date);
                $og_gcal_end_date = str_replace("__", "T", $og_gcal_end_date);

                $events[] = array(
                    "id" => get_the_ID(),
                    "slug" => get_post_field( 'post_name' ),
                    "name" => carbon_get_the_post_meta( 'og-event-name' ),
                    "uuid" => carbon_get_the_post_meta( 'og-event-uuid' ),
                    "popularity_score" => carbon_get_the_post_meta( 'og-event-popularity-score' ),
                    "description" => carbon_get_the_post_meta( 'og-event-description' ),
                    "flags" => json_decode(carbon_get_the_post_meta( 'og-event-flags' )),
                    "start_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                    "end_date" => $end_date,
                    "start_date_unix" => carbon_get_the_post_meta( 'og-event-start-date-unix' ),
                    "end_date_unix" => $end_date_unix,
                    "date_formatted" => $og_output_date,
                    "source_url" => carbon_get_the_post_meta( 'og-event-source-url' ),
                    "image_url" => carbon_get_the_post_meta( 'og-event-image-url' ),
                    "ticket_url" => carbon_get_the_post_meta( 'og-event-ticket-url' ),
                    "venue_name" => carbon_get_the_post_meta( 'og-event-venue-name' ),
                    "venue_uuid" => carbon_get_the_post_meta( 'og-event-venue-uuid' ),
                    "venue_address_1" => carbon_get_the_post_meta( 'og-event-venue-address-1' ),
                    "venue_address_2" => carbon_get_the_post_meta( 'og-event-venue-address-2' ),
                    "venue_city" => carbon_get_the_post_meta( 'og-event-venue-city' ),
                    "venue_state" => carbon_get_the_post_meta( 'og-event-venue-state' ),
                    "venue_zip" => carbon_get_the_post_meta( 'og-event-venue-zip-code' ),
                    "venue_country" => carbon_get_the_post_meta( 'og-event-venue-country' ),
                    "latitude" => carbon_get_the_post_meta( 'og-event-venue-latitude' ),
                    "longitude" => carbon_get_the_post_meta( 'og-event-venue-longitude' )
                );

                $venue_name = carbon_get_the_post_meta( 'og-event-venue-name' );
                $venue_uuid = carbon_get_the_post_meta( 'og-event-venue-uuid' );
                $venue_address_1 = carbon_get_the_post_meta( 'og-event-venue-address-1' );
                $venue_address_2 = carbon_get_the_post_meta( 'og-event-venue-address-2' );
                $venue_city = carbon_get_the_post_meta( 'og-event-venue-city' );
                $venue_state = carbon_get_the_post_meta( 'og-event-venue-state' );
                $venue_zip = carbon_get_the_post_meta( 'og-event-venue-zip-code' );
                $venue_country = carbon_get_the_post_meta( 'og-event-venue-country' );
                
            endwhile;
            wp_reset_query();

            $response['events'] = $events;

            return $response;

        }


        /**
        * API Personalized Events Response (JSON Data)
        *
        * @since 0.9.0
        */
        function api_personalized_response_data(){

            extract($_GET);
            $flags = explode(",", $flags);

            if(!$limit):
                $limit = 4;
            endif;

            $response = array(); $flag_count = array(); $events = array();
            $counts = array_count_values($flags);

            foreach($flags as $flag):
                if($flag != "undefined"):
                    $flag_count[$flag] = array(
                        "flag" => $flag,
                        "count" => $counts[$flag]
                    );
                endif;
            endforeach;

            $dynamic_flags_query_args = array(
                "relation" => "OR"
            );
            foreach($flag_count as $flag):
                $dynamic_flags_query_args[] = array(
                    'key' => 'og-event-flags',
                    'value' => '"' . $flag['flag'] . '"',
                    'compare' => 'LIKE'    
                );
            endforeach;

            $query_args = array(
                'post_type'=>'og_events',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'og-event-start-date-unix',
                        'value' => time(),
                        'compare' => '>='
                    ),
                ),
                'meta_key' => '_og-event-popularity-score',
                'orderby' => 'meta_value',
                'order' => 'DESC',
                'posts_per_page' => $limit,
            );
            $query_args['meta_query'][] = $dynamic_flags_query_args;

            $query = new WP_Query($query_args);

            $og_time_format = carbon_get_theme_option( 'og-time-format' );
            if(!$og_time_format): $og_time_format = "F j, Y, g:i a"; endif;

            $og_time_zone = carbon_get_theme_option( 'og-time-zone' );
            if(!$og_time_zone): $og_time_zone = "US/Eastern"; endif;

            date_default_timezone_set($og_time_zone);
            while($query->have_posts()) :
                $query->the_post();

                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_end = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
    
                $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                $og_output_date = date('m/d/y g:i a', $og_output_start); 
                $og_gcal_start_date = date("Ymd" . '__' . "Gi", $og_output_start);

                if(carbon_get_the_post_meta( 'og-event-end-date' )):
                    $end_date = date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-end-date' )));
                    $end_date_unix = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
                    if( date('m/d/y', $og_output_start) == date('m/d/y', $end_date_unix)):
                        $og_output_date = date('m/d/y g:i a', $og_output_start) . " - " . date('g:i a', $end_date_unix);
                    else:
                        $og_output_date = date('m/d/y', $og_output_start) . " - " . date('m/d/y', $end_date_unix);
                    endif;
                    $og_gcal_end_date = date("Ymd" . '__' . "Gi", $end_date_unix);
                else:
                    $end_date = "";
                    $end_date_unix = "";
                endif;

                $og_gcal_start_date = str_replace("__", "T", $og_gcal_start_date);
                $og_gcal_end_date = str_replace("__", "T", $og_gcal_end_date);

                $events[] = array(
                    "id" => get_the_ID(),
                    "slug" => get_post_field( 'post_name' ),
                    "name" => carbon_get_the_post_meta( 'og-event-name' ),
                    "uuid" => carbon_get_the_post_meta( 'og-event-uuid' ),
                    "popularity_score" => carbon_get_the_post_meta( 'og-event-popularity-score' ),
                    "description" => carbon_get_the_post_meta( 'og-event-description' ),
                    "flags" => json_decode(carbon_get_the_post_meta( 'og-event-flags' )),
                    "start_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                    "end_date" => $end_date,
                    "start_date_unix" => carbon_get_the_post_meta( 'og-event-start-date-unix' ),
                    "end_date_unix" => $end_date_unix,
                    "date_formatted" => $og_output_date,
                    "source_url" => carbon_get_the_post_meta( 'og-event-source-url' ),
                    "image_url" => carbon_get_the_post_meta( 'og-event-image-url' ),
                    "ticket_url" => carbon_get_the_post_meta( 'og-event-ticket-url' ),
                    "venue_name" => carbon_get_the_post_meta( 'og-event-venue-name' ),
                    "venue_uuid" => carbon_get_the_post_meta( 'og-event-venue-uuid' ),
                    "venue_address_1" => carbon_get_the_post_meta( 'og-event-venue-address-1' ),
                    "venue_address_2" => carbon_get_the_post_meta( 'og-event-venue-address-2' ),
                    "venue_city" => carbon_get_the_post_meta( 'og-event-venue-city' ),
                    "venue_state" => carbon_get_the_post_meta( 'og-event-venue-state' ),
                    "venue_zip" => carbon_get_the_post_meta( 'og-event-venue-zip-code' ),
                    "venue_country" => carbon_get_the_post_meta( 'og-event-venue-country' ),
                    "latitude" => carbon_get_the_post_meta( 'og-event-venue-latitude' ),
                    "longitude" => carbon_get_the_post_meta( 'og-event-venue-longitude' )
                );

                $venue_name = carbon_get_the_post_meta( 'og-event-venue-name' );
                $venue_uuid = carbon_get_the_post_meta( 'og-event-venue-uuid' );
                $venue_address_1 = carbon_get_the_post_meta( 'og-event-venue-address-1' );
                $venue_address_2 = carbon_get_the_post_meta( 'og-event-venue-address-2' );
                $venue_city = carbon_get_the_post_meta( 'og-event-venue-city' );
                $venue_state = carbon_get_the_post_meta( 'og-event-venue-state' );
                $venue_zip = carbon_get_the_post_meta( 'og-event-venue-zip-code' );
                $venue_country = carbon_get_the_post_meta( 'og-event-venue-country' );
                
            endwhile;
            wp_reset_query();

            $response['events'] = $events;

            $response['total'] = $query->post_count;
            $response['info'] = $flag_count;

            return $response;

        }


        /**
        * API Bucket Lists Events Response (JSON Data)
        *
        * @since 1.2.0
        */
        function api_bucket_response_data( $data ){

            extract($_GET);

            $response = array(); $response_events = array();
            if($events){
                
                $events = explode(",", $events);
                $events = array_unique($events);
                $response['filter']['events'] = $events;
    
                $query_args = array(
                    'post_type'=>'og_events',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'og-event-start-date-unix',
                            'value' => time(),
                            'compare' => '>='
                        ),
                    ),
                    'meta_key' => '_og-event-popularity-score',
                    'orderby' => 'meta_value',
                    'order' => 'DESC',
                    'posts_per_page' => -1,
                );

                $dynamic_flags_query_args = array(
                    "relation" => "OR"
                );
                foreach($events as $event):
                    $dynamic_flags_query_args[] = array(
                        'key' => '_og-event-uuid',
                        'value' => $event,
                        'compare' => '='    
                    );
                endforeach;

                $query_args['meta_query'][] = $dynamic_flags_query_args;

                $query = new WP_Query($query_args);
    
                $og_time_format = carbon_get_theme_option( 'og-time-format' );
                if(!$og_time_format): $og_time_format = "F j, Y, g:i a"; endif;
    
                $og_time_zone = carbon_get_theme_option( 'og-time-zone' );
                if(!$og_time_zone): $og_time_zone = "US/Eastern"; endif;
    
                date_default_timezone_set($og_time_zone);
                while($query->have_posts()) :
                    $query->the_post();
    
                    $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                    $og_output_end = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
        
                    $og_output_start = strtotime(carbon_get_the_post_meta( 'og-event-start-date' ));
                    $og_output_date = date('m/d/y g:i a', $og_output_start); 
                    $og_gcal_start_date = date("Ymd" . '__' . "Gi", $og_output_start);
    
                    if(carbon_get_the_post_meta( 'og-event-end-date' )):
                        $end_date = date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-end-date' )));
                        $end_date_unix = strtotime(carbon_get_the_post_meta( 'og-event-end-date' ));
                        if( date('m/d/y', $og_output_start) == date('m/d/y', $end_date_unix)):
                            $og_output_date = date('m/d/y g:i a', $og_output_start) . " - " . date('g:i a', $end_date_unix);
                        else:
                            $og_output_date = date('m/d/y', $og_output_start) . " - " . date('m/d/y', $end_date_unix);
                        endif;
                        $og_gcal_end_date = date("Ymd" . '__' . "Gi", $end_date_unix);
                    else:
                        $end_date = "";
                        $end_date_unix = "";
                    endif;
    
                    $og_gcal_start_date = str_replace("__", "T", $og_gcal_start_date);
                    $og_gcal_end_date = str_replace("__", "T", $og_gcal_end_date);
    
                    $response_events[] = array(
                        "id" => get_the_ID(),
                        "slug" => get_post_field( 'post_name' ),
                        "name" => carbon_get_the_post_meta( 'og-event-name' ),
                        "uuid" => carbon_get_the_post_meta( 'og-event-uuid' ),
                        "popularity_score" => carbon_get_the_post_meta( 'og-event-popularity-score' ),
                        "description" => carbon_get_the_post_meta( 'og-event-description' ),
                        "flags" => json_decode(carbon_get_the_post_meta( 'og-event-flags' )),
                        "start_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                        "end_date" => $end_date,
                        "start_date_unix" => carbon_get_the_post_meta( 'og-event-start-date-unix' ),
                        "end_date_unix" => $end_date_unix,
                        "date_formatted" => $og_output_date,
                        "source_url" => carbon_get_the_post_meta( 'og-event-source-url' ),
                        "image_url" => carbon_get_the_post_meta( 'og-event-image-url' ),
                        "ticket_url" => carbon_get_the_post_meta( 'og-event-ticket-url' ),
                        "venue_name" => carbon_get_the_post_meta( 'og-event-venue-name' ),
                        "venue_uuid" => carbon_get_the_post_meta( 'og-event-venue-uuid' ),
                        "venue_address_1" => carbon_get_the_post_meta( 'og-event-venue-address-1' ),
                        "venue_address_2" => carbon_get_the_post_meta( 'og-event-venue-address-2' ),
                        "venue_city" => carbon_get_the_post_meta( 'og-event-venue-city' ),
                        "venue_state" => carbon_get_the_post_meta( 'og-event-venue-state' ),
                        "venue_zip" => carbon_get_the_post_meta( 'og-event-venue-zip-code' ),
                        "venue_country" => carbon_get_the_post_meta( 'og-event-venue-country' ),
                        "latitude" => carbon_get_the_post_meta( 'og-event-venue-latitude' ),
                        "longitude" => carbon_get_the_post_meta( 'og-event-venue-longitude' )
                    );
    
                    $venue_name = carbon_get_the_post_meta( 'og-event-venue-name' );
                    $venue_uuid = carbon_get_the_post_meta( 'og-event-venue-uuid' );
                    $venue_address_1 = carbon_get_the_post_meta( 'og-event-venue-address-1' );
                    $venue_address_2 = carbon_get_the_post_meta( 'og-event-venue-address-2' );
                    $venue_city = carbon_get_the_post_meta( 'og-event-venue-city' );
                    $venue_state = carbon_get_the_post_meta( 'og-event-venue-state' );
                    $venue_zip = carbon_get_the_post_meta( 'og-event-venue-zip-code' );
                    $venue_country = carbon_get_the_post_meta( 'og-event-venue-country' );
                    
                endwhile;
                wp_reset_query();

                shuffle($response_events);
                if(!$limit):
                    $response_events = array_slice($response_events, 0, 4);
                endif;
                
                $response['total'] = count($response_events);
                $response['events'] = $response_events;

            }else{

                $response['error'] = "true";

            }

            return $response;

        }


        /**
        * Allow for `pretty urls`
        *
        * @since 0.5.0
        */
        function og_pretty_urls(){
            
            add_rewrite_rule(
                "^events/(.+?)",
                "index.php?pagename=events",
                "top");

        }


        /**
        * Removal of Events CPT in WordPress
        *
        * @since 0.5.0
        */
        function og_nav_cleanup() {

            remove_menu_page( 'edit.php?post_type=og_events' );

        }


        /**
        * Debug / Testing Support
        *
        * @since 0.1.0
        */
        function debug_plugin_functions(){
            
            $og_dev_key = carbon_get_theme_option( 'og-developer-security-key' );

            if(isset($_GET['og_dev_key']) && $_GET['og_dev_key'] == $og_dev_key):
                if($_GET['occasiongenius_action'] == "import"):
                    $this->import_events();
                elseif($_GET['occasiongenius_action'] == "flags"):
                    $flags = $this->og_api_flags();
                    print_r($flags);
                elseif($_GET['occasiongenius_action'] == "areas"):
                    $areas = $this->og_api_areas();
                    print_r($areas);            
                elseif($_GET['occasiongenius_action'] == "purge"):
                    $this->purge_events();
                endif;
            endif;

        }

    }

}
$crowdcue = new Crowdcue();