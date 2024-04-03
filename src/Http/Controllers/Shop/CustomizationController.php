<?php

namespace Webbycrown\Customization\Http\Controllers\Shop;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Webbycrown\Customization\Models\CustomizationPages;
use Webbycrown\Customization\Models\CustomizationDetails;

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

        if ( !is_null( $section_slug ) ) {

            $cust_details = $cust_details->where( 'section_slug', $section_slug );

        }

        $cust_details = $cust_details->get();

        if ( $cust_details && count( $cust_details ) > 0 ) {

            foreach ( $cust_details as $key => $cust_detail ) {

                if ( !is_null( $field_key ) ) {

                    $field_details = (array)json_decode( $cust_detail->field_details );

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

                    $data[ $key ][ 'section_slug' ] = $cust_detail->section_slug;

                    $data[ $key ][ 'field_details' ] = json_decode( $cust_detail->field_details );
                
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

}