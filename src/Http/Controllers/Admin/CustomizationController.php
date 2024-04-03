<?php

namespace Webbycrown\Customization\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Webbycrown\Customization\Datagrids\CustomizationDataGrid;
use Webbycrown\Customization\Datagrids\CustomizationPageDataGrid;
use Webbycrown\Customization\Datagrids\CustomizationSectionDataGrid;
use Webbycrown\Customization\Models\CustomizationPages;
use Webbycrown\Customization\Models\CustomizationSections;
use Webbycrown\Customization\Models\CustomizationDetails;
use Webbycrown\Customization\Http\Controllers\Shop\CustomizationController as ShopCustomizationController;
use Intervention\Image\ImageManager;

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
    public function __construct()
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
        $customizations = $this->get_customizations_data();

        $pages = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'pages', $customizations) ) ? $customizations[ 'pages' ] : [];

        if ( request()->ajax() ) {

            return app(CustomizationPageDataGrid::class)->toJson();

            // $columns = array(
            //     array(
            //         'databaseColumnName' => 'title',
            //         'index' => 'title',
            //         'label' => 'Title',
            //         'type' => 'string',
            //         'options' => null,
            //         'searchable' => true,
            //         'filterable' => true,
            //         'sortable' => true,
            //         'closure' => null,
            //         'input_type' => null
            //     ),
            //     array(
            //         'databaseColumnName' => 'slug',
            //         'index' => 'slug',
            //         'label' => 'Slug',
            //         'type' => 'string',
            //         'options' => null,
            //         'searchable' => true,
            //         'filterable' => true,
            //         'sortable' => true,
            //         'closure' => null,
            //         'input_type' => null
            //     )
            // );

            // $actions = array(
            //     array(
            //         'index' => 'page_edit',
            //         'icon' => 'icon-edit',
            //         'title' => 'Edit',
            //         'method' => 'GET',
            //         'url' => []
            //     ),
            //     array(
            //         'index' => 'page_exit',
            //         'icon' => 'icon-exit',
            //         'title' => 'Section',
            //         'method' => 'GET',
            //         'url' => []
            //     )
            // );

            // $meta = array(
            //     'primary_column'=> 'title',
            //     'from'=> 1,
            //     'to'=> count( $pages) > 10 ? 10 : count( $pages ),
            //     'total'=> count( $pages ),
            //     'per_page_options'=> [ 10, 20, 30, 40, 50 ],
            //     'per_page'=> 10,
            //     'current_page'=> 1,
            //     'last_page'=> 1
            // );

            // if ( $pages && count( $pages ) > 0 ) {
                
            //     foreach ( $pages as $section_key => $page ) {
                    
            //         foreach ( $actions as $action_key => $action ) {

            //             if ( $action[ 'index' ] == 'page_edit' ) {

            //                 $actions[ $action_key ][ 'url' ] = route( 'wc_customization.admin.customization.index' );

            //             }

            //             if ( $action[ 'index' ] == 'page_exit' ) {

            //                 $actions[ $action_key ][ 'url' ] = route( 'wc_customization.admin.customization.pages.index', $page[ 'slug' ] );
                            
            //             }
                        

            //         }

            //         $pages[ $section_key ][ 'actions' ] = $actions;

            //     }

            // }
            
            // return response()->json([
            //     'columns' => $columns,
            //     'actions' => $actions,
            //     'mass_actions' => [],
            //     'meta' => $meta,
            //     'records' => $pages
            // ],200);

        }

        return view( $this->_config[ 'view' ], compact( 'customizations' ) );

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
        $customization_page = CustomizationSections::where( 'id', $id )->firstOrFail();

        return new JsonResponse([
            'data' => $customization_page,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function pages_index( $page_slug )
    {
        $customizations = $this->get_customization_data_by_slug( $page_slug );

        $sections = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'sections', $customizations) ) ? $customizations[ 'sections' ] : [];

        if ( request()->ajax() ) {

            return app(CustomizationSectionDataGrid::class)->toJson();

            // $columns = array(
            //     array(
            //         'databaseColumnName' => 'title',
            //         'index' => 'title',
            //         'label' => 'Title',
            //         'type' => 'string',
            //         'options' => null,
            //         'searchable' => true,
            //         'filterable' => true,
            //         'sortable' => true,
            //         'closure' => null,
            //         'input_type' => null
            //     ),
            //     array(
            //         'databaseColumnName' => 'slug',
            //         'index' => 'slug',
            //         'label' => 'Slug',
            //         'type' => 'string',
            //         'options' => null,
            //         'searchable' => true,
            //         'filterable' => true,
            //         'sortable' => true,
            //         'closure' => null,
            //         'input_type' => null
            //     )
            // );

            // $actions = array(
            //     array(
            //         'index' => 'section',
            //         // 'icon' => 'icon-exit',
            //         'icon' => 'icon-edit',
            //         'title' => 'Section',
            //         'method' => 'GET',
            //         'url' => []
            //     )
            // );

            // $meta = array(
            //     'primary_column'=> 'title',
            //     'from'=> 1,
            //     'to'=> count( $sections) > 10 ? 10 : count( $sections ),
            //     'total'=> count( $sections ),
            //     'per_page_options'=> [ 10, 20, 30, 40, 50 ],
            //     'per_page'=> 10,
            //     'current_page'=> 1,
            //     'last_page'=> 1
            // );

            // if ( $sections && count( $sections ) > 0 ) {
                
            //     foreach ( $sections as $section_key => $section ) {
                    
            //         foreach ( $actions as $action_key => $action ) {
                        
            //             $actions[ $action_key ][ 'url' ] = route( 'wc_customization.admin.customization.sections.index', [ $page_slug, $section[ 'slug' ] ] );

            //         }

            //         $sections[ $section_key ][ 'actions' ] = $actions;

            //     }

            // }
            
            // return response()->json([
            //     'columns' => $columns,
            //     'actions' => $actions,
            //     'mass_actions' => [],
            //     'meta' => $meta,
            //     'records' => $sections
            // ],200);

        }

        $customization_page = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'page', $customizations ) ) ? $customizations[ 'page' ] : [];

        if ( !$customization_page || empty( $customization_page ) ) {
            abort( 404 );
        }

        return view( $this->_config[ 'view' ], compact( 'page_slug', 'customization_page', 'sections', 'customizations' ) );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function sections_setting_index( $page_slug, $section_slug )
    {
        $customizations = $this->get_customization_data_by_slug( $page_slug, $section_slug );

        $fields = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'fields', $customizations ) ) ? $customizations[ 'fields' ] : [];

        $customization_section = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'section', $customizations ) ) ? $customizations[ 'section' ] : [];

        if ( !$customization_section || empty( $customization_section ) ) {
            abort( 404 );
        }

        return view( $this->_config[ 'view' ], compact( 'page_slug', 'section_slug', 'customization_section', 'customizations', 'fields' ) );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function sections_index( $page_slug, $section_slug )
    {
        $customizations = $this->get_customization_data_by_slug( $page_slug, $section_slug );

        $fields = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'fields', $customizations ) ) ? $customizations[ 'fields' ] : [];

        if (request()->ajax()) {

            $section_form = '';

            $field_details = [];

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
                                                placeholder="' . $field[ 'name' ] . '"
                                                value="' . $section_field_val . '"
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
                                                    href="' . env( 'APP_URL' ) . '/storage/' . $file . '" 
                                                    target="_blank"
                                                >
                                                    <img 
                                                        src="' . env( 'APP_URL' ) . '/storage/' . $file . '" 
                                                        class="relative mr-5 h-[33px] w-[33px] top-15 rounded-3 border-3 border-gray-500"
                                                    >
                                                </a>';

                                }

                                $file_content .= '</div>';

                            }

                        } else {

                            if ( isset( $section_field_val ) && !empty( $section_field_val ) && !is_null( $section_field_val ) ) {

                                $file_content .= '<a 
                                                    href="' . env( 'APP_URL' ) . '/storage/' . $section_field_val . '" 
                                                    target="_blank"
                                                >
                                                    <img 
                                                        src="' . env( 'APP_URL' ) . '/storage/' . $section_field_val . '" 
                                                        class="relative mr-5 h-[33px] w-[33px] top-15 rounded-3 border-3 border-gray-500"
                                                    >
                                                </a>';
                            
                            }

                        }
                        
                        $section_form .= '<div class="mb-4">
                                            <div class="flex justify-between">
                                                <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium" 
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
                                                    ' . $multiple_file_option . '
                                                >
                                            </div>
                                            ' . $file_content . '
                                        </div>';

                    }

                }

            }

            return response()->json([
                'data' => array(
                    'fields' => $fields,
                    'section_form' => $section_form,
                    'page_slug' => $page_slug,
                    'section_slug' => $section_slug,
                    'section_details' => $section_details
                )
            ],200);

        }

        $customization_section = ( !empty( $customizations ) && count( $customizations ) > 0 && array_key_exists( 'section', $customizations ) ) ? $customizations[ 'section' ] : [];

        if ( !$customization_section || empty( $customization_section ) ) {
            abort( 404 );
        }

        return view( $this->_config[ 'view' ], compact( 'page_slug', 'section_slug', 'customization_section', 'customizations', 'fields' ) );
    }

    public function get_customizations_data()
    {
        $customizations = array(
            'pages' => array( 
                array(
                    'title' => 'Page 1', 
                    'slug' => 'page-1',
                    'sections' => array(
                        array( 
                            'title' => 'Section 1-1', 
                            'slug' => 'section-1-1',
                            'fields' => array(
                                array(
                                    'name' => 'text_box',
                                    'type' => 'text',
                                    'title' => 'Text Box',
                                    'required' => true
                                ),
                                array(
                                    'type' => 'select',
                                    'title' => 'Select Box',
                                    'name' => 'select_box',
                                    'required' => false,
                                    'options' => array(
                                        'option-1' => 'Option 1',
                                        'option-2' => 'Option 2',
                                        'option-3' => 'Option 3',
                                    )
                                ),
                                array(
                                    'name' => 'text_area',
                                    'type' => 'textarea',
                                    'title' => 'Text Area',
                                    'required' => true
                                ),
                                array(
                                    'name' => 'file_upload',
                                    'type' => 'file',
                                    'title' => 'File Upload',
                                    'required' => false,
                                    'multiple' => false
                                ),
                                array(
                                    'name' => 'multiple_file_upload',
                                    'type' => 'file',
                                    'title' => 'Multiple File Upload',
                                    'required' => false,
                                    'multiple' => true
                                )
                            )
                        ),
                        array( 
                            'title' => 'Section 1-2', 
                            'slug' => 'section-1-2',
                            'fields' => array(
                                array(
                                    'name' => 'text_box',
                                    'type' => 'text',
                                    'title' => 'Text Box',
                                    'required' => true
                                ),
                                
                            )
                        ),
                        array( 
                            'title' => 'Section 1-3', 
                            'slug' => 'section-1-3',
                            'fields' => array(
                                array(
                                    'name' => 'text_box',
                                    'type' => 'text',
                                    'title' => 'Text Box',
                                    'required' => true
                                ),
                                
                                array(
                                    'name' => 'text_area',
                                    'type' => 'textarea',
                                    'title' => 'Text Area',
                                    'required' => true
                                )
                            )
                        ),
                    )
                ),
                array(
                    'title' => 'Page 2', 
                    'slug' => 'page-2',
                    'sections' => array(
                        array( 
                            'title' => 'Section 2-1', 
                            'slug' => 'section-2-1',
                            'fields' => array(
                                
                                array(
                                    'type' => 'select',
                                    'title' => 'Select Box',
                                    'name' => 'select_box',
                                    'required' => false,
                                    'options' => array(
                                        'option-1' => 'Option 1',
                                        'option-2' => 'Option 2',
                                        'option-3' => 'Option 3',
                                    )
                                ),
                                array(
                                    'name' => 'text_area',
                                    'type' => 'textarea',
                                    'title' => 'Text Area',
                                    'required' => true
                                )
                            )
                        ),
                        array( 
                            'title' => 'Section 2-2', 
                            'slug' => 'section-2-2',
                            'fields' => array(
                                
                                array(
                                    'name' => 'text_area',
                                    'type' => 'textarea',
                                    'title' => 'Text Area',
                                    'required' => true
                                )
                            )
                        ),
                        array( 
                            'title' => 'Section 2-3', 
                            'slug' => 'section-2-3',
                            'fields' => array(
                                array(
                                    'name' => 'text_box',
                                    'type' => 'text',
                                    'title' => 'Text Box',
                                    'required' => true
                                ),
                                
                                array(
                                    'name' => 'text_area',
                                    'type' => 'textarea',
                                    'title' => 'Text Area',
                                    'required' => true
                                )
                            )
                        ),
                    )
                ),
                array(
                    'title' => 'Page 3', 
                    'slug' => 'page-3',
                    'sections' => array(
                        array( 
                            'title' => 'Section 3-1', 
                            'slug' => 'section-3-1',
                            'fields' => array(
                                
                                array(
                                    'type' => 'select',
                                    'title' => 'Select Box',
                                    'name' => 'select_box',
                                    'required' => false,
                                    'options' => array(
                                        'option-1' => 'Option 1',
                                        'option-2' => 'Option 2',
                                        'option-3' => 'Option 3',
                                    )
                                ),
                            )
                        ),
                        array( 
                            'title' => 'Section 3-2', 
                            'slug' => 'section-3-2',
                            'fields' => array(
                                
                                array(
                                    'name' => 'text_area',
                                    'type' => 'textarea',
                                    'title' => 'Text Area',
                                    'required' => true
                                )
                            )
                        ),
                        array( 
                            'title' => 'Section 3-3', 
                            'slug' => 'section-3-3',
                            'fields' => array(
                                array(
                                    'name' => 'text_box',
                                    'type' => 'text',
                                    'title' => 'Text Box',
                                    'required' => true
                                ),
                                
                                array(
                                    'name' => 'text_area',
                                    'type' => 'textarea',
                                    'title' => 'Text Area',
                                    'required' => true
                                )
                            )
                        ),
                    )
                ),
            )
        );
        return $customizations;
    }

    public function get_data_by_slug( $slug = null, $data = [] )
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

    public function get_customization_data_by_slug( $page_slug = null, $section_slug = null )
    {
        $customization_data = $pages = $sections = $fields = $page = $section = [];

        $customizations = $this->get_customizations_data();

        if ( array_key_exists( 'pages', $customizations ) && !empty( $customizations ) && count( $customizations ) > 0 && isset( $page_slug ) && !empty( $page_slug ) && !is_null( $page_slug ) ) {

            $pages = $this->get_data_by_slug( $page_slug, $customizations[ 'pages' ] );
        }

        if ( array_key_exists( 'sections', $pages ) && !empty( $pages ) && count( $pages ) > 0 && isset( $section_slug ) && !empty( $section_slug ) && !is_null( $section_slug ) ) {

            $sections = $this->get_data_by_slug( $section_slug, $pages[ 'sections' ] );
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

    public function get_all_pages_data()
    {
        $pages = array(
            array( 'id' => 1, 'title' => 'Page 1', 'slug' => 'page-1' ),
            array( 'id' => 2, 'title' => 'Page 2', 'slug' => 'page-2' ),
            array( 'id' => 3, 'title' => 'Page 3', 'slug' => 'page-3' )
        );

        return $pages;
    }

    public function get_all_section_data()
    {
        $sections = array(
            array( 'id' => 1, 'title' => 'Section 1', 'slug' => 'section-1' ),
            array( 'id' => 2, 'title' => 'Section 2', 'slug' => 'section-2' ),
            array( 'id' => 3, 'title' => 'Section 3', 'slug' => 'section-3' )
        );

        return $sections;
    }

    public function get_customization_directory(): string
    {
        return 'customizations';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function page_store(): JsonResponse
    {
        $this->validate(request(), [
            'title'     => 'required',
            'slug'  => 'required',
        ]);

        $data = request()->all();

        $page_title = array_key_exists( 'title', $data ) ? $data[ 'title' ] : null;

        $page_slug = array_key_exists( 'slug', $data ) ? $data[ 'slug' ] : null;

        $store_data = array(
            'title' => $page_title,
            'slug' => $page_slug,
        );

        $if_exist = CustomizationPages::where( 'slug', $page_slug )->first();

        if ( $if_exist ) {

            CustomizationPages::where( 'slug', $page_slug )->update( $store_data );

        } else {

            CustomizationPages::create( $store_data );
        }

        return new JsonResponse([
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
        $this->validate(request(), [
            'page_slug'     => 'required',
            'title'     => 'required',
            'slug'  => 'required',
        ]);

        $data = request()->all();

        $page_slug = array_key_exists( 'page_slug', $data ) ? $data[ 'page_slug' ] : null;

        $section_title = array_key_exists( 'title', $data ) ? $data[ 'title' ] : null;

        $section_slug = array_key_exists( 'slug', $data ) ? $data[ 'slug' ] : null;

        $store_data = array(
            'page_slug' => $page_slug,
            'title' => $section_title,
            'slug' => $section_slug,
        );

        $if_exist = CustomizationSections::where( 'page_slug', $page_slug )->where( 'slug', $section_slug )->first();

        if ( $if_exist ) {

            CustomizationSections::where( 'page_slug', $page_slug )->where( 'slug', $section_slug )->update( $store_data );

        } else {

            CustomizationSections::create( $store_data );
        }

        return new JsonResponse([
            'message' => 'Save Customization Section Successfully',
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

        $file_field_keys = array( 'file_upload', 'multiple_file_upload' );

        if ( $file_field_keys && count( $file_field_keys ) > 0 ) {

            foreach ( $file_field_keys as $file_field_key ) {

                $file_empty_flag = true;

                if ( array_key_exists( $file_field_key, $data ) ) {

                    $file_field = request()->file( $file_field_key );

                    if ( $file_field && request()->hasFile( $file_field_key ) ) {

                        $old_files = $this->get_customization_specific_field_data( $page_slug, $section_slug, $file_field_key );

                        if ( $old_files && isset( $old_files ) && !empty( $old_files ) && !is_null( $old_files ) ) {

                            if ( is_array( $old_files ) && count( $old_files ) > 0 ) {

                                foreach ( $old_files as $old_file ) {

                                    Storage::delete( $old_file );

                                }

                            } else {

                                Storage::delete( $old_files );

                            }

                        }
                        
                        if ( is_array( $file_field ) && count( $file_field ) > 0 ) {

                            foreach ( $file_field as $file ) {

                                $path = $this->upload_media( $file );

                                if ( $path && isset( $path ) && !empty( $path ) && !is_null( $path ) ) {
                                    
                                    $field_details[ $file_field_key ][] = $path;

                                    $file_empty_flag = false;

                                }

                            }

                        } else {

                            $path = $this->upload_media( $file_field );

                            if ( $path && isset( $path ) && !empty( $path ) && !is_null( $path ) ) {

                                $field_details[ $file_field_key ] = $path;

                                $file_empty_flag = false;

                            }

                        }

                    }

                }

                if ( $file_empty_flag == true ) {
                    
                    $db_data = $this->get_customization_specific_field_data( $page_slug, $section_slug, $file_field_key );

                    if ( $db_data && !empty( $db_data ) && !is_null( $db_data ) ) {

                        $field_details[ $file_field_key ] = $db_data;

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

    public function upload_media( $file )
    {
        $path = '';

        if ( $file instanceof UploadedFile ) {

            if ( Str::contains( $file->getMimeType(), 'image' ) ) {

                $manager = new ImageManager();

                $image = $manager->make( $file )->encode( 'webp' );

                $path = $this->get_customization_directory() . '/' . Str::random( 40 ) . '.webp';

                Storage::put( $path, $image );

            } else {

                $path = $file->store( $this->get_customization_directory() );

            }

        }

        return $path;

    }

    public function get_customization_specific_field_data( $page_slug, $section_slug, $field_key )
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

        }

        return $field_val;
    }

}