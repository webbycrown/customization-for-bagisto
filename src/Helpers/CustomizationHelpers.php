<?php

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

class CustomizationHelpers
{

    public function __construct() {

    }

    public static function getTesting()
    {
        return 'getTesting';
    }

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

    public static function get_customizations_data()
    {
        return CustomizationHelpers::get_customization_setting_data();
    }

    public static function get_file_field_keys( $repeater = null )
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'file', $repeater );
    }

    public static function get_repeater_field_keys()
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'repeater' );
    }

    public static function get_product_field_keys()
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'product' );
    }

    public static function get_category_field_keys()
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'category' );
    }

    public static function get_category_product_field_keys()
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'category_product' );
    }

    public static function get_blog_field_keys()
    {
        return CustomizationHelpers::get_field_keys_arr_by_type( 'blog' );
    }

    public static function get_field_keys_arr_by_type( $field_type = null, $repeater = null )
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

                                            $field_keys_arr[] = $field[ 'name' ];

                                        }

                                        if ( $repeater == 'repeater' ) {
                                            
                                            if ( array_key_exists( 'repeater_fields', $field ) ) {

                                            	foreach ( $field[ 'repeater_fields' ] as $rf_key => $r_field ) {

                                            		if ( $r_field && is_array( $r_field ) && array_key_exists( 'type', $r_field ) && $r_field[ 'type' ] == $field_type && array_key_exists( 'name', $r_field ) ) {

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

        return $field_keys_arr;
    }

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

    public static function get_customization_directory(): string
    {
        return 'customizations';
    }

    public static function pre_file_url(): string
    {
        if ( class_exists( 'Webbycrown\S3Extension\S3Extension' )) {

            return env( 'AWS_FILE_PATH' ) . '/';

        } else {

            return env( 'APP_URL' ) . '/storage/';

        }
    }

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

                        if ( class_exists( 'Webbycrown\S3Extension\S3Extension' )) {

                            $s3Data = array( 
                                'action' => 'delete',
                                'imageUrl' => URL::to('/').'/storage/'. $old_file,
                                'location' => CustomizationHelpers::get_customization_directory(),
                            );
                            app('Webbycrown\S3Extension\S3Extension')->awsS3Operation( $s3Data );

                        }

    				}

    			} else {

    				Storage::delete( $old_files );

                    if ( class_exists( 'Webbycrown\S3Extension\S3Extension' )) {

                        $s3Data = array( 
                            'action' => 'delete',
                            'imageUrl' => URL::to('/').'/storage/'. $old_file,
                            'location' => CustomizationHelpers::get_customization_directory(),
                        );
                        app('Webbycrown\S3Extension\S3Extension')->awsS3Operation( $s3Data );

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

        if ( class_exists( 'Webbycrown\S3Extension\S3Extension' )) {

            $s3Data = array( 
                'action' => 'upload',
                'imageUrl' => URL::to('/').'/storage/'. $path,
                'location' => CustomizationHelpers::get_customization_directory(),
            );
            app('Webbycrown\S3Extension\S3Extension')->awsS3Operation( $s3Data );

            Storage::delete( $path );
        }

        return $path;

    }

    public static function get_customization_specific_field_data( $page_slug, $section_slug, $field_key )
    {
        $field_val = '';

        $req_data = new Request();

        $req_data->merge([
            'page_slug' => $page_slug,
            'section_slug' => $section_slug,
            'field_key' => $field_key
        ]);

        $controller = new ShopCustomizationController();

        $res_data = $controller->get_customization_details($req_data);

        $res_data = $res_data && $res_data->original ? $res_data->original : [];

        if ( array_key_exists( 'status_code', $res_data ) && $res_data[ 'status_code' ] == 200 && array_key_exists( 'status', $res_data ) && $res_data[ 'status' ] == 'success' ) {
            
            $field_val = ( array_key_exists( 'data', $res_data ) ) ? $res_data[ 'data' ] : '';

            if ( isset( $field_val ) && !empty( $field_val ) && !is_null( $field_val ) ) {

                $file_field_keys = CustomizationHelpers::get_file_field_keys();

                $pre_file_url = CustomizationHelpers::pre_file_url();

                if ( $file_field_keys && is_array( $file_field_keys ) && in_array( $field_key, $file_field_keys ) ) {

                    $field_val = str_replace( $pre_file_url, '', $field_val );

                }
                
            }

        }

        return $field_val;
    }

    public static function get_table_name_by_model( $model_name = null ){

        $table_name = null;

        if ( isset( $model_name ) && !empty( $model_name ) && !is_null( $model_name ) ) {

            if ( class_exists( $model_name ) ) {

                $table_name =  (new $model_name)->getTable();

            }

        }

        return $table_name;
    }

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

    public static function get_aws_url_by_file_field_key( $field_key, $field_details )
    {
        $file_field_keys = CustomizationHelpers::get_file_field_keys();

        if ( is_array( $file_field_keys ) && count( $file_field_keys ) > 0 ) {

            if ( isset( $field_key ) && !empty( $field_key ) && !is_null( $field_key ) ) {

                if ( in_array( $field_key, $file_field_keys ) && $field_details && is_array( $field_details ) && count( $field_details ) > 0 && array_key_exists( $field_key, $field_details ) ) {

                    if ( is_array( $field_details[ $field_key ] ) ) {

                        $multiple_aws_urls = [];

                        $multiple_files = $field_details[ $field_key ] ; 

                        if ( $multiple_files && count( $multiple_files ) > 0 ) {

                            foreach ( $multiple_files as $multiple_file ) {

                                $multiple_aws_urls[] = CustomizationHelpers::pre_file_url() . $multiple_file;

                            }

                        }

                        $field_details[ $field_key ] = $multiple_aws_urls;

                    } else {

                        $path = $field_details[ $field_key ];

                        $field_details[ $field_key ] = CustomizationHelpers::pre_file_url() . $path;

                    }

                }

            } else {

                if ( $field_details && is_array( $field_details ) && count( $field_details ) > 0 ) {

                    foreach ( $field_details as $db_field_key => $field_detail ) {

                        if ( in_array( $db_field_key, $file_field_keys ) ) {

                            if ( is_array( $field_details[ $db_field_key ] ) ) {

                                $multiple_aws_urls = [];

                                $multiple_files = $field_details[ $db_field_key ] ; 

                                if ( $multiple_files && count( $multiple_files ) > 0 ) {

                                    foreach ( $multiple_files as $multiple_file ) {

                                        $multiple_aws_urls[] = CustomizationHelpers::pre_file_url() . $multiple_file;

                                    }

                                }

                                $field_details[ $db_field_key ] = $multiple_aws_urls;

                            } else {

                                $field_details[ $db_field_key ] = CustomizationHelpers::pre_file_url() . $field_detail;

                            }

                        }

                    }

                }

            }

        }
        
        return $field_details;
    }

}
