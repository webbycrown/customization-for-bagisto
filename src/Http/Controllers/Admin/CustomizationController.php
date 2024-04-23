<?php

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // $customizations = CustomizationHelpers::get_customizations_data();

        // $pages = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'pages', $customizations) ) ? $customizations[ 'pages' ] : [];

        if ( request()->ajax() ) {

            return app(CustomizationPageDataGrid::class)->toJson();

        }

        return view( $this->_config[ 'view' ] );
        // return view( $this->_config[ 'view' ], compact( 'customizations' ) );

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function page_edit(int $id): JsonResponse
    {
        $customization_page = CustomizationPages::where( 'id', $id )->firstOrFail();

        return new JsonResponse([
            'data' => $customization_page,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function section_edit(int $id): JsonResponse
    {
        $customization_section = CustomizationSections::where( 'id', $id )->firstOrFail();

        return new JsonResponse([
            'data' => $customization_section,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function pages_index( $page_slug )
    {
        // $customizations = CustomizationHelpers::get_customization_data_by_slug( $page_slug );

        // $sections = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'sections', $customizations) ) ? $customizations[ 'sections' ] : [];

        if ( request()->ajax() ) {

            return app(CustomizationSectionDataGrid::class)->toJson();

        }

        // $customization_page = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'page', $customizations ) ) ? $customizations[ 'page' ] : [];

        // if ( !$customization_page || empty( $customization_page ) ) {
        //     abort( 404 );
        // }

        // return view( $this->_config[ 'view' ], compact( 'page_slug', 'customization_page', 'sections', 'customizations' ) );

        $customization_page = CustomizationPages::where( 'slug', $page_slug )->firstOrFail();

        return view( $this->_config[ 'view' ], compact( 'page_slug', 'customization_page' ) );

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function sections_setting_index( $page_slug, $section_slug )
    {
        if ( request()->ajax() ) {

            return app(CustomizationSettingDataGrid::class)->toJson();

        }

        // $customizations = CustomizationHelpers::get_customization_data_by_slug( $page_slug, $section_slug );

        // $fields = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'fields', $customizations ) ) ? $customizations[ 'fields' ] : [];

        // $customization_section = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'section', $customizations ) ) ? $customizations[ 'section' ] : [];

        // if ( !$customization_section || empty( $customization_section ) ) {
        //     abort( 404 );
        // }

        // return view( $this->_config[ 'view' ], compact( 'page_slug', 'section_slug', 'customization_section', 'customizations', 'fields' ) );

        $customization_page = CustomizationPages::where( 'slug', $page_slug )->firstOrFail();

        $customization_section = CustomizationSections::where( 'page_slug', $page_slug )->where( 'slug', $section_slug )->firstOrFail();

        return view( $this->_config[ 'view' ], compact( 'page_slug', 'section_slug', 'customization_section', 'customization_page' ) );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function repeater_sections_setting_index( $page_slug, $section_slug, $id )
    {
        if ( request()->ajax() ) {

            return app(CustomizationSettingDataGrid::class)->toJson();

        }

        // $customizations = CustomizationHelpers::get_customization_data_by_slug( $page_slug, $section_slug );

        // $fields = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'fields', $customizations ) ) ? $customizations[ 'fields' ] : [];

        // $customization_section = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'section', $customizations ) ) ? $customizations[ 'section' ] : [];

        // if ( !$customization_section || empty( $customization_section ) ) {
        //     abort( 404 );
        // }

        // return view( $this->_config[ 'view' ], compact( 'id', 'page_slug', 'section_slug', 'customization_section', 'customizations', 'fields' ) );

        $customization_setting = CustomizationSettings::where( 'id', $id )->firstOrFail();

        $customization_page = CustomizationPages::where( 'slug', $page_slug )->firstOrFail();

        $customization_section = CustomizationSections::where( 'page_slug', $page_slug )->where( 'slug', $section_slug )->firstOrFail();

        return view( $this->_config[ 'view' ], compact( 'id', 'page_slug', 'section_slug', 'customization_setting', 'customization_page', 'customization_section' ) );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
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

                    if ( $field[ 'type' ] == 'text' ) {
                        
                        $section_form .= '<div class="mb-4">
                                            <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium ' . $required_field . '">' . $field[ 'title' ] . '</label>
                                            <input 
                                                type="' . $field[ 'type' ] . '" 
                                                name="field_details[' . $field[ 'name' ] . ']" 
                                                class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800" 
                                                id="' . $field[ 'name' ] . '" 
                                                placeholder="' . $field[ 'title' ] . '"
                                                value="' . $section_field_val . '"
                                                ' . $required_field . '
                                            >
                                        </div>';

                    }

                    if ( $field[ 'type' ] == 'select' ) {

                        $section_option = '';

                        if ( array_key_exists( 'options', $field ) &&  $field[ 'options' ] && count( $field[ 'options' ] ) > 0 ) {
                            
                            foreach ( $field[ 'options' ] as $option_key => $option_val ) {

                                $selected_val = ( $section_field_val == $option_key ) ? 'selected' : '';
                                
                                $section_option .= '<option value="'.$option_key.'" '.$selected_val.'>'.$option_val.'</option>';

                            }

                        }
                        
                        $section_form .= '<div class="mb-4">
                                            <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium ' . $required_field . '">' . $field[ 'title' ] . '</label>
                                            <select 
                                                name="field_details[' . $field[ 'name' ] . ']" 
                                                class="custom-select w-full py-2.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 dark:hover:border-gray-400" 
                                                id="' . $field[ 'name' ] . '"
                                                ' . $required_field . '
                                            >
                                                <option value=""> Select ' . $field[ 'title' ] . ' </option>
                                                ' . $section_option . '
                                            </select>
                                        </div>';

                    }

                    if ( $field[ 'type' ] == 'textarea' ) {
                        
                        $section_form .= '<div class="mb-4 !mb-0">
                                            <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium ' . $required_field . '">' . $field[ 'title' ] . '</label>
                                            <textarea 
                                                type="' . $field[ 'type' ] . '" 
                                                name="field_details[' . $field[ 'name' ] . ']" 
                                                class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800" 
                                                id="' . $field[ 'name' ] . '" 
                                                placeholder="' . $field[ 'title' ] . '"
                                                ' . $required_field . '
                                            >' . $section_field_val . '</textarea>
                                        </div>';

                    }

                    if ( $field[ 'type' ] == 'file' ) {

                        $multiple_flag = ( array_key_exists( 'multiple', $field ) && $field[ 'multiple' ] == true ) ? true : false;

                        $multiple_file_option = ( $multiple_flag == true ) ? 'multiple' : '';

                        $file_input_name = ( $multiple_flag == true ) ? $field[ 'name' ] . '[]' : $field[ 'name' ];

                        $file_content = '';

                        if ( $multiple_flag == true ) {

                            if ( $section_field_val && is_array( $section_field_val ) && count( $section_field_val ) > 0 ) {

                                $file_content .= '<div class="flex ">';

                                foreach ( $section_field_val as $file ) {

                                    $file_content .= '<a 
                                                    href="' . CustomizationHelpers::pre_file_url() . $file . '" 
                                                    target="_blank"
                                                >
                                                    <img 
                                                        src="' . CustomizationHelpers::pre_file_url() . $file . '" 
                                                        class="relative mr-5 h-[33px] w-[33px] top-15 rounded-3 border-3 border-gray-500"
                                                    >
                                                </a>';

                                }

                                $file_content .= '</div>';

                            }

                        } else {

                            if ( isset( $section_field_val ) && !empty( $section_field_val ) && !is_null( $section_field_val ) ) {

                                $file_content .= '<a 
                                                    href="' . CustomizationHelpers::pre_file_url() . $section_field_val . '" 
                                                    target="_blank"
                                                >
                                                    <img 
                                                        src="' . CustomizationHelpers::pre_file_url() . $section_field_val . '" 
                                                        class="relative mr-5 h-[33px] w-[33px] top-15 rounded-3 border-3 border-gray-500"
                                                    >
                                                </a>';
                            
                            }

                        }
                        
                        $section_form .= '<div class="mb-4">
                                            <div class="flex justify-between">
                                                <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium 
                                                ' . ( ( isset( $section_field_val ) && !empty( $section_field_val ) && !is_null( $section_field_val ) ) 
                                                    ? "" 
                                                    : $required_field ) . '" 
                                                    for="field_details[' . $field[ 'name' ] . ']"
                                                    > ' . $field[ 'title' ] . '
                                                    <span class=""></span>
                                                </label>
                                            </div>
                                            <div class="flex justify-center items-center">
                                                <input 
                                                    type="' . $field[ 'type' ] . '" 
                                                    name="' . $file_input_name . '" 
                                                    class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 dark:file:bg-gray-800 dark:file:dark:text-white dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800" 
                                                    id="' . $field[ 'name' ] . '"
                                                    ' . ( ( isset( $section_field_val ) && !empty( $section_field_val ) && !is_null( $section_field_val ) ) 
                                                        ? "" 
                                                        : $required_field ) . '
                                                    ' . $multiple_file_option . '
                                                >
                                            </div>
                                            ' . $file_content . '
                                        </div>';

                    }

                    if ( $field[ 'type' ] == 'product' ) {

                        $section_option = '';

                        $multiple_flag = ( array_key_exists( 'multiple', $field ) && $field[ 'multiple' ] == true ) ? true : false;

                        $multiple_file_option = ( $multiple_flag == true ) ? 'multiple' : '';

                        $file_input_name = ( $multiple_flag == true ) ? 'field_details[' . $field[ 'name' ] . '][]' : $field[ 'name' ];

                        $products = ProductFlat::whereNull( 'parent_id' )->where( 'status', 1 )->get();

                        if ( $products && count( $products ) > 0 ) {
                            
                            foreach ( $products as $product ) {

                                $selected_val = ( is_array( $section_field_val ) && in_array( $product->id, $section_field_val ) ) ? 'selected' : '';
                                
                                $section_option .= '<option value="'.$product->id.'" '.$selected_val.'>[ '.$product->sku.' ] '.$product->name.'</option>';

                            }

                        }
                        
                        $section_form .= '<div class="mb-4">
                                            <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium ' . $required_field . '">' . $field[ 'title' ] . '</label>
                                            <select 
                                                name="' . $file_input_name . '" 
                                                class="custom-select w-full py-2.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 dark:hover:border-gray-400" 
                                                id="' . $field[ 'name' ] . '"
                                                ' . $required_field . '
                                                ' . $multiple_file_option . '
                                            >
                                                ' . $section_option . '
                                            </select>
                                        </div>';

                    }

                    if ( $field[ 'type' ] == 'category' ) {

                        $section_option = '';

                        $multiple_flag = ( array_key_exists( 'multiple', $field ) && $field[ 'multiple' ] == true ) ? true : false;

                        $multiple_file_option = ( $multiple_flag == true ) ? 'multiple' : '';

                        $file_input_name = ( $multiple_flag == true ) ? 'field_details[' . $field[ 'name' ] . '][]' : $field[ 'name' ];

                        $categorys = Category::leftjoin('category_translations as cat_trl', 'cat_trl.category_id', '=', 'categories.id')
                        ->select( 'categories.id', 'cat_trl.name' )
                        ->where( 'categories.status', 1 )
                        ->where( 'cat_trl.locale', 'en' )
                        ->get();

                        if ( $categorys && count( $categorys ) > 0 ) {
                            
                            foreach ( $categorys as $category ) {

                                $selected_val = ( is_array( $section_field_val ) && in_array( $category->id, $section_field_val ) ) ? 'selected' : '';
                                
                                $section_option .= '<option value="'.$category->id.'" '.$selected_val.'>'.$category->name.'</option>';

                            }

                        }
                        
                        $section_form .= '<div class="mb-4">
                                            <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium ' . $required_field . '">' . $field[ 'title' ] . '</label>
                                            <select 
                                                name="' . $file_input_name . '" 
                                                class="custom-select w-full py-2.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 dark:hover:border-gray-400" 
                                                id="' . $field[ 'name' ] . '"
                                                ' . $required_field . '
                                                ' . $multiple_file_option . '
                                            >
                                                ' . $section_option . '
                                            </select>
                                        </div>';

                    }

                    if ( $field[ 'type' ] == 'category_product' ) {

                        $section_option = '';

                        $multiple_flag = ( array_key_exists( 'multiple', $field ) && $field[ 'multiple' ] == true ) ? true : false;

                        $multiple_file_option = ( $multiple_flag == true ) ? 'multiple' : '';

                        $file_input_name = ( $multiple_flag == true ) ? 'field_details[' . $field[ 'name' ] . '][]' : $field[ 'name' ];

                        $categorys = Category::leftjoin('category_translations as cat_trl', 'cat_trl.category_id', '=', 'categories.id')
                        ->select( 'categories.id', 'cat_trl.name' )
                        ->where( 'categories.status', 1 )
                        ->where( 'cat_trl.locale', 'en' )
                        ->get();

                        if ( $categorys && count( $categorys ) > 0 ) {
                            
                            foreach ( $categorys as $category ) {

                                $selected_val = ( is_array( $section_field_val ) && in_array( $category->id, $section_field_val ) ) ? 'selected' : '';
                                
                                $section_option .= '<option value="'.$category->id.'" '.$selected_val.'>'.$category->name.'</option>';

                            }

                        }
                        
                        $section_form .= '<div class="mb-4">
                                            <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium ' . $required_field . '">' . $field[ 'title' ] . '</label>
                                            <select 
                                                name="' . $file_input_name . '" 
                                                class="custom-select w-full py-2.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 dark:hover:border-gray-400" 
                                                id="' . $field[ 'name' ] . '"
                                                ' . $required_field . '
                                                ' . $multiple_file_option . '
                                            >
                                                ' . $section_option . '
                                            </select>
                                        </div>';

                    }

                    if ( $field[ 'type' ] == 'blog' ) {

                        $section_option = $blogs = '';

                        $multiple_flag = ( array_key_exists( 'multiple', $field ) && $field[ 'multiple' ] == true ) ? true : false;

                        $multiple_file_option = ( $multiple_flag == true ) ? 'multiple' : '';

                        $file_input_name = ( $multiple_flag == true ) ? 'field_details[' . $field[ 'name' ] . '][]' : $field[ 'name' ];

                        $if_blog_table = CustomizationHelpers::check_table_exist_in_database( 'Webbycrown\BlogBagisto\Models\Blog' );

                        if ( $if_blog_table ) {

                            $blogs = Blog::where('published_at', '<=', Carbon::now()->format('Y-m-d'))
                            ->where( 'status', 1 )
                            ->where( 'locale', 'en' )
                            ->get();

                        }

                        if ( $blogs && count( $blogs ) > 0 ) {
                            
                            foreach ( $blogs as $blog ) {

                                $selected_val = ( is_array( $section_field_val ) && in_array( $blog->id, $section_field_val ) ) ? 'selected' : '';
                                
                                $section_option .= '<option value="'.$blog->id.'" '.$selected_val.'>'.$blog->name.'</option>';

                            }

                        }
                        
                        $section_form .= '<div class="mb-4">
                                            <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium ' . $required_field . '">' . $field[ 'title' ] . '</label>
                                            <select 
                                                name="' . $file_input_name . '" 
                                                class="custom-select w-full py-2.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 dark:hover:border-gray-400" 
                                                id="' . $field[ 'name' ] . '"
                                                ' . $required_field . '
                                                ' . $multiple_file_option . '
                                            >
                                                ' . $section_option . '
                                            </select>
                                        </div>';

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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
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

}