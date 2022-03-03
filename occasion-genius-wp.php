<?php
/**
 * @wordpress-plugin
 * Plugin Name: OccasionGenius
 * Plugin URI: https://occasiongenius.com/
 * Description: OccasionGenius allows you to easily output a beautiful and simple event without any coding.
 * Version: 0.3.0
 * Author: Nicholas Mercer (@kittabit)
 * Author URI: https://kittabit.com
 */

// TODO:  Imports - Import Requires 2x run (issue with getting ID)
// TODO:  React - Routing & Details Pages
// TODO:  Mosaic Grid Style Design
// TODO:  Hit "Run Once" Style Page (iniital import of flags, areas, etc) - update_db_check()
// TODO:  React Components

defined( 'ABSPATH' ) or die( 'Direct Access Not Allowed.' );

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Carbon_Fields\Container;
use Carbon_Fields\Block;
use Carbon_Fields\Field;

define( 'Carbon_Fields\URL', $_SERVER['HTTP_X_FORWARDED_PROTO'] . "://" . $_SERVER['HTTP_X_FORWARDED_HOST'] . "/wp-content/plugins/windycoat/vendor/htmlburger/carbon-fields" );

class OccasionGenius {

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
        $this->OG_DB_VERSION = "0.3.0";

        register_activation_hook( __FILE__, array($this, 'og_install') );

        if(!is_admin()):
            add_filter( 'script_loader_tag', array($this, "script_loader_wc_widget_js"), 10, 2);
            add_action( 'wp_enqueue_scripts', array($this, "enqueue_wc_widget_js"));
        endif; 
        add_shortcode( 'occassiongenius_events', array($this, "shortcode_occassiongenius_events"));
        add_action( 'carbon_fields_register_fields', array($this, 'plugin_settings_page_and_blocks') );
        add_action( 'init', array($this, 'register_post_types') );
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
        add_action( 'wp', array($this, 'og_scheduled_tasks') );
        add_action( 'og_sync_events', array($this, 'import_events') );

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

        if ( $installed_ver != $this->OG_DB_VERSION ):
            update_option("wc_og_version", $this->OG_DB_VERSION);
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
        $areas = $this->og_api_areas();
        
        $time_formats = array(
            "F j, Y, g:i a" => date("F j, Y, g:i a")
        );

        $timezones = array(
            'Pacific/Midway'       => "(GMT-11:00) Midway Island",
            'US/Samoa'             => "(GMT-11:00) Samoa",
            'US/Hawaii'            => "(GMT-10:00) Hawaii",
            'US/Alaska'            => "(GMT-09:00) Alaska",
            'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
            'America/Tijuana'      => "(GMT-08:00) Tijuana",
            'US/Arizona'           => "(GMT-07:00) Arizona",
            'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
            'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
            'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
            'America/Mexico_City'  => "(GMT-06:00) Mexico City",
            'America/Monterrey'    => "(GMT-06:00) Monterrey",
            'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
            'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
            'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
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

        Container::make( 'theme_options', 'OccasionGenius' )->set_page_parent("options-general.php")->add_fields( array(
            Field::make( 'separator', 'og_basic_settings', 'Basic Settings' )->set_classes( 'og-admin-heading' ),
            Field::make( 'text', 'og-token-key', "Token Key"),
            Field::make( 'select', 'og-time-format', 'Time Format' )->add_options( $time_formats )->set_default_value('F j, Y, g:i a')->set_width( 50 ),
            Field::make( 'select', 'og-time-zone', 'Time Zone' )->add_options( $timezones )->set_default_value('US/Eastern')->set_width( 50 ),
            Field::make( "multiselect", "og-disabled-flags", "Disabled Flags" )->add_options( $flags )->set_width( 50 ),
            Field::make( "multiselect", "og-disabled-areas", "Disabled Areas" )->add_options( $areas )->set_width( 50 ),
            Field::make( 'separator', 'og_design_options', 'Design Settings' )->set_classes( 'og-admin-heading' ),
            Field::make( 'text', 'og-design-per-page-limit', "Events Per Page")->set_default_value( "24" ),
            Field::make( 'separator', 'og_developer_settings', 'Developer Settings' )->set_classes( 'og-admin-heading' ),
            Field::make( 'text', 'og-developer-security-key', "Developer Security Key")->set_default_value( md5(rand() . "-og-" . time() ) )
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
    * Import Events via API
    *
    * @since 0.1.0
    */
    function import_events($url=NULL){

        if ( ! is_admin() ) {
            require_once( ABSPATH . 'wp-admin/includes/post.php' );
        }

        $og_token = carbon_get_theme_option( 'og-token-key' );

        if(!$url){
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+1 Month"));
            $url = "https://v2.api.occasiongenius.com/api/events/?start_date=" . $start_date . "&end_date=" . $end_date . "&limit=25";
        }
        
        $crl = curl_init();
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = 'accept: application/json';
        $headers[] = 'Authorization: Token ' . $og_token;
        
        curl_setopt($crl, CURLOPT_HTTPHEADER,$headers);
        $rest = curl_exec($crl);
        curl_close($crl);
        
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

            carbon_set_post_meta( $event_local_id, 'og-event-name', $event->name );
            carbon_set_post_meta( $event_local_id, 'og-event-uuid', $event->uuid );
            carbon_set_post_meta( $event_local_id, 'og-event-popularity-score', $event->popularity_score );
            carbon_set_post_meta( $event_local_id, 'og-event-description', $event->description );
            carbon_set_post_meta( $event_local_id, 'og-event-flags', json_encode($event->flags) );
            carbon_set_post_meta( $event_local_id, 'og-event-start-date', $event->start_date );
            carbon_set_post_meta( $event_local_id, 'og-event-end-date', $event->end_date );
            carbon_set_post_meta( $event_local_id, 'og-event-start-date-unix', strtotime($event->start_date) );
            carbon_set_post_meta( $event_local_id, 'og-event-end-date-unix', strtotime($event->end_date) );
            carbon_set_post_meta( $event_local_id, 'og-event-event-dates', json_encode($event->event_dates) );
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
    * Get Event Flags via API
    *
    * @since 0.2.0
    */
    function og_api_flags($url=NULL,$flags=NULL){

        if( get_option("og_api_flags") && count(json_decode( get_option("og_api_flags") ) ) > 0 ):
            return (array) json_decode( get_option("og_api_flags"), true );
        else:
            $og_token = carbon_get_theme_option( 'og-token-key' );

            if(!$url){
                $flags = array();
                $url = "https://v2.api.occasiongenius.com/api/flag_definitions/";
            }
            
            $crl = curl_init();
            curl_setopt($crl, CURLOPT_URL, $url);
            curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);

            $headers = array();
            $headers[] = 'accept: application/json';
            $headers[] = 'Authorization: Token ' . $og_token;

            curl_setopt($crl, CURLOPT_HTTPHEADER,$headers);
            $rest = curl_exec($crl);
            curl_close($crl);
            
            $results = json_decode($rest);
            foreach($results->results as $flag){
                $flags[$flag->id] = $flag->name;
            }

            if($results->next):
                $this->og_api_flags($results->next, $flags);
            else:
                update_option("og_api_flags", json_encode($flags));
                return $flags;
            endif;
        endif;

    }


    /**
    * Get Area Flags via API
    *
    * @since 0.2.0
    */
    function og_api_areas($url=NULL,$areas=NULL){

        if( get_option("og_api_areas") && count(json_decode( get_option("og_api_areas") ) ) > 0 ):
            return (array) json_decode( get_option("og_api_areas"), true );
        else:
            $og_token = carbon_get_theme_option( 'og-token-key' );

            if(!$url){
                $areas = array();
                $url = "https://v2.api.occasiongenius.com/api/areas/";
            }
            
            $crl = curl_init();
            curl_setopt($crl, CURLOPT_URL, $url);
            curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);

            $headers = array();
            $headers[] = 'accept: application/json';
            $headers[] = 'Authorization: Token ' . $og_token;
            
            curl_setopt($crl, CURLOPT_HTTPHEADER,$headers);
            $rest = curl_exec($crl);
            curl_close($crl);
            
            $results = json_decode($rest);
            foreach($results->results as $area){
                $areas[$area->uuid] = $area->name;
            }

            if($results->next):
                $this->og_api_areas($results->next, $areas);
            else:
                update_option("og_api_areas", json_encode($areas));
                return $areas;
            endif;
        endif;

    }


    /**
    * Optimize JS Loading for `wc-*` Assets
    *
    * @since 0.1.0
    */
    function script_loader_wc_widget_js($tag, $handle){

        if ( ! preg_match( '/^occasiongenius-/', $handle ) ) { return $tag; }
        return str_replace( ' src', ' async defer src', $tag );

    }
    

    /**
    * Load all JS/CSS assets from 'OccasionGenius' React Widget
    *
    * @since 0.1.0
    */
    function enqueue_wc_widget_js(){

        $asset_manifest = json_decode( file_get_contents( $this->OG_ASSET_MANIFEST ), true )['files'];
        
        if ( isset( $asset_manifest[ 'main.css' ] ) ) {
          wp_enqueue_style( 'occasiongenius', get_site_url() . $asset_manifest[ 'main.css' ] );
        }
    
        wp_enqueue_script( 'occasiongenius-main', get_site_url() . $asset_manifest[ 'main.js' ], array(), null, true );
    
        foreach ( $asset_manifest as $key => $value ) {
          if ( preg_match( '@static/js/(.*)\.chunk\.js@', $key, $matches ) ) {
            if ( $matches && is_array( $matches ) && count( $matches ) === 2 ) {
              $name = "occasiongenius-" . preg_replace( '/[^A-Za-z0-9_]/', '-', $matches[1] );
              wp_enqueue_script( $name, get_site_url() . $value, array( 'occasiongenius-main' ), null, true );
            }
          }
    
          if ( preg_match( '@static/css/(.*)\.chunk\.css@', $key, $matches ) ) {
            if ( $matches && is_array( $matches ) && count( $matches ) == 2 ) {
              $name = "occasiongenius-" . preg_replace( '/[^A-Za-z0-9_]/', '-', $matches[1] );
              wp_enqueue_style( $name, get_site_url() . $value, array( 'occasiongenius' ), null );
            }
          }
        }

    }


    /**
    * [occassiongenius_events] Shortcode Output
    *
    * @since 0.1.0
    */
    function shortcode_occassiongenius_events( $atts ){

        $default_atts = array();
        $args = shortcode_atts( $default_atts, $atts );

        ob_start();
        ?>
        <div class="og-root"></div>
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
                echo "[Events Container]";
            else:
                echo do_shortcode("[occassiongenius_events]");
            endif;
        } );        
        
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
            endif;
        endif;

    }


    /**
    * API Response (JSON Data)
    *
    * @since 0.1.0
    */
    function api_response_data(){

        extract($_GET);
        
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

        $events = array();
        $query = new WP_Query( array(
            'post_type'=>'og_events',
            'orderby' => 'og-event-start-date-unix',
            'order' => 'asc',
            'posts_per_page' => $limit,
            'paged' => $page,
            'meta_query'=>array(
                'text_field' => array(
                    'key' => 'og-event-start-date-unix',
                    'compare' => 'EXISTS',
                ),
            ),
        ) );

        $events['info'] = array(
            "limit" => $limit,
            "current_page" => $page,            
            "next_page" => $page + 1,
            "max_pages" => $query->max_num_pages
        );

        while($query->have_posts()) :
            $query->the_post();

            $events['events'][] = array(
                "id" => get_the_ID(),
                "name" => carbon_get_the_post_meta( 'og-event-name' ),
                "uuid" => carbon_get_the_post_meta( 'og-event-uuid' ),
                "popularity_score" => carbon_get_the_post_meta( 'og-event-popularity-score' ),
                "description" => carbon_get_the_post_meta( 'og-event-description' ),
                "flags" => carbon_get_the_post_meta( 'og-event-flags' ),
                "start_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-start-date' ))),
                "end_date" => date($og_time_format, strtotime(carbon_get_the_post_meta( 'og-event-end-date' ))),
                "start_date_unix" => carbon_get_the_post_meta( 'og-event-start-date-unix' ),
                "end_date_unix" => carbon_get_the_post_meta( 'og-event-end-date-unix' ),
                "event_dates" => carbon_get_the_post_meta( 'og-event-event-dates' ),
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

}
$occasiongenius = new OccasionGenius();