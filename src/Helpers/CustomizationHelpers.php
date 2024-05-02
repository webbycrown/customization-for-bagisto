<?php

/**
 * Namespace declaration for helper classes and functions related to customization in the Webbycrown application.
 * This namespace encapsulates various utility methods and functionalities to assist with customizing
 * different aspects of the application's behavior or appearance.
 */
namespace Webbycrown\Customization\Helpers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Webbycrown\Customization\Models\CustomizationPages;
use Webbycrown\Customization\Models\CustomizationSections;
use Webbycrown\Customization\Models\CustomizationDetails;
use Webbycrown\Customization\Models\CustomizationSettings;
use Webbycrown\Customization\Http\Controllers\Shop\CustomizationController as ShopCustomizationController;
use Webkul\Product\Models\ProductFlat;
use Webkul\Product\Models\Product;
use Webkul\Category\Models\Category;
use Webkul\Product\Repositories\ProductRepository;
use Webbycrown\BlogBagisto\Models\Blog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Webkul\Core\Models\CoreConfig;

/**
 * This class contains helper methods for customization purposes. 
 * It likely provides various functions or utilities to customize 
 * certain aspects of an application or system. Developers can utilize 
 * these methods to tailor the behavior or appearance of the software 
 * according to specific requirements or preferences.
 */
class CustomizationHelpers
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Retrieves customization setting data.
     * 
     * This function retrieves data related to customization settings, 
     * which can be used to tailor the application's behavior or appearance.
     * 
     * @return array Customization setting data.
     */
    public static function get_customization_setting_data()
    {
        $customizations = array();

        $pages = CustomizationPages::get();

        if ( $pages && count( $pages ) > 0 ) {

            foreach ( $pages as $page_key => $page_val ) {

                $pages_arr = array( 'title' => $page_val->title, 'slug' => $page_val->slug, 'sections' => array() );

                $sections = CustomizationSections::where( 'page_slug', $page_val->slug )->get();

                if ( $sections && count( $sections ) > 0 ) {

                    foreach ( $sections as $section_key => $section_val ) {

                        $sections_arr = array( 'title' => $section_val->title, 'slug' => $section_val->slug, 'fields' => array() );

                        $fields = CustomizationSettings::where( 'page_slug', $section_val->page_slug )
                        ->where( 'section_slug', $section_val->slug )
                        ->where( 'status', 1 )
                        ->where( 'parent_id', 0 )
                        ->get();

                        if ( $fields && count( $fields ) > 0 ) {

                            foreach ( $fields as $field_key => $field_val ) {

                                $options_arr = $repeater_fields = array();

                                $other_settings = $field_val->other_settings;

                                $other_settings = (array)json_decode( $other_settings );

                                if ( $field_val->type == 'select' && array_key_exists( 'options', $other_settings ) ) {

                                    $options_arr = CustomizationHelpers::get_select_option_format_data( $other_settings[ 'options' ] );

                                }

                                if ( $field_val->type == 'repeater' ) {

                                    $repeater_fields_details = CustomizationSettings::where( 'page_slug', $field_val->page_slug )
                                    ->where( 'section_slug', $field_val->section_slug )
                                    ->where( 'status', 1 )
                                    ->where( 'parent_id', $field_val->id )
                                    ->where( 'setting_type', 'repeater' )
                                    ->get();

                                    if ( $repeater_fields_details && count( $repeater_fields_details ) > 0 ) {

                                        foreach ( $repeater_fields_details as $rp_key => $rp_field_val ) {

                                            $rp_options_arr = array();

                                            $rp_other_settings = $rp_field_val->other_settings;

                                            $rp_other_settings = (array)json_decode( $rp_other_settings );

                                            if ( $rp_field_val->type == 'select' && array_key_exists( 'options', $rp_other_settings ) ) {

                                                $rp_options_arr = CustomizationHelpers::get_select_option_format_data( $rp_other_settings[ 'options' ] );

                                            }

                                            $repeater_fields[] = array(
                                                'name'      => $rp_field_val->name,
                                                'type'      => $rp_field_val->type,
                                                'title'     => $rp_field_val->title,
                                                'required'  => ( (int)$rp_field_val->required == 1 ) ? true : false,
                                                'multiple'  => ( (int)$rp_field_val->multiple == 1 ) ? true : false,
                                                'options'   => $rp_options_arr,
                                            );

                                        }

                                    }

                                }

                                $fields_arr = array(
                                    'name'      => $field_val->name,
                                    'type'      => $field_val->type,
                                    'title'     => $field_val->title,
                                    'required'  => ( (int)$field_val->required == 1 ) ? true : false,
                                    'multiple'  => ( (int)$field_val->multiple == 1 ) ? true : false,
                                    'options'   => $options_arr,
                                    'repeater_fields' => $repeater_fields
                                );

                                $sections_arr[ 'fields' ][] = $fields_arr;

                            }

                        }

                        $pages_arr[ 'sections' ][] = $sections_arr;

                    }

                }

                $customizations[ 'pages' ][] = $pages_arr;

            }

        }

        return $customizations;

    }

    /**
     * Retrieves select option format data.
     *
     * @param array|null $options_data An array containing the options data.
     * @return array The formatted select option data.
     */
    public static function get_select_option_format_data( $options_data = null )
    {
        $options_arr = array();

        if ( isset( $options_data ) && !is_null( $options_data ) ) {
            
            $options_arr_data = preg_split( '~\R~', $options_data );

            if ( is_array( $options_arr_data ) && $options_arr_data && count( $options_arr_data ) > 0 ) {

                foreach ( $options_arr_data as $options_pair ) {

                    $options_pair_val = trim( $options_pair );

                    $options_pair_arr = explode( ':', $options_pair_val );

                    $option_key = ( is_array( $options_pair_arr ) && $options_pair_arr && count( $options_pair_arr ) > 0 )
                    ? $options_pair_arr[0]
                    : '';

                    $option_key = trim( $option_key );

                    $option_val = ( is_array( $options_pair_arr ) && $options_pair_arr && count( $options_pair_arr ) > 1 )
                    ? $options_pair_arr[1]
                    : '';

                    $option_val = trim( $option_val );

                    if ( isset( $option_key ) && !empty( $option_key ) && !is_null( $option_key ) && isset( $option_val ) && !empty( $option_val ) && !is_null( $option_val ) ) {

                        $options_arr[ $option_key ] = $option_val;

                    }

                }

            }

        }

        return $options_arr;
    }

    /**
     * Retrieves data for customizations.
     *
     * This function fetches and returns the necessary data for customizations,
     * such as user preferences, settings, or configurations.
     *
     * @return array Customization data.
     */
    public static function get_customizations_data()
    {
        return CustomizationHelpers::get_customization_setting_data();
    }

    /**
     * Retrieve keys related to file fields.
     *
     * @param string|null $repeater     Optional. The name of the repeater.
     * @param string|null $page_slug    Optional. The slug of the page.
     * @param string|null $section_slug Optional. The slug of the section.
     * @return array An array of file field keys.
     */
    public static function get_file_field_keys( $repeater = null, $page_slug = null, $section_slug = null )
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'file', $repeater, $page_slug, $section_slug );
    }

    /**
     * Retrieve the keys of repeater fields for a specific page and section.
     *
     * @param string|null $page_slug    The slug of the page.
     * @param string|null $section_slug The slug of the section.
     *
     * @return array An array containing the keys of repeater fields.
     */
    public static function get_repeater_field_keys( $page_slug = null, $section_slug = null )
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'repeater', null, $page_slug, $section_slug );
    }

    /**
     * Retrieves the keys of repeater fields for a specific page and section.
     *
     * @param string|null $page_slug    Optional. Slug of the page where the repeater fields are located.
     * @param string|null $section_slug Optional. Slug of the section within the page where the repeater fields are located.
     * @return array An array containing the keys of repeater fields.
     */
    public static function get_product_field_keys( $page_slug = null, $section_slug = null )
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'product', null, $page_slug, $section_slug );
    }

    /**
     * Retrieves the field keys associated with a specific category for a given page and section.
     *
     * @param string|null $page_slug     Optional. The slug of the page. Defaults to null.
     * @param string|null $section_slug  Optional. The slug of the section. Defaults to null.
     * @return array An array of field keys associated with the specified category.
     */
    public static function get_category_field_keys( $page_slug = null, $section_slug = null )
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'category', null, $page_slug, $section_slug );
    }

    /**
     * Retrieves the field keys for category products.
     *
     * @param string|null $page_slug    Optional. The slug of the page.
     * @param string|null $section_slug Optional. The slug of the section.
     * @return array An array of field keys.
     */
    public static function get_category_product_field_keys( $page_slug = null, $section_slug = null )
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'category_product', null, $page_slug, $section_slug );
    }

    /**
     * Retrieve an array of blog field keys based on page and section slugs.
     *
     * @param string|null $page_slug     Optional. The slug of the page.
     * @param string|null $section_slug  Optional. The slug of the section.
     * @return array An array of blog field keys.
     */
    public static function get_blog_field_keys( $page_slug = null, $section_slug = null )
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'blog', null, $page_slug, $section_slug );
    }

    /**
     * Retrieve an array of field keys based on the specified criteria.
     *
     * @param string|null $field_type   Optional. The type of field to filter by.
     * @param bool|null   $repeater     Optional. Whether to include repeater fields.
     * @param string|null $page_slug    Optional. The slug of the page to filter by.
     * @param string|null $section_slug Optional. The slug of the section to filter by.
     *
     * @return array An array of field keys matching the specified criteria.
     */
    public static function get_field_keys_arr_by_type( $field_type = null, $repeater = null, $page_slug = null, $section_slug = null )
    {
        $field_keys_arr = [];

        if ( $field_type && isset( $field_type ) && !empty( $field_type ) && !is_null( $field_type ) ) {

            $customizations_data = CustomizationHelpers::get_customizations_data();

            if ( $customizations_data && is_array( $customizations_data ) && count( $customizations_data ) > 0 ) {

                if ( array_key_exists( 'pages', $customizations_data ) && $customizations_data[ 'pages' ] && is_array( $customizations_data[ 'pages' ] ) && count( $customizations_data[ 'pages' ] ) > 0 ) {

                    foreach ( $customizations_data[ 'pages' ] as $key => $customization ) {

                        if ( array_key_exists( 'sections', $customization ) && $customization[ 'sections' ] && is_array( $customization[ 'sections' ] ) && count( $customization[ 'sections' ] ) > 0 ) {

                            foreach ( $customization[ 'sections' ] as $s_key => $section ) {

                                if ( array_key_exists( 'fields', $section ) && $section[ 'fields' ] && is_array( $section[ 'fields' ] ) && count( $section[ 'fields' ] ) > 0 ) {

                                    foreach ( $section[ 'fields' ] as $f_key => $field ) {

                                        if ( $field && is_array( $field ) && array_key_exists( 'type', $field ) && $field[ 'type' ] == $field_type && array_key_exists( 'name', $field ) ) {

                                            if ( $page_slug && isset( $page_slug ) && !empty( $page_slug ) && !is_null( $page_slug ) && $section_slug && isset( $section_slug ) && !empty( $section_slug ) && !is_null( $section_slug ) ) {

                                                $if_cust_setting = CustomizationSettings::where( 'page_slug', $page_slug )
                                                                                        ->where( 'section_slug', $section_slug )
                                                                                        ->where( 'name', $field[ 'name' ] )
                                                                                        ->where( 'type', $field_type )
                                                                                        ->where( 'status', 1 )
                                                                                        ->where( 'parent_id', 0 )
                                                                                        ->whereNull( 'setting_type' )
                                                                                        ->first();

                                                if ( $if_cust_setting ) {

                                                    $field_keys_arr[] = $field[ 'name' ];

                                                }

                                            } else {

                                                $field_keys_arr[] = $field[ 'name' ];

                                            }

                                        }

                                        if ( $repeater == 'repeater' ) {
                                            
                                            if ( array_key_exists( 'repeater_fields', $field ) ) {

                                                $rp_id_flag = false;

                                                $repeater_id = 0;

                                                $repeater_name = array_key_exists( 'name', $field ) ? $field[ 'name' ] : null;

                                                $if_rp_cust_setting_flag = CustomizationSettings::where( 'page_slug', $page_slug )
                                                                                            ->where( 'section_slug', $section_slug )
                                                                                            ->where( 'name', $repeater_name )
                                                                                            ->where( 'type', 'repeater' )
                                                                                            ->where( 'status', 1 )
                                                                                            ->where( 'parent_id', 0 )
                                                                                            ->whereNull( 'setting_type' )
                                                                                            ->first();

                                                if ( $if_rp_cust_setting_flag ) {

                                                    $repeater_id = (int)$if_rp_cust_setting_flag->id;

                                                    $rp_id_flag = true;

                                                }

                                            	foreach ( $field[ 'repeater_fields' ] as $rf_key => $r_field ) {

                                            		if ( $r_field && is_array( $r_field ) && array_key_exists( 'type', $r_field ) && $r_field[ 'type' ] == $field_type && array_key_exists( 'name', $r_field ) ) {

                                                        if ( $page_slug && isset( $page_slug ) && !empty( $page_slug ) && !is_null( $page_slug ) && $section_slug && isset( $section_slug ) && !empty( $section_slug ) && !is_null( $section_slug ) && $rp_id_flag == true ) {

                                                            $if_rp_cust_setting = CustomizationSettings::where( 'page_slug', $page_slug )
                                                                                                    ->where( 'section_slug', $section_slug )
                                                                                                    ->where( 'name', $r_field[ 'name' ] )
                                                                                                    ->where( 'status', 1 )
                                                                                                    ->where( 'parent_id', $repeater_id )
                                                                                                    ->where( 'setting_type', 'repeater' )
                                                                                                    ->first();

                                                            if ( $if_rp_cust_setting ) {

                                                                $field_keys_arr[] = $r_field[ 'name' ];

                                                            }

                                                        } else {

                                                            $field_keys_arr[] = $r_field[ 'name' ];

                                                        }

                                            		}

                                            	}

                                            }

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

        return $field_keys_arr;
    }

    /**
     * Retrieves data based on the provided slug.
     *
     * @param string|null $slug The slug used to retrieve data.
     * @param array $data Additional data (optional).
     * @return mixed The retrieved data.
     */
    public static function get_data_by_slug( $slug = null, $data = [] )
    {
        $search_data = [];

        if ( isset( $slug ) && !empty( $slug ) && !is_null( $slug ) && !empty( $data ) && count( $data ) > 0 ) {

            foreach ($data as $d) {

                if ( array_key_exists( 'slug', $d ) && $d[ 'slug' ] == $slug ) {

                    $search_data = $d;

                }

            }

        }

        return $search_data;
    }

    /**
     * Retrieve customization data based on provided slugs for a page and section.
     *
     * @param string|null $page_slug    Slug of the page to retrieve customization data for.
     * @param string|null $section_slug Slug of the section within the page to retrieve customization data for.
     *
     * @return mixed Customization data associated with the provided slugs.
     */
    public static function get_customization_data_by_slug( $page_slug = null, $section_slug = null )
    {
        $customization_data = $pages = $sections = $fields = $page = $section = [];

        $customizations = CustomizationHelpers::get_customizations_data();

        if ( array_key_exists( 'pages', $customizations ) && !empty( $customizations ) && count( $customizations ) > 0 && isset( $page_slug ) && !empty( $page_slug ) && !is_null( $page_slug ) ) {

            $pages = CustomizationHelpers::get_data_by_slug( $page_slug, $customizations[ 'pages' ] );
        }

        if ( array_key_exists( 'sections', $pages ) && !empty( $pages ) && count( $pages ) > 0 && isset( $section_slug ) && !empty( $section_slug ) && !is_null( $section_slug ) ) {

            $sections = CustomizationHelpers::get_data_by_slug( $section_slug, $pages[ 'sections' ] );
        }

        if ( !empty( $pages ) && count( $pages ) > 0 ) {
            
            $page = $pages;
            
            if ( array_key_exists( 'sections', $page) ) {

                unset( $page[ 'sections' ] );

            }

        }

        if ( !empty( $sections ) && count( $sections ) > 0 ) {

            $section = $sections;

            if ( array_key_exists( 'fields', $section) ) {

                unset( $section[ 'fields' ] );

            }

        } else {

            $sections = ( !empty( $pages ) && count( $pages ) > 0 && array_key_exists( 'sections', $pages ) ) ? $pages[ 'sections' ] : [];

        }

        $fields = ( !empty( $sections ) && count( $sections ) > 0 && array_key_exists( 'fields', $sections ) ) ? $sections[ 'fields' ] : [];

        $customization_data = array(
            'page' => $page,
            'pages' => $pages,
            'section' => $section,
            'sections' => $sections,
            'fields' => $fields,
        );

        return $customization_data;
    }

    /**
     * Retrieves the directory path for customizations.
     *
     * This function returns the directory path where customizations for the application are stored.
     * It provides a standardized way to access this directory, ensuring consistency across the application.
     *
     * @return string The directory path for customizations.
     */
    public static function get_customization_directory(): string
    {
        return 'customizations';
    }

    /**
     * Generates a pre-signed URL for accessing a file.
     * 
     * This function generates a pre-signed URL, which grants temporary access to a specific file.
     * Pre-signed URLs are useful for providing time-limited access to files without requiring authentication.
     * 
     * @return string The pre-signed URL for the file.
     */
    public static function pre_file_url(): string
    {
        $pre_file_url = '';

        if ( CustomizationHelpers::check_customization_s3_enabled() ) {

            $aws_access = CoreConfig::where( 'code', 'customization_aws.customization_s3_bucket.customization_setting.customization_aws_url' )->first();

            $db_aws_access = $aws_access ? $aws_access->value : '';

            $pre_file_url =  $db_aws_access . '/';

        
        } else {
        
            $pre_file_url =  env( 'APP_URL' ) . '/storage/';
        
        }
        
        return $pre_file_url;
    }

    /**
     * Checks if customization for S3 storage is enabled.
     * 
     * This function likely checks some configuration settings or conditions to determine 
     * whether customization for S3 storage is enabled within the application. 
     * Ensure proper documentation and context are provided elsewhere in the codebase 
     * to clarify the purpose and usage of this function.
     * 
     * @return bool Returns true if customization for S3 storage is enabled, otherwise false.
     */
    public static function check_customization_s3_enabled()
    {
        $enabled = false;
        
        $aws_enabled = CoreConfig::where( 'code', 'customization_aws.customization_s3_bucket.customization_setting.customization_s3_enabled  ' )->first();
        
        $db_aws_enabled = $aws_enabled ? (int)$aws_enabled->value : 0;
        
        if ( $db_aws_enabled == 1 ) {
        
            $enabled = true;
        
        }
        
        return $enabled;
    }

    /**
     * Updates AWS details in the environment configuration file for customization.
     * This function is responsible for modifying the environment configuration file to reflect any changes or updates in AWS settings.
     * It ensures that the application's AWS-related configurations remain up-to-date and consistent with the customization requirements.
     */
    public static function update_aws_details_in_env_file_for_customization()
    {
        $aws_access = CoreConfig::where( 'code', 'customization_aws.customization_s3_bucket.customization_setting.customization_access_key' )->first();
        
        $db_aws_access = $aws_access ? $aws_access->value : null;

        $aws_secret = CoreConfig::where( 'code', 'customization_aws.customization_s3_bucket.customization_setting.customization_secret_key' )->first();
        
        $db_aws_secret = $aws_secret ? $aws_secret->value : null;
        
        $aws_default_region = CoreConfig::where( 'code', 'customization_aws.customization_s3_bucket.customization_setting.customization_default_region' )->first();
        
        $db_aws_default_region = $aws_default_region ? $aws_default_region->value : null;
        
        $aws_bucket_name = CoreConfig::where( 'code', 'customization_aws.customization_s3_bucket.customization_setting.customization_bucket_name' )->first();
        
        $db_aws_bucket_name = $aws_bucket_name ? $aws_bucket_name->value : null;
        
        $aws_console_url = CoreConfig::where( 'code', 'customization_aws.customization_s3_bucket.customization_setting.customization_console_url' )->first();
        
        $db_aws_console_url = $aws_console_url ? $aws_console_url->value : null;
        
        $aws_file_url = CoreConfig::where( 'code', 'customization_aws.customization_s3_bucket.customization_setting.customization_aws_url' )->first();
        
        $db_aws_file_url = $aws_file_url ? $aws_file_url->value : null;

        $env_values = [
            'AWS_ACCESS_KEY_ID' => $db_aws_access,
            'AWS_SECRET_ACCESS_KEY' => $db_aws_secret,
            'AWS_DEFAULT_REGION' => $db_aws_default_region,
            'AWS_BUCKET' => $db_aws_bucket_name,
            'AWS_URL' => $db_aws_console_url,
            'AWS_USE_PATH_STYLE_ENDPOINT' => 'false',
            'AWS_FILE_PATH' => $db_aws_file_url,
        ];

        $env_file_path = base_path( '.env' );
        
        $current_env = file_get_contents( $env_file_path );

        foreach ( $env_values as $key => $value ) {

            $env_variable = strtoupper( $key ) . '=' . $value;
            
            if ( strpos( $current_env, $key ) !== false ) {
        
                $current_env = preg_replace( "/$key=.*/", $env_variable, $current_env );
        
            } else {
        
                $current_env .= "\n$env_variable";
        
            }
        
        }

        File::put( $env_file_path, $current_env );

        Artisan::call( 'optimize:clear' );
    }

    /**
     * Handles the operation for uploading media in customization store.
     *
     * @param string $file_key The key of the file to upload.
     * @param array $files An array containing the uploaded file data.
     * @param array $field_details Details of the field.
     * @param string $page_slug The slug of the page.
     * @param string $section_slug The slug of the section.
     * @param string|null $type Optional. The type of media being uploaded.
     * @param string|null $repeater_field_name Optional. The name of the repeater field, if applicable.
     * @param int|null $repeater_field_index Optional. The index of the repeater field, if applicable.
     */
    public static function operation_for_upload_media_in_customization_store( $file_key, $files, $field_details, $page_slug, $section_slug, $type = null, $repeater_field_name = null, $repeater_field_index = null )
    {
    	$field_val = $old_files = null;

    	$file_empty_flag = true;

    	if ( $type == 'repeater' ) {

			$old_repeater_data = CustomizationHelpers::get_customization_specific_field_data( $page_slug, $section_slug, $repeater_field_name );

			if (

				$old_repeater_data && 

				isset( $old_repeater_data ) && 

				!empty( $old_repeater_data ) && 

				!is_null( $old_repeater_data ) && 

				is_array( $old_repeater_data ) && 

				count( $old_repeater_data ) > 0 && 

				array_key_exists( $repeater_field_index, $old_repeater_data ) && 
				
                is_array( $old_repeater_data[ $repeater_field_index ] ) && 

				count( $old_repeater_data[ $repeater_field_index ] ) > 0 && 

				array_key_exists( $repeater_field_name, $old_repeater_data[ $repeater_field_index ] ) 

			) {

				$old_files = $old_repeater_data[ $repeater_field_index ][ $repeater_field_name ];

			}

    	} else {

			$old_files = CustomizationHelpers::get_customization_specific_field_data( $page_slug, $section_slug, $file_key );

    	}

    	if ( $files ) {

    		if ( $old_files && isset( $old_files ) && !empty( $old_files ) && !is_null( $old_files ) ) {

    			if ( is_array( $old_files ) && count( $old_files ) > 0 ) {

    				foreach ( $old_files as $old_file ) {

    					Storage::delete( $old_file );

                        if ( CustomizationHelpers::check_customization_s3_enabled() ) {

                            Storage::disk( 's3' )->delete( $old_file );

                        }

    				}

    			} else {

    				Storage::delete( $old_files );

                    if ( CustomizationHelpers::check_customization_s3_enabled() ) {

                        Storage::disk( 's3' )->delete( $old_files );

                    }

    			}

    		}

    		if ( is_array( $files ) && count( $files ) > 0 ) {

    			foreach ( $files as $file ) {

    				$path = CustomizationHelpers::upload_media( $file );

    				if ( $path && isset( $path ) && !empty( $path ) && !is_null( $path ) ) {

    					$field_val[] = $path;

    					$file_empty_flag = false;

    				}

    			}

    		} else {

    			$path = CustomizationHelpers::upload_media( $files );

    			if ( $path && isset( $path ) && !empty( $path ) && !is_null( $path ) ) {

    				$field_val = $path;

    				$file_empty_flag = false;

    			}

    		}
    	}

    	if ( $file_empty_flag == true ) {

    		if ( $old_files && !empty( $old_files ) && !is_null( $old_files ) ) {

    			$field_val = $old_files;

    		}

    	}

    	if ( $type == 'repeater' ) {

    		$field_details[ $repeater_field_name ][ $repeater_field_index ][ $file_key ] = $field_val;

    	} else {

    		$field_details[ $file_key ] = $field_val;

    	}

    	return $field_details;
    }

    /**
     * Uploads a media file.
     *
     * @param string $file The file to upload.
     * @return bool True if the upload was successful, false otherwise.
     */
    public static function upload_media( $file )
    {
        $path = '';

        if ( $file instanceof UploadedFile ) {

            if ( Str::contains( $file->getMimeType(), 'image' ) ) {

                $manager = new ImageManager();

                $image = $manager->make( $file )->encode( 'webp' );

                $path = CustomizationHelpers::get_customization_directory() . '/' . Str::random( 40 ) . '.webp';

                Storage::put( $path, $image );

            } else {

                $path = $file->store( CustomizationHelpers::get_customization_directory() );

            }

        }

        if ( CustomizationHelpers::check_customization_s3_enabled() ) {

            stream_context_set_default(array(
                'ssl'                => array(
                    'peer_name'          => 'generic-server',
                    'verify_peer'        => FALSE,
                    'verify_peer_name'   => FALSE,
                    'allow_self_signed'  => TRUE
                )));

            $url = URL::to('/').'/storage/'. $path;

            $local_file = file_get_contents( $url, FALSE );

            Storage::disk( 's3' )->put( '/' . $path, $local_file );

            Storage::delete( $path );

        }

        return $path;

    }

    /**
     * Retrieve customization-specific field data based on page, section, and field key.
     *
     * @param string $page_slug     The slug of the page.
     * @param string $section_slug  The slug of the section.
     * @param string $field_key     The key of the field.
     * @return mixed|null           The value of the field, or null if not found.
     */
    public static function get_customization_specific_field_data( $page_slug, $section_slug, $field_key )
    {
        $field_val = '';

        $cust_detail = CustomizationDetails::where( 'page_slug', $page_slug )->where( 'section_slug', $section_slug )->first();

        if ( $cust_detail ) {

            $field_details = (array)json_decode( $cust_detail->field_details );

            if ( !is_null( $field_key ) ) {

                if ( $field_details && is_array( $field_details ) && !empty( $field_details ) && count( $field_details ) > 0 && array_key_exists( $field_key, $field_details ) ) {

                    $field_val = $field_details[ $field_key ];

                }

            }

        }

        return $field_val;
    }

    /**
     * Retrieves the table name associated with a given model.
     *
     * @param string|null $model_name The name of the model (optional). If not provided, the function will attempt to infer the model name.
     * @return string|null The table name, or null if not found.
     */
    public static function get_table_name_by_model( $model_name = null )
    {
        $table_name = null;

        if ( isset( $model_name ) && !empty( $model_name ) && !is_null( $model_name ) ) {

            if ( class_exists( $model_name ) ) {

                $table_name =  (new $model_name)->getTable();

            }

        }

        return $table_name;
    }

    /**
     * Check if a table exists in the database.
     *
     * @param string|null $model_name The name of the table/model to check. If null, the default model name is used.
     * @return bool True if the table exists, false otherwise.
     */
    public static function check_table_exist_in_database( $model_name = null )
    {
        $table_flag = false;

        if ( isset( $model_name ) && !empty( $model_name ) && !is_null( $model_name ) ) {

            $class_flag = CustomizationHelpers::check_class_exists_in_project( $model_name );

            if ( $class_flag ) {

                $table_name = CustomizationHelpers::get_table_name_by_model( $model_name );

                if ( $table_name && Schema::hasTable( $table_name ) ){

                    $table_flag = true;

                }

            }

        }

        return $table_flag;
    }

    /**
     * Check if a class exists within the project.
     *
     * @param string|null $model_name The name of the class to check.
     * @return bool True if the class exists, false otherwise.
     */
    public static function check_class_exists_in_project( $model_name = null )
    {
        $class_flag = false;

        if ( isset( $model_name ) && !empty( $model_name ) && !is_null( $model_name ) ) {

            if ( class_exists( $model_name ) ) {

                $class_flag = true;

            }

        }
        
        return $class_flag;
    }

    /**
     * Retrieve AWS URL by key from the database.
     *
     * @param string $db_field_key The key used for database lookup.
     * @param string $db_field_val The value corresponding to the key.
     * @return string|null The AWS URL if found, null otherwise.
     */
    public static function get_aws_url_by_key( $db_field_key, $db_field_val )
    {
        $file_field_keys = CustomizationHelpers::get_file_field_keys( 'repeater' );

        if ( is_array( $file_field_keys ) && count( $file_field_keys ) > 0 && in_array( $db_field_key, $file_field_keys ) ) {

            if ( is_array( $db_field_val ) ) {

                $multiple_aws_urls = [];

                $multiple_files = $db_field_val ; 

                if ( $multiple_files && count( $multiple_files ) > 0 ) {

                    foreach ( $multiple_files as $multiple_file ) {

                        $multiple_aws_urls[] = CustomizationHelpers::pre_file_url() . $multiple_file;

                    }

                }

                $db_field_val = $multiple_aws_urls;

            } else {

                $path = $db_field_val;

                $db_field_val = CustomizationHelpers::pre_file_url() . $path;

            }

        }

        return $db_field_val;

    }

    /**
     * Retrieve products from the database based on a specific field and its value.
     *
     * @param string $db_field_key The key of the field to search for.
     * @param mixed $db_field_val The value to match for the given field.
     * @return array|null Array of products matching the criteria, or null if no products found.
     */
    public static function get_products_by_key( $db_field_key, $db_field_val )
    {
        $product_data = '';

        $product_field_keys = CustomizationHelpers::get_product_field_keys();

        if ( is_array( $product_field_keys ) && count( $product_field_keys ) > 0 && in_array( $db_field_key, $product_field_keys ) && is_array( $db_field_val ) ) {

            $product_ids = array();

            $product_flats = DB::table( 'product_flat' )->whereIn( 'id', $db_field_val )->select( 'product_id' )->get();

            if ( $product_flats && count( $product_flats ) > 0 ) {

                $product_ids = $product_flats->pluck( 'product_id' )->unique()->toarray();

            }
            
            $products = Product::with( [ 'images', 'videos', 'inventories' ] )->whereIn( 'id', $product_ids )->get();

            if ( $products && count( $products ) > 0 ) {
                
                $product_data = $products;

            }

        }

        return $product_data;

    }

    /**
     * Retrieve categories from the database based on a given key-value pair.
     *
     * @param string $db_field_key The key to search for in the database.
     * @param mixed $db_field_val The value corresponding to the key.
     * @return array|null An array of categories matching the key-value pair, or null if none found.
     */
    public static function get_categorys_by_key( $db_field_key, $db_field_val )
    {
        $category_data = '';

        $category_field_keys = CustomizationHelpers::get_category_field_keys();

        if ( is_array( $category_field_keys ) && count( $category_field_keys ) > 0 && in_array( $db_field_key, $category_field_keys ) && is_array( $db_field_val ) ) {

            $categorys = Category::leftjoin('category_translations as cat_trl', 'cat_trl.category_id', '=', 'categories.id')
                        ->select( 'categories.*', 'cat_trl.category_id', 'cat_trl.name as cat_name')
                        ->whereIn( 'categories.id', $db_field_val )
                        ->where( 'categories.status', 1 )
                        ->where( 'cat_trl.locale', 'en' )
                        ->get();

            if ( $categorys && count( $categorys ) > 0 ) {
                
                $category_data = $categorys;

            }

        }

        return $category_data;

    }

    /**
     * Retrieves a category of products from the database based on a specific field and its value.
     *
     * @param string $db_field_key The database field key to search for.
     * @param mixed $db_field_val The value to match against the given field key.
     * @return array|null An array of products in the category, or null if no matching category is found.
     */
    public static function get_category_product_by_key( $db_field_key, $db_field_val )
    {
        $category_product_data = '';

        $category_product_field_keys = CustomizationHelpers::get_category_product_field_keys();

        if ( is_array( $category_product_field_keys ) && count( $category_product_field_keys ) > 0 && in_array( $db_field_key, $category_product_field_keys ) && is_array( $db_field_val ) ) {

            $product_ids = [];

            $product_categories = DB::table( 'product_categories as pc' )
            ->leftjoin('categories', 'categories.id', '=', 'pc.category_id')
            ->whereIn( 'pc.category_id', $db_field_val )
            ->where( 'categories.status', 1 )
            ->select( 'pc.product_id' )
            ->get();

            if ( $product_categories && count( $product_categories ) > 0 ) {
                
                $product_ids = $product_categories->pluck( 'product_id' )->unique()->toarray();

            }

            $category_products = Product::with( [ 'images', 'videos', 'inventories' ] )->whereIn( 'id', $product_ids )->get();

            if ( $category_products && count( $category_products ) > 0 ) {
                
                $category_product_data = $category_products;

            }

        }

        return $category_product_data;

    }

    /**
     * Retrieves a blog entry from the database based on the provided field key and value.
     *
     * @param string $db_field_key The field key to search for.
     * @param mixed $db_field_val The value associated with the field key.
     * @return array|false Returns an array containing the blog entry if found, or false if not found.
     */
    public static function get_blog_by_key( $db_field_key, $db_field_val )
    {
        $blog_data = '';

        $blog_field_keys = CustomizationHelpers::get_blog_field_keys();

        if ( is_array( $blog_field_keys ) && count( $blog_field_keys ) > 0 && in_array( $db_field_key, $blog_field_keys ) && is_array( $db_field_val ) ) {

            $blogs = array();

            $if_blog_table = CustomizationHelpers::check_table_exist_in_database( 'Webbycrown\BlogBagisto\Models\Blog' );

            if ( $if_blog_table ) {

                $blogs = Blog::where('published_at', '<=', Carbon::now()->format('Y-m-d'))
                ->whereIn( 'id', $db_field_val )
                ->where( 'status', 1 )
                ->where( 'locale', 'en' )
                ->get();

            }

            if ( $blogs && count( $blogs ) > 0 ) {
                
                $blog_data = $blogs;

            }

        }

        return $blog_data;

    }

    /**
     * Retrieves the slugs of sections belonging to a specific page.
     *
     * @param string $page_slug The slug of the page.
     * @return array An array containing the slugs of sections.
     */
    public static function get_section_slugs( $page_slug )
    {
        $section_slug_arr = array();

        if ( isset( $page_slug ) && !is_null( $page_slug ) ) {

            $customizations = CustomizationHelpers::get_customization_data_by_slug( $page_slug );

            $sections = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'sections', $customizations) ) ? $customizations[ 'sections' ] : [];

            if ( $sections && count( $sections ) > 0 ) {

                foreach ( $sections as $section ) {

                    if ( is_array( $section ) && array_key_exists( 'slug', $section ) ) {

                        $section_slug_arr[] = $section[ 'slug' ];

                    }

                }

            }

        }

        return $section_slug_arr;
    }

    /**
     * Get string with breaks and spaces.
     *
     * Breaks the given string into chunks with spaces.
     *
     * @param string $str_with_space The string to process.
     * @param int $break_length The length at which to break the string.
     * @return string The processed string.
     */
    public static function get_string_with_breack_with_space( $str_with_space = '', $break_length = 0 )
    {
        $str_with_space_arr = explode( ' ', $str_with_space );

        
        if ( is_array( $str_with_space_arr ) && count( $str_with_space_arr ) > 0 ) {
        
            foreach ( $str_with_space_arr as $index => $single_word ) {
        
                $break_strings = $single_word;
        
                if ( (int)$break_length > 0 && strlen( $break_strings ) > (int)$break_length ) {
        
                    $break_strings = CustomizationHelpers::get_string_with_breack_without_space( $break_strings, (int)$break_length );
        
                }
        
                $str_with_space_arr[ $index ] = $break_strings;
        
            }
        
        }

        return htmlspecialchars_decode( implode( ' ', $str_with_space_arr ) );
    }

    /**
     * Formats a string by inserting line breaks without breaking words.
     *
     * @param string $str_without_space The input string without spaces.
     * @param int $break_length The maximum length before inserting a line break.
     * @return string The formatted string with line breaks.
     */
    public static function get_string_with_breack_without_space( $str_without_space = '', $break_length = 0 )
    {
        if ( (int)$break_length > 0 ) {

            $segments = str_split( $str_without_space, $break_length );

            $str_without_space = implode( "\n", $segments );

        } 

        return htmlspecialchars_decode( $str_without_space );
    }

    /**
     * Deletes a specific field value from the customization details.
     *
     * @param string $page_slug         The slug of the page where the field is located.
     * @param string $section_slug      The slug of the section where the field is located.
     * @param string $field_key         The key identifying the field.
     * @param string $field_type        The type of the field (e.g., text, textarea, checkbox).
     * @param string $field_setting_type The setting type of the field (e.g., theme_mod, option).
     * @param int    $setting_id        Optional. The ID of the setting (if applicable).
     */
    public static function delete_field_value_in_customization_detail( $page_slug, $section_slug, $field_key, $field_type, $field_setting_type, $setting_id = 0 )
    {
        $customization_detail = CustomizationDetails::where( 'page_slug', $page_slug )->where( 'section_slug', $section_slug )->first();
        
        if ( $customization_detail ) {
        
            $field_details = $customization_detail->field_details;
        
            if ( isset( $field_details ) && !is_null( $field_details ) ) {
        
                $field_details = (array)json_decode( $field_details );
        
                if ( $field_details && is_array( $field_details ) && count( $field_details ) > 0 ) {
        
                    $new_field_details = array();
        
                    foreach ( $field_details as $field_detail_key => $field_detail_value ) {
        
                        if ( trim( $field_detail_key ) == trim( $field_key ) ) {
        
                            if ( $field_type == 'repeater' ) {
        
                                if ( (int)$setting_id > 0 ) {
        
                                    $new_field_details = CustomizationHelpers::operation_for_repeater_field_for_delete_field( $new_field_details, $page_slug, $section_slug, $setting_id, $field_detail_key, $field_detail_value, $field_key, $field_type );
        
                                }
        
                            } elseif ( $field_type == 'file' ) {
        
                                CustomizationHelpers::delete_file_field_value( $field_detail_value );
        
                            }
        
                        } elseif ( $field_setting_type == 'repeater' && (int)$setting_id > 0 ) {
        
                            $parent_setting = CustomizationSettings::where( 'id', $setting_id )->first();
        
                            if ( $parent_setting && $parent_setting->name == trim( $field_detail_key ) ) {
        
                                $repeater_field_detail_value = $field_detail_value;
        
                                $new_field_details = CustomizationHelpers::operation_for_repeater_field_for_delete_field( $new_field_details, $page_slug, $section_slug, $setting_id, $field_detail_key, $field_detail_value, $field_key, $field_type );
        
                            }
        
                        } else {
        
                            $new_field_details[ $field_detail_key ] = $field_detail_value;
        
                        }
        
                    }
        
                    CustomizationDetails::where( 'page_slug', $page_slug )
                                        ->where( 'section_slug', $section_slug )
                                        ->update( [ 'field_details' => json_encode( $new_field_details ) ] );
        
                }
        
            }
        
        }

    }

    /**
     * Handles the operation for deleting a field in a repeater field.
     *
     * @param mixed  $new_field_details        Details of the new field.
     * @param string $page_slug                The slug of the page.
     * @param string $section_slug             The slug of the section.
     * @param string $setting_id               The ID of the setting.
     * @param string $field_detail_key         The key of the field detail.
     * @param mixed  $repeater_field_detail_value The value of the repeater field detail.
     * @param string $field_key                The key of the field.
     * @param string $field_type               The type of the field.
     * @return void
     */
    public static function operation_for_repeater_field_for_delete_field( $new_field_details, $page_slug, $section_slug, $setting_id, $field_detail_key, $repeater_field_detail_value, $field_key, $field_type )
    {
        $repeater_fields = array();
        
        if ( $field_type == 'repeater' ) {
        
            $repeater_fields = CustomizationSettings::where( 'page_slug', $page_slug )
                                                    ->where( 'section_slug', $section_slug )
                                                    ->where( 'parent_id', $setting_id )
                                                    ->where( 'setting_type', 'repeater' )
                                                    ->get();
        
        }
        
        if ( $repeater_field_detail_value && is_array( $repeater_field_detail_value ) && count( $repeater_field_detail_value ) > 0 ) {
        
            foreach ( $repeater_field_detail_value as $repeater_field_index => $repeater_field_detail ) {
        
                $repeater_field_detail_arr = (array)$repeater_field_detail;
        
                if ( $repeater_field_detail_arr && is_array( $repeater_field_detail_arr ) && count( $repeater_field_detail_arr ) > 0 ) {
        
                    foreach ( $repeater_field_detail_arr as $repeater_field_key => $repeater_field_value ) {
        
                        if ( $field_detail_key == $field_key  && $field_type == 'repeater' ) {
        
                            if ( $repeater_fields && count( $repeater_fields ) > 0 ) {
        
                                foreach ( $repeater_fields as $repeater_field ) {
        
                                    if ( $repeater_field->type == 'file' && $repeater_field->name == $repeater_field_key ) {
        
                                        CustomizationHelpers::delete_file_field_value( $repeater_field_value );
        
                                    }
        
                                }
        
                            }
        
                        } else {
        
                            if ( $repeater_field_key == $field_key ) {
        
                                if ( $field_type  == 'file' ) {
        
                                    CustomizationHelpers::delete_file_field_value( $repeater_field_value );
        
                                }
        
                            } else {
        
                                $new_field_details[ $field_detail_key ][ $repeater_field_index ][ $repeater_field_key ] = $repeater_field_value;
        
                            }
        
                        }
        
                    }
        
                }
        
            }
        
        }
        
        return $new_field_details;
    }

    /**
     * Deletes the file field value.
     *
     * @param string $file_field_value The value of the file field to be deleted.
     * @return bool True if deletion was successful, false otherwise.
     */
    public static function delete_file_field_value( $file_field_value )
    {
        $file_field_val = ( is_array( $file_field_value ) ) ? $file_field_value : ( ( isset( $file_field_value ) && !is_null( $file_field_value ) ) ? array( $file_field_value ) : array() );
        
        if ( $file_field_val && is_array( $file_field_val ) && count( $file_field_val ) > 0 ) {
        
            foreach ( $file_field_val as $file_path ) {
        
                Storage::delete( $file_path );
        
                if ( CustomizationHelpers::check_customization_s3_enabled() ) {
        
                    Storage::disk( 's3' )->delete( $file_path );
        
                }
        
            }
        
        }

    }

}
