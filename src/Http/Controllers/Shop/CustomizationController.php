<?php

namespace Webbycrown\Customization\Http\Controllers\Shop;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Webbycrown\Customization\Models\CustomizationPages;
use Webbycrown\Customization\Models\CustomizationDetails;
use Webbycrown\Customization\Models\CustomizationSections;
use Webkul\Product\Models\ProductFlat;
use Webkul\Product\Models\Product;
use Webkul\Category\Models\Category;
use Webkul\Product\Repositories\ProductRepository;
use Webbycrown\BlogBagisto\Models\Blog;
use Carbon\Carbon;
use Webbycrown\Customization\Helpers\CustomizationHelpers;
use Webkul\CMS\Models\Page;

class CustomizationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    public function get_customization_details(Request $request)
    {
        $data = $rules = $massage = [];

        $req_data = $request->all();

        $page_slug = ( array_key_exists( 'page_slug', $req_data ) && isset( $req_data[ 'page_slug' ] ) && !empty( $req_data[ 'page_slug' ] ) && !is_null( $req_data[ 'page_slug' ] ) ) ? $req_data[ 'page_slug' ] : null;

        $section_slug = ( array_key_exists( 'section_slug', $req_data ) && isset( $req_data[ 'section_slug' ] ) && !empty( $req_data[ 'section_slug' ] ) && !is_null( $req_data[ 'section_slug' ] ) ) ? $req_data[ 'section_slug' ] : null;
        
        $field_key = ( array_key_exists( 'field_key', $req_data ) && isset( $req_data[ 'field_key' ] ) && !empty( $req_data[ 'field_key' ] ) && !is_null( $req_data[ 'field_key' ] ) ) ? $req_data[ 'field_key' ] : null;

        $rules[ 'page_slug' ] = 'required';
        
        if ( !is_null( $field_key ) ) {

            $rules[ 'page_slug' ] = 'required';

            $rules[ 'section_slug' ] = 'required';

        }

        if ( !is_null( $section_slug ) ) {

            $rules[ 'page_slug' ] = 'required';

        }

        $massage = array(
            "page_slug.required" => 'Page slug is required.',
            "section_slug.required" => 'Section slug is required.',
            "field_key.required" => 'Field key is required.'
        );

        $validator = Validator::make( $req_data, $rules, $massage );

        if ( $validator->fails() ) {

            return response()->json([
                'status_code' => 500,
                'status' => 'error',
                'error_messages' => $validator->errors()
            ], 200);

        }

        $cust_details = CustomizationDetails::where( 'page_slug', $page_slug );

        $section_slug_arr = CustomizationHelpers::get_section_slugs( $page_slug );

        if ( !is_null( $section_slug ) ) {

            if ( !in_array( $section_slug, $section_slug_arr ) ) {

                $section_slug = null;

            }

            $cust_details = $cust_details->where( 'section_slug', $section_slug );

        } else {

            $cust_details = $cust_details->whereIn( 'section_slug', $section_slug_arr );
            
        }

        $cust_details = $cust_details->get();

        if ( $cust_details && count( $cust_details ) > 0 ) {

            foreach ( $cust_details as $key => $cust_detail ) {

                $field_details = (array)json_decode( $cust_detail->field_details );

                $field_details = $this->operation_for_field_data( $field_details );

                if ( !is_null( $field_key ) ) {

                    if ( $field_details && is_array( $field_details ) && !empty( $field_details ) && count( $field_details ) > 0 && array_key_exists( $field_key, $field_details ) ) {
                        
                        $data = $field_details[ $field_key ];

                    } else {

                        return response()->json([
                            'status_code' => 404,
                            'status' => 'error',
                            'data' => 'Data not found'
                        ], 200);

                    }

                } else {

                    $data[ $cust_detail->section_slug ][ 'section_slug' ] = $cust_detail->section_slug;

                    $data[ $cust_detail->section_slug ][ 'field_details' ] = $field_details;
                
                }

            }

        } else {

            return response()->json([
                'status_code' => 404,
                'status' => 'error',
                'data' => 'Data not found'
            ], 200);

        }

        return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'data' => $data
        ], 200);
    }

    public function operation_for_field_data( $field_details )
    {
        if ( $field_details && is_array( $field_details ) && count( $field_details ) > 0 ) {
            
            foreach ( $field_details as $db_field_key => $db_field_val ) {
                
                $file_field_keys = CustomizationHelpers::get_file_field_keys();

                if ( is_array( $file_field_keys ) && count( $file_field_keys ) > 0 && in_array( $db_field_key, $file_field_keys ) && isset( $db_field_val ) && !is_null( $db_field_val ) ) {

                    $field_details[ $db_field_key ] = CustomizationHelpers::get_aws_url_by_key( $db_field_key, $db_field_val );

                }
                
                $product_field_keys = CustomizationHelpers::get_product_field_keys();

                if ( is_array( $product_field_keys ) && count( $product_field_keys ) > 0 && in_array( $db_field_key, $product_field_keys ) ) {

                    $field_details[ $db_field_key ] = CustomizationHelpers::get_products_by_key( $db_field_key, $db_field_val );
                    
                }
                
                $category_field_keys = CustomizationHelpers::get_category_field_keys();

                if ( is_array( $category_field_keys ) && count( $category_field_keys ) > 0 && in_array( $db_field_key, $category_field_keys ) ) {

                    $field_details[ $db_field_key ] = CustomizationHelpers::get_categorys_by_key( $db_field_key, $db_field_val );
                    
                }
                
                $category_product_field_keys = CustomizationHelpers::get_category_product_field_keys();

                if ( is_array( $category_product_field_keys ) && count( $category_product_field_keys ) > 0 && in_array( $db_field_key, $category_product_field_keys ) ) {

                    $field_details[ $db_field_key ] = CustomizationHelpers::get_category_product_by_key( $db_field_key, $db_field_val );
                    
                }
                
                $blog_field_keys = CustomizationHelpers::get_blog_field_keys();

                if ( is_array( $blog_field_keys ) && count( $blog_field_keys ) > 0 && in_array( $db_field_key, $blog_field_keys ) ) {

                    $field_details[ $db_field_key ] = CustomizationHelpers::get_blog_by_key( $db_field_key, $db_field_val );
                    
                }
                
                $repeater_field_keys = CustomizationHelpers::get_repeater_field_keys();

                if ( is_array( $repeater_field_keys ) && count( $repeater_field_keys ) > 0 && in_array( $db_field_key, $repeater_field_keys ) && is_array( $field_details[ $db_field_key ] ) && count( $field_details[ $db_field_key ] ) > 0 ) {

                    foreach ( $field_details[ $db_field_key ] as $repeater_row_index => $repeater_detail ) {

                        $repeater_data = (array)$repeater_detail;

                        $field_details[ $db_field_key ][ $repeater_row_index ] = $repeater_data;

                        $rp_file_field_keys = CustomizationHelpers::get_file_field_keys( 'repeater' );

                        if ( is_array( $repeater_data ) && count( $repeater_data ) > 0 && is_array( $rp_file_field_keys ) && count( $rp_file_field_keys ) > 0 ) {

                            foreach ( $rp_file_field_keys as $rp_file_field_key ) {

                                if ( array_key_exists( $rp_file_field_key, $repeater_data ) ) {

                                    $field_details[ $db_field_key ][ $repeater_row_index ][ $rp_file_field_key ] = CustomizationHelpers::get_aws_url_by_key( $rp_file_field_key, $repeater_data[ $rp_file_field_key ] );

                                }

                            }

                        }
                        
                    }
                    
                }

            }

        }

        return $field_details;

    }

    public function get_cms_data(Request $request)
    {
        try {

            $req_data = $request->all();

            $url_key = ( array_key_exists( 'slug', $req_data ) ) ? $req_data[ 'slug' ] : null;

            if ( class_exists( 'Webkul\CMS\Models\Page' ) ) {
                
                if ( isset( $url_key ) && !empty( $url_key ) && !is_null( $url_key ) ) {

                    $data = Page::whereHas(
                        'translations',
                        function ($q1) use ( $url_key ) {
                            $q1->where('url_key', $url_key);
                        }
                    )->first();

                } else {

                    $data = Page::all();

                }

                return response()->json([
                    'status_code' => 200,
                    'status' => 'success',
                    'message' => 'get cms pages data successfully',
                    'data' => $data
                ], 200);

            } else {

                return response()->json([
                    'status_code' => 404,
                    'status' => 'error',
                    'message' => 'model file not found',
                    'data' => []
                ], 200);

            }
            
        } catch (\Exception $e) {
            
            return response()->json([
                'status_code' => 500,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => []
            ], 200);

        }
    }

}