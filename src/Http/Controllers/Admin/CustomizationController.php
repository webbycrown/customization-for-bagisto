<?php

/**
 * Namespace containing controller classes responsible for handling admin-related operations.
 */
namespace Webbycrown\Customization\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Webbycrown\Customization\Datagrids\CustomizationDataGrid;
use Webbycrown\Customization\Datagrids\CustomizationPageDataGrid;
use Webbycrown\Customization\Datagrids\CustomizationSectionDataGrid;
use Webbycrown\Customization\Datagrids\CustomizationSettingDataGrid;
use Webbycrown\Customization\Models\CustomizationPages;
use Webbycrown\Customization\Models\CustomizationSections;
use Webbycrown\Customization\Models\CustomizationDetails;
use Webbycrown\Customization\Models\CustomizationSettings;
use Webbycrown\Customization\Http\Controllers\Shop\CustomizationController as ShopCustomizationController;
use Intervention\Image\ImageManager;
use Webkul\Product\Models\ProductFlat;
use Webkul\Category\Models\Category;
use Webbycrown\BlogBagisto\Models\Blog;
use Carbon\Carbon;
use Webbycrown\Customization\Helpers\CustomizationHelpers;

/**
 * Controller class responsible for handling customization logic for admin.
 */
class CustomizationController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected CustomizationHelpers $CustomizationHelpers)
    {
        $this->middleware( 'admin' );

        $this->_config = request( '_config' );
    }

    /**
     * Display the index page.
     */
    public function index()
    {
        if ( request()->ajax() ) {

            return app(CustomizationPageDataGrid::class)->toJson();

        }

        return view( $this->_config[ 'view' ] );
    }

    /**
     * Edit a page by its ID.
     *
     * @param int $id The ID of the page to edit.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the edit operation.
     */
    public function page_edit(int $id): JsonResponse
    {
        $customization_page = CustomizationPages::where( 'id', $id )->firstOrFail();

        return new JsonResponse([
            'data' => $customization_page,
        ]);
    }

    /**
     * Edit a section based on the provided ID.
     *
     * @param int $id The ID of the section to edit.
     * @return JsonResponse The JSON response indicating the result of the edit operation.
     */
    public function section_edit(int $id): JsonResponse
    {
        $customization_section = CustomizationSections::where( 'id', $id )->firstOrFail();

        return new JsonResponse([
            'data' => $customization_section,
        ]);
    }

    /**
     * Retrieves and prepares data for editing a section setting.
     *
     * @param int $id The ID of the section setting to edit.
     * @return JsonResponse The JSON response containing the data for editing the section setting.
     */
    public function section_setting_edit(int $id): JsonResponse
    {
        $customization_setting = CustomizationSettings::where( 'id', $id )->firstOrFail();

        if ( $customization_setting ) {

            $others = $customization_setting->other_settings;

            if ( isset( $others ) && !is_null( $others ) ) {

                $customization_setting->other_settings = json_decode( $others );

            }

        }

        return new JsonResponse([
            'data' => $customization_setting,
        ]);
    }

    /**
     * Retrieves the list of a page based on its slug.
     *
     * @param string $page_slug The slug of the page.
     * @return int|false The list of the page if found, otherwise false.
     */
    public function pages_index( $page_slug )
    {
        if ( request()->ajax() ) {

            return app(CustomizationSectionDataGrid::class)->toJson();

        }

        $customization_page = CustomizationPages::where( 'slug', $page_slug )->firstOrFail();

        if ( CustomizationHelpers::check_customization_s3_enabled() ) {

            CustomizationHelpers::update_aws_details_in_env_file_for_customization();
            
        }

        return view( $this->_config[ 'view' ], compact( 'page_slug', 'customization_page' ) );
    }

    /**
     * Retrieve the list of a section setting.
     *
     * @param string $page_slug    The slug of the page.
     * @param string $section_slug The slug of the section.
     * @return int|null            The list of the section setting, or null if not found.
     */
    public function sections_setting_index( $page_slug, $section_slug )
    {
        if ( request()->ajax() ) {

            return app(CustomizationSettingDataGrid::class)->toJson();

        }

        $customization_page = CustomizationPages::where( 'slug', $page_slug )->firstOrFail();

        $customization_section = CustomizationSections::where( 'page_slug', $page_slug )->where( 'slug', $section_slug )->firstOrFail();

        $types = array(
            array( 'option_key' => 'text',              'option_val' => 'Text Box' ),
            array( 'option_key' => 'select',            'option_val' => 'Select Field' ),
            array( 'option_key' => 'textarea',          'option_val' => 'Text Area' ),
            array( 'option_key' => 'file',              'option_val' => 'File' ),
            array( 'option_key' => 'product',           'option_val' => 'Product' ),
            array( 'option_key' => 'category',          'option_val' => 'Category' ),
            array( 'option_key' => 'category_product',  'option_val' => 'Category Product' ),
            array( 'option_key' => 'repeater',          'option_val' => 'Repeater' ),
        );

        if ( class_exists( 'Webbycrown\BlogBagisto\Providers\BlogServiceProvider' ) ) {

            $types[] = array( 'option_key' => 'blog', 'option_val' => 'Blog' );

        }

        return view( $this->_config[ 'view' ], compact( 'page_slug', 'section_slug', 'customization_section', 'customization_page', 'types' ) );
    }

    /**
     * Listing for repeater sections setting.
     *
     * @param string $page_slug    The slug of the page.
     * @param string $section_slug The slug of the section.
     * @param int    $id           The ID of the section.
     */
    public function repeater_sections_setting_index( $page_slug, $section_slug, $id )
    {
        if ( request()->ajax() ) {

            return app(CustomizationSettingDataGrid::class)->toJson();

        }

        $customization_setting = CustomizationSettings::where( 'id', $id )->firstOrFail();

        $customization_page = CustomizationPages::where( 'slug', $page_slug )->firstOrFail();

        $customization_section = CustomizationSections::where( 'page_slug', $page_slug )->where( 'slug', $section_slug )->firstOrFail();

        return view( $this->_config[ 'view' ], compact( 'id', 'page_slug', 'section_slug', 'customization_setting', 'customization_page', 'customization_section' ) );
    }

    /**
     * Retrieves data for a specific section within a page.
     *
     * @param string $page_slug     The slug of the page.
     * @param string $section_slug  The slug of the section within the page.
     * @return mixed                Data related to the specified section.
     */
    public function sections_index( $page_slug, $section_slug )
    {
        $customizations = CustomizationHelpers::get_customization_data_by_slug( $page_slug, $section_slug );

        $fields = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'fields', $customizations ) ) ? $customizations[ 'fields' ] : [];

        if (request()->ajax()) {

            $section_form = '';

            $field_details = $repeaters = [];

            $section_details = CustomizationDetails::where( 'page_slug', $page_slug )->where( 'section_slug', $section_slug )->first();

            if ( $section_details ) {

                $field_details = (array)json_decode( $section_details->field_details );

            }

            if ( $fields && count( $fields ) > 0 ) {
                
                foreach ( $fields as $key => $field ) {
                    
                    $required_field = $field[ 'required' ] ? 'required' : '';

                    $section_field_val = array_key_exists( $field[ 'name' ], $field_details ) ? $field_details[ $field[ 'name' ] ] : '';

                    $multiple_flag = ( array_key_exists( 'multiple', $field ) && $field[ 'multiple' ] == true ) ? true : false;

                    $multiple_option = ( $multiple_flag == true ) ? 'multiple' : '';

                    if ( in_array( $field[ 'type' ], array( 'text', 'select', 'textarea' ) ) ) {

                        $field_input_name = ( $field[ 'type' ] == 'select' && $multiple_flag == true ) ? 'field_details[' . $field[ 'name' ] . '][]' : 'field_details[' . $field[ 'name' ] . ']';

                        if ( $field[ 'type' ] == 'select' ) {

                            $section_field_val = ( is_array( $section_field_val ) ) ? $section_field_val : array( $section_field_val );

                        }
                        
                        ob_start();

                        echo view( 'wc_customization::admin.fields.' . $field[ 'type' ], compact( 'field', 'section_field_val', 'required_field', 'multiple_option', 'field_input_name' ) );

                        $section_form .= ob_get_clean();

                    }

                    if ( $field[ 'type' ] == 'file' ) {

                        $pre_file_url = CustomizationHelpers::pre_file_url();

                        $field_input_name = ( $multiple_flag == true ) ? $field[ 'name' ] . '[]' : $field[ 'name' ];

                        $section_field_val = ( is_array( $section_field_val ) ) ? $section_field_val : ( ( isset( $section_field_val ) && !is_null( $section_field_val ) ) ? array( $section_field_val ) : array() );

                        ob_start();

                        echo view( 'wc_customization::admin.fields.file', compact( 'field', 'section_field_val', 'required_field', 'multiple_option', 'field_input_name', 'pre_file_url' ) );

                        $section_form .= ob_get_clean();

                    }

                    if ( $field[ 'type' ] == 'product' ) {

                        $products = '';

                        $field_input_name = ( $multiple_flag == true ) ? 'field_details[' . $field[ 'name' ] . '][]' : 'field_details[' . $field[ 'name' ] . ']';

                        if ( class_exists( 'Webkul\Category\Models\Category' ) ) {

                            $products = ProductFlat::whereNull( 'parent_id' )->where( 'status', 1 )->get();

                        }

                        ob_start();

                        echo view( 'wc_customization::admin.fields.product', compact( 'field', 'section_field_val', 'required_field', 'multiple_option', 'field_input_name', 'products' ) );

                        $section_form .= ob_get_clean();

                    }

                    if ( in_array( $field[ 'type' ], array( 'category', 'category_product' ) ) ) {

                        $categorys = '';

                        $field_input_name = ( $multiple_flag == true ) ? 'field_details[' . $field[ 'name' ] . '][]' : 'field_details[' . $field[ 'name' ] . ']';

                        if ( class_exists( 'Webkul\Category\Models\Category' ) ) {

                            $categorys = Category::leftjoin('category_translations as cat_trl', 'cat_trl.category_id', '=', 'categories.id')
                            ->select( 'categories.id', 'cat_trl.name' )
                            ->where( 'categories.status', 1 )
                            ->where( 'cat_trl.locale', 'en' )
                            ->get();

                        }

                        ob_start();

                        echo view( 'wc_customization::admin.fields.category', compact( 'field', 'section_field_val', 'required_field', 'multiple_option', 'field_input_name', 'categorys' ) );

                        $section_form .= ob_get_clean();

                    }

                    if ( class_exists( 'Webbycrown\BlogBagisto\Providers\BlogServiceProvider' ) ) {

                        if ( $field[ 'type' ] == 'blog' ) {

                            $blogs = '';

                            $field_input_name = ( $multiple_flag == true ) ? 'field_details[' . $field[ 'name' ] . '][]' : 'field_details[' . $field[ 'name' ] . ']';

                            $if_blog_table = CustomizationHelpers::check_table_exist_in_database( 'Webbycrown\BlogBagisto\Models\Blog' );

                            if ( $if_blog_table ) {

                                $blogs = Blog::where('published_at', '<=', Carbon::now()->format('Y-m-d'))
                                ->where( 'status', 1 )
                                ->where( 'locale', 'en' )
                                ->get();

                            }

                            ob_start();

                            echo view( 'wc_customization::admin.fields.blog', compact( 'field', 'section_field_val', 'required_field', 'multiple_option', 'field_input_name', 'blogs' ) );

                            $section_form .= ob_get_clean();

                        }

                    }

                    if ( $field[ 'type' ] == 'repeater' ) {

                        $repeaters[] = $field;

                    }

                }

            } else {

                $section_form = '<div class="text-red-500 font-semibold">There is no setting here.</div>';

            }

            return response()->json([
                'data' => array(
                    'fields' => $fields,
                    'field_details' => $field_details,
                    'pre_file_url' => CustomizationHelpers::pre_file_url(),
                    'section_form' => $section_form,
                    'page_slug' => $page_slug,
                    'section_slug' => $section_slug,
                    'section_details' => $section_details,
                    'repeaters' => $repeaters
                )
            ],200);

        }

        $customization_section = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'section', $customizations ) ) ? $customizations[ 'section' ] : [];

        if ( !$customization_section || empty( $customization_section ) ) {
            abort( 404 );
        }

        return view( $this->_config[ 'view' ], compact( 'page_slug', 'section_slug', 'customization_section', 'customizations', 'fields' ) );
    }

    /**
     * Handles the store of page data.
     *
     * @return JsonResponse A JSON response indicating the success or failure of the operation.
     */
    public function page_store(): JsonResponse
    {
        $data = request()->all();

        $page_id = array_key_exists( 'page_id', $data ) ? $data[ 'page_id' ] : 0;

        $page_title = array_key_exists( 'title', $data ) ? $data[ 'title' ] : null;

        $page_slug = array_key_exists( 'slug', $data ) ? $data[ 'slug' ] : null;

        $require_arr = array( 'title' => 'required' );

        $store_data = array( 'title' => $page_title );

        if ( (int)$page_id <= 0 ) {

            $require_arr[ 'slug' ] = 'required';

            $store_data[ 'slug' ] = $page_slug;

            $if_exist = CustomizationPages::where( 'slug', $page_slug )->first();

            if ( $if_exist ) {

                return new JsonResponse([
                    'status_code' => 500,
                    'status' => 'error',
                    'message' => 'Page slug already used',
                ]);

            }

        } else {

            $if_exist = CustomizationPages::where( 'slug', $page_slug )->where( 'id', '!=', $page_id )->first();

            if ( $if_exist ) {

                return new JsonResponse([
                    'status_code' => 500,
                    'status' => 'error',
                    'message' => 'Page slug already used',
                ]);

            }

            $if_exist = CustomizationPages::where( 'id', $page_id )->first();

        }

        $this->validate( request(), $require_arr );

        if ( $if_exist ) {

            $if_exist->update( $store_data );

        } else {

            CustomizationPages::create( $store_data );
        }

        return new JsonResponse([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Save Customization Page Successfully',
        ]);

    }

    /**
     * Handles the store of section data.
     *
     * @return JsonResponse A JSON response indicating the success or failure of the operation.
     */
    public function section_store(): JsonResponse
    {
        $data = request()->all();

        $section_id = array_key_exists( 'section_id', $data ) ? $data[ 'section_id' ] : 0;

        $page_slug = array_key_exists( 'page_slug', $data ) ? $data[ 'page_slug' ] : null;

        $section_title = array_key_exists( 'title', $data ) ? $data[ 'title' ] : null;

        $section_slug = array_key_exists( 'slug', $data ) ? $data[ 'slug' ] : null;

        $require_arr = array( 'title' => 'required' );

        $store_data = array( 'title' => $section_title );

        if ( (int)$section_id <= 0 ) {

            $require_arr[ 'slug' ] = 'required';

            $require_arr[ 'page_slug' ] = 'required';

            $store_data[ 'slug' ] = $section_slug;

            $store_data[ 'page_slug' ] = $page_slug;

            $if_exist = CustomizationSections::where( 'page_slug', $page_slug )->where( 'slug', $section_slug )->first();

            if ( $if_exist ) {

                return new JsonResponse([
                    'status_code' => 500,
                    'status' => 'error',
                    'message' => 'Section slug already used',
                ]);

            }

        } else {

            $if_exist = CustomizationSections::where( 'page_slug', $page_slug )->where( 'slug', $section_slug )->where( 'id', '!=', $section_id )->first();

            if ( $if_exist ) {

                return new JsonResponse([
                    'status_code' => 500,
                    'status' => 'error',
                    'message' => 'Section slug already used',
                ]);

            }

            $if_exist = CustomizationSections::where( 'id', $section_id )->first();

        }

        $this->validate( request(), $require_arr );

        if ( $if_exist ) {

            $if_exist->update( $store_data );

        } else {

            CustomizationSections::create( $store_data );
        }

        return new JsonResponse([
            'status_code' => 200,
            'status' => 'success',
            'message' => 'Save Customization Section Successfully',
        ]);

    }

    /**
     * Handles the validation of section setting.
     *
     * @return JsonResponse A JSON response indicating the success or failure of the operation.
     */
    public function section_setting_validate(): JsonResponse
    {
        $data = request()->all();

        $page_slug = array_key_exists( 'page_slug', $data ) ? trim( $data[ 'page_slug' ] ) : null;

        $section_slug = array_key_exists( 'section_slug', $data ) ? trim( $data[ 'section_slug' ] ) : null;

        $field_code = array_key_exists( 'field_code', $data ) ? trim( $data[ 'field_code' ] ) : null;

        $section_setting_id = array_key_exists( 'section_setting_id', $data ) ? (int)$data[ 'section_setting_id' ] : 0;

        if ( (int)$section_setting_id > 0 ) {

            $if_exist = CustomizationSettings::where( 'page_slug', $page_slug )
            ->where( 'section_slug', $section_slug )
            ->where( 'name', $field_code )
            ->where( 'id', '!=', $section_setting_id )
            ->first();

        } else {

            $if_exist = CustomizationSettings::where( 'page_slug', $page_slug )->where( 'section_slug', $section_slug )->where( 'name', $field_code )->first();

        }

        if ( $if_exist ) {

            return new JsonResponse([
                'status_code' => 200,
                'status' => 'success',
                'message' => 'key already exist for this section',
            ]);

        }

        return new JsonResponse([
            'status_code' => 200,
            'status' => 'error',
            'message' => 'key already not exist for this section',
        ]);

    }

    /**
     * Handles the store of section setting data.
     *
     * @return JsonResponse A JSON response indicating the success or failure of the operation.
     */
    public function section_setting_store(): JsonResponse
    {
        $data = request()->all();

        $section_setting_id = array_key_exists( 'section_setting_id', $data ) ? $data[ 'section_setting_id' ] : 0;

        $page_slug = array_key_exists( 'page_slug', $data ) ? $data[ 'page_slug' ] : null;

        $section_slug = array_key_exists( 'section_slug', $data ) ? $data[ 'section_slug' ] : null;

        $field_title = array_key_exists( 'field_title', $data ) ? $data[ 'field_title' ] : null;

        $field_name = array_key_exists( 'field_name', $data ) ? $data[ 'field_name' ] : null;

        $field_type = array_key_exists( 'field_type', $data ) ? $data[ 'field_type' ] : null;

        $field_required = array_key_exists( 'field_required', $data ) ? $data[ 'field_required' ] : 0;

        $field_multiple = array_key_exists( 'field_multiple', $data ) ? $data[ 'field_multiple' ] : 0;

        $field_status = array_key_exists( 'field_status', $data ) ? $data[ 'field_status' ] : 0;

        $field_option = array_key_exists( 'field_option', $data ) ? $data[ 'field_option' ] : null;

        $setting_parent_id = array_key_exists( 'setting_parent_id', $data ) ? (int)$data[ 'setting_parent_id' ] : 0;

        $setting_type = array_key_exists( 'setting_type', $data ) ? $data[ 'setting_type' ] : null;

        $field_required = ( (int)$field_required > 0 ) ? 1 : 0;

        $field_multiple = ( (int)$field_multiple > 0 ) ? 1 : 0;
        
        $field_status = ( (int)$field_status > 0 ) ? 1 : 0;

        $options = array();

        if ( $field_type == 'select' ) {

            $options = array( 'options' => $field_option );

        }

        $other_settings = json_encode( $options );

        $store_data = array(
            'page_slug' => $page_slug,
            'section_slug' => $section_slug,
            'title' => $field_title,
            'name' => $field_name,
            'type' => $field_type,
            'required' => $field_required,
            'multiple' => $field_multiple,
            'status' => $field_status,
            'parent_id' => $setting_parent_id,
            'setting_type' => $setting_type,
            'other_settings' => $other_settings,
        );

        if ( (int)$section_setting_id > 0 ) {

            $if_exist = CustomizationSettings::where( 'id', $section_setting_id )->first();

        } else {

            $if_exist = CustomizationSettings::where( 'page_slug', $page_slug )->where( 'section_slug', $section_slug )->where( 'name', $field_name )->first();

        }

        if ( $if_exist ) {

            unset( $store_data[ 'page_slug' ] );

            unset( $store_data[ 'section_slug' ] );

            $if_exist->update( $store_data );

        } else {

            CustomizationSettings::create( $store_data );

        }

        return new JsonResponse([
            'message' => 'Save Customization Setting Successfully',
        ]);

    }

    /**
     * Handles the stor of section detail data for page.
     *
     * @return JsonResponse A JSON response indicating the success or failure of the operation.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'page_slug'     => 'required',
            'section_slug'  => 'required',
            'field_details' => 'array',
        ]);

        $data = request()->all();

        $page_slug = array_key_exists( 'page_slug', $data ) ? $data[ 'page_slug' ] : null;

        $section_slug = array_key_exists( 'section_slug', $data ) ? $data[ 'section_slug' ] : null;

        $field_details = array_key_exists( 'field_details', $data ) ? $data[ 'field_details' ] : array();

        $repeater_data = array_key_exists( 'repeater_data', $data ) ? $data[ 'repeater_data' ] : array();

        if ( $repeater_data && is_array( $repeater_data ) && count( $repeater_data ) > 0 ) {

            $field_details = array_merge( $field_details, $repeater_data );

        }

        $file_field_keys = CustomizationHelpers::get_file_field_keys();

        $file_field_keys_with_rp = CustomizationHelpers::get_file_field_keys( 'repeater' );

        $repeater_field_keys = CustomizationHelpers::get_repeater_field_keys();

        $file_flags_arr = array();
        
        $allFiles = request()->allFiles();
        
        if ( $allFiles && is_array( $allFiles ) && count( $allFiles ) > 0 ) {
            foreach ( $allFiles as $file_key => $file_val ) {

                // add repeaters file
                if ( $file_key == 'repeater_data' ) {

                    foreach ( $file_val as $repeater_field_name => $repeater_files ) {

                        if ( is_array( $repeater_field_keys ) && in_array( $repeater_field_name, $repeater_field_keys ) && is_array( $repeater_files ) && count( $repeater_files ) > 0 ) {

                            foreach ( $repeater_files as $repeater_field_index => $repeater_file ) {

                                if ( $file_field_keys_with_rp && count( $file_field_keys_with_rp ) > 0 ) {

                                    foreach ( $file_field_keys_with_rp as $file_field_key ) {

                                        if ( is_array( $repeater_file ) && array_key_exists( $file_field_key, $repeater_file ) ) {

                                            $rp_file = $repeater_file[$file_field_key];

                                            $field_details = CustomizationHelpers::operation_for_upload_media_in_customization_store( $file_field_key, $rp_file, $field_details, $page_slug, $section_slug, 'repeater', $repeater_field_name, $repeater_field_index  );

                                            $file_flags_arr[ $repeater_field_name.'###'.$repeater_field_index.'###'.$file_field_key ] = true;

                                        }

                                    }

                                }

                            }

                        }

                    }

                } else {

                    // add normal file
                    if ( $file_field_keys && is_array( $file_field_keys ) && in_array( $file_key, $file_field_keys ) ) {

                        $field_details = CustomizationHelpers::operation_for_upload_media_in_customization_store( $file_key, $file_val, $field_details, $page_slug, $section_slug );

                    }

                }

            }

        }

        // add old normal file value
        if ( $file_field_keys && count( $file_field_keys ) > 0 ) {

            foreach ( $file_field_keys as $file_field_key ) {

                if ( !array_key_exists( $file_field_key, $allFiles ) ) {

                    $if_exist = CustomizationSettings::where( 'name', $file_field_key )
                    ->where( 'page_slug', $page_slug )
                    ->where( 'section_slug', $section_slug )
                    ->where( 'parent_id', 0 )
                    ->whereNull( 'setting_type' )
                    ->first();

                    if ( $if_exist ) {
                        
                        $field_details = CustomizationHelpers::operation_for_upload_media_in_customization_store( $file_field_key, null, $field_details, $page_slug, $section_slug );

                    }

                }

            }

        }

        // add old repeater file value
        if ( $repeater_data && is_array( $repeater_data ) && count( $repeater_data ) > 0 ) {

            foreach ( $repeater_data as $repeater_field_name => $repeater_field_datas ) {

                if ( is_array( $repeater_field_keys ) && in_array( $repeater_field_name, $repeater_field_keys ) && is_array( $repeater_field_datas ) && count( $repeater_field_datas ) > 0 ) {

                    foreach ( $repeater_field_datas as $repeater_row_index => $repeater_row_data ) {

                        if ( is_array( $repeater_row_data ) && count( $repeater_row_data ) > 0 ) {

                            foreach ( $repeater_row_data as $rp_row_field_name => $rp_row_field_val ) {

                                $flag_key = $repeater_field_name.'###'.$repeater_row_index.'###'.$rp_row_field_name;

                                if ( is_array( $file_flags_arr ) && count( $file_flags_arr ) > 0 && array_key_exists( $flag_key, $file_flags_arr ) ) {

                                    if ( $rp_row_field_name == 'hidden' ) {

                                        unset( $field_details[ $repeater_field_name ][ $repeater_row_index ][ $rp_row_field_name ] );

                                    }

                                } else {

                                    if ( $rp_row_field_name == 'hidden' ) {

                                        foreach ( $rp_row_field_val as $hidden_rp_row_field_name => $hidden_rp_row_field_val ) {

                                            $hidden_flag_key = $repeater_field_name.'###'.$repeater_row_index.'###'.$hidden_rp_row_field_name;

                                            if ( is_array( $file_flags_arr ) && !array_key_exists( $hidden_flag_key, $file_flags_arr ) ) {

                                                $field_details[ $repeater_field_name ][ $repeater_row_index ][ $hidden_rp_row_field_name ] = $hidden_rp_row_field_val;

                                            }

                                        }

                                        unset( $field_details[ $repeater_field_name ][ $repeater_row_index ][ $rp_row_field_name ] );

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

        $if_exist = CustomizationDetails::where( 'page_slug', $page_slug )->where( 'section_slug', $section_slug )->first();

        if ( $if_exist ) {

            CustomizationDetails::where( 'page_slug', $page_slug )->where( 'section_slug', $section_slug )->update([ 'field_details' => json_encode( $field_details ) ]);

        } else {

            $create_data = array(
                'page_slug' => $page_slug,
                'section_slug' => $section_slug,
                'field_details' => json_encode( $field_details )
            );
            
            CustomizationDetails::create( $create_data );
        }

        return new JsonResponse([
            'message' => 'Save Customization Details Successfully',
        ]);
    }

    /**
     * Remove the specified resource from page.
     */
    public function page_delete(int $id): JsonResponse
    {
        try {

            $page = CustomizationPages::find( $id );

            if ( $page ) {

                $settings = CustomizationSettings::where( 'page_slug', $page->slug )
                                                ->where( 'parent_id', 0 )
                                                ->whereNull( 'setting_type' )
                                                ->get();
                
                if ( $settings && count( $settings ) > 0 ) {
                
                    foreach ( $settings as $setting ) {
                
                        $this->section_setting_delete( $setting->id );
                
                    }
                
                }
                
                CustomizationSections::where( 'page_slug', $page->slug )->delete();
                
                CustomizationDetails::where( 'page_slug', $page->slug )->delete();
                
                $page->delete();

                return new JsonResponse([
                    'message' => 'The page has been successfully deleted and also deleted this all sections, setting and value.',
                ]);

            }

            return new JsonResponse([
                'message' => 'Something went wrong while deleting page',
            ], 500);

        } catch (\Exception $e) {

            return new JsonResponse([
                'message' => 'Error encountered while deleting page',
            ], 500);

        }

    }

    /**
     * Remove the specified resource from section.
     */
    public function section_delete(int $id): JsonResponse
    {
        try {

            $section = CustomizationSections::find( $id );

            if ( $section ) {
                
                $settings = CustomizationSettings::where( 'page_slug', $section->page_slug )
                                                ->where( 'section_slug', $section->slug )
                                                ->where( 'parent_id', 0 )
                                                ->whereNull( 'setting_type' )
                                                ->get();
                
                if ( $settings && count( $settings ) > 0 ) {
                
                    foreach ( $settings as $setting ) {
                
                        $this->section_setting_delete( $setting->id );
                
                    }
                
                }
                
                CustomizationDetails::where( 'page_slug', $section->page_slug )->where( 'section_slug', $section->slug )->delete();
                
                $section->delete();

                return new JsonResponse([
                    'message' => 'The section has been successfully deleted and also deleted this all settings and values.',
                ]);

            }

            return new JsonResponse([
                'message' => 'Something went wrong while deleting section',
            ], 500);

        } catch (\Exception $e) {

            return new JsonResponse([
                'message' => 'Error encountered while deleting section',
            ], 500);

        }

    }

    /**
     * Remove the specified resource from section setting.
     */
    public function section_setting_delete(int $id): JsonResponse
    {
        try {

            $setting = CustomizationSettings::find( $id );

            if ( $setting ) {

                $old_setting = $setting;

                $id = $old_setting->id;
                
                $parent_id = $old_setting->parent_id;
                
                $page_slug = $old_setting->page_slug;
                
                $section_slug = $old_setting->section_slug;
                
                $field_key = $old_setting->name;
                
                $field_type = $old_setting->type;
                
                $field_setting_type = $old_setting->setting_type;

                $setting->delete();

                $setting_id = ( $field_setting_type == 'repeater' ) ? $parent_id : $id;
                
                CustomizationHelpers::delete_field_value_in_customization_detail( $page_slug, $section_slug, $field_key, $field_type, $field_setting_type, $setting_id );

                if ( $field_type == 'repeater' && (int)$setting_id > 0 ) {
                
                    $repeater_fields = CustomizationSettings::where( 'page_slug', $page_slug )
                                                            ->where( 'section_slug', $section_slug )
                                                            ->where( 'parent_id', $setting_id )
                                                            ->where( 'setting_type', 'repeater' )
                                                            ->delete();
                
                }

                return new JsonResponse([
                    'message' => 'The section setting has been successfully deleted and also deleted this value.',
                ]);

            }

            return new JsonResponse([
                'message' => 'Something went wrong while deleting section setting',
            ], 500);

        } catch (\Exception $e) {

            return new JsonResponse([
                'message' => 'Error encountered while deleting section setting',
            ], 500);

        }

    }

}