<?php
/**
 * Plugin Name: WP Job Manager Applications - My Custom Content
 * Description: A modification of Astoundify's first Pre-Defined Regions Plugin.  Loads my custom content to WP Job Manager Applications
 * Author:      Adam Hurlebaus
 * Version:     1.0.0
 * Text Domain: ahjma
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class My_Custom_Job_Manager_Application_Content {

	/**
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Make sure only one instance is only running.
	 */
	public static function instance() {
		if ( ! isset ( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Start things up.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Set some smart defaults to class variables. Allow some of them to be
	 * filtered to allow for early overriding.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	private function setup_globals() {
		$this->file         = __FILE__;

		$this->basename     = apply_filters( 'ahjma_plugin_basenname', plugin_basename( $this->file ) );
		$this->plugin_dir   = apply_filters( 'ahjma_plugin_dir_path',  plugin_dir_path( $this->file ) );
		$this->plugin_url   = apply_filters( 'ahjma_plugin_dir_url',   plugin_dir_url ( $this->file ) );

		$this->lang_dir     = apply_filters( 'ahjma_lang_dir',     trailingslashit( $this->plugin_dir . 'languages' ) );

		$this->domain       = 'ahjma';
	}

	/**
	 * Setup the default hooks and actions for editing cutsom taxonomies and custom fields
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	private function setup_actions() {
	    	add_filter( 'job_application_form_fields', array( $this, 'form_fields' ) );
	    	add_filter( 'job_application_form_posted_meta', array( $this, 'update_application_data' ), 10, 2 );
	    	add_filter( 'job_application_content', array( $this, 'custom_content' ) );
	    	//add_filter( 'job_manager_job_listing_data_fields', array( $this, 'job_listing_data_fields' ), 15 );

	    	$this->load_textdomain();
	}


	/**
	 * Add custom fields to the submission form.
	 *
	 * @since 1.0
	 */
	function form_fields( $fields ) {
	  	$fields['property_name'] = array(
			'label'       => __( 'Property Name', 'wp-job-manager-applications' ),
			'type'        => 'text',
			'required'    => true,
			'priority'    => '1'
		);

		return $fields;

	}

	/**
	 * When the form is submitted, update the data.
	 *
	 * @since 1.0
	 */
	function update_application_data( $application_id, $values ) {
		update_post_meta( $application_id, '_property_name', $values['property_name'] );

	}

	/**
	 * Add custom notes to note data
	 *
	 * @param  object $application
	 */
	function custom_content( $application ) {
		if ( 'job_application' === $application->post_type ) {
			
			global $post;

  			$property_name = get_post_meta( $post->ID, '_property_name', true );

  			if ( $property_name ) {
				echo $property_name;
			}
		}
	}


	/**
	 * Create Admin write panels for custom content.
	 *
	 * @since 1.0
	 */
	/**function job_listing_data_fields( $fields ) {
		
		$fields['_property_name'] = array(
				'label' => __( 'Property Name', 'wp-job-manager-applications' ),
				'placeholder' => __( '' ),
				'description' => __( 'Leave this blank if there is no name', 'wp-job-manager-applications' )
			);

		return $fields;
	}*/

	/**
	 * Loads the plugin language files
	 *
	 * @since 1.0
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/' . $this->domain . '/' . $mofile;

		// Look in global /wp-content/languages/ahjma folder
		if ( file_exists( $mofile_global ) ) {
			return load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/ahjma/languages/ folder
		} elseif ( file_exists( $mofile_local ) ) {
			return load_textdomain( $this->domain, $mofile_local );
		}

		return false;
	}
}

/**
 * Start things up.
 *
 * Use this function instead of a global.
 *
 * $ahjma = ahjma();
 *
 * @since 1.0
 */
function ahjma() {
	return My_Custom_Job_Manager_Application_Content::instance();
}

ahjma();

/**
 * Custom widgets
 *
 * @since 1.1
 */
function ahjma_widgets_init() {
	if ( ! class_exists( 'Custom_Application_Widget' ) )
		return;

	$ahjma = ahjma();

	include_once( $ahjma->plugin_dir . '/widgets.php' );

	register_widget( 'My_Custom_Job_Manager_Application_Content_Widget' );
}
add_action( 'after_setup_theme', 'ahjma_widgets_init', 11 );
