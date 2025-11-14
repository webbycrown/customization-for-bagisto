<?php

/**
 * Namespace for customizations related to datagrids within the Webbycrown application.
 * This namespace organizes code related to manipulating and displaying data in tabular format.
 */
namespace Webbycrown\Customization\Datagrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webbycrown\Customization\Helpers\CustomizationHelpers;

/**
 * CustomizationSettingDataGrid extends the functionality of DataGrid to provide specialized features
 * tailored for customization settings display and management.
 */
class CustomizationSettingDataGrid extends DataGrid
{
    /**
     * Set index columns, ex: id.
     *
     * @var int
     */
    protected $index = 'id';

    /**
     * Default sort order of datagrid.
     *
     * @var string
     */
    protected $sortOrder = 'asc';

    /**
     * Locale.
     *
     * @var string
     */
    protected $locale = 'all';

    /**
     * Channel.
     *
     * @var string
     */
    protected $channel = 'all';

    /**
     * Contains the keys for which extra filters to render.
     *
     * @var string[]
     */
    protected $extraFilters = [
        'channels',
        'locales',
    ];

    /**
     * Prepares a query builder for database operations.
     * 
     * This method initializes or configures a query builder object
     * for performing database operations. It may set up initial conditions,
     * select columns, apply filters, or perform other necessary setup steps.
     * 
     * @return QueryBuilder The prepared query builder object.
     */
    public function prepareQueryBuilder()
    {
        $page_slug = request()->route('slug1');

        $section_slug = request()->route('slug2');

        $setting_id = request()->route('id') ?? 0;

        if ( (int)$setting_id > 0 ) {
            
            $queryBuilder = DB::table('customization_settings')
            ->where( 'page_slug', $page_slug )
            ->where( 'section_slug', $section_slug )
            ->where( 'parent_id', $setting_id )
            ->where( 'setting_type', 'repeater' )
            ->select('customization_settings.*');

        } else {

        	$queryBuilder = DB::table('customization_settings')
            ->where( 'page_slug', $page_slug )
            ->where( 'section_slug', $section_slug )
            ->where( 'parent_id', $setting_id )
            ->select('customization_settings.*');

        }

    	return $queryBuilder;
    }

    /**
     * Prepares columns for data processing.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => 'Title',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return CustomizationHelpers::get_string_with_breack_with_space( $row->title, 20 );
            }
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return CustomizationHelpers::get_string_with_breack_without_space( $row->name, 20 );
            }
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => 'Type',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index' => 'required',
            'label' => 'Required',
            'type' => 'integer',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return ( (int)$row->required == 1 ) ? 'True' : 'False';
            }
        ]);

        $this->addColumn([
            'index' => 'multiple',
            'label' => 'Multiple',
            'type' => 'integer',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return ( (int)$row->multiple == 1 ) ? 'True' : 'False';
            }
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Visible in Section',
            'type' => 'integer',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return ( (int)$row->status == 1 ) ? 'Yes' : 'No';
            }
        ]);

    }

    /**
     * This function prepares actions.
     * It currently lacks implementation details and requires further development.
     */
    public function prepareActions()
    {
    	$this->addAction([
            'index' => 'setting_edit',
            'icon' => 'icon-edit',
            'title' => 'Edit',
            'method' => 'GET',
            'url'    => function ($row) {
                return route( 'wc_customization.section.setting.edit', $row->id );
            },
        ]);

        $this->addAction([
            'index' => 'repeater_section_settings',
            'icon' => 'icon-settings',
            'title' => 'Section setting',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('wc_customization.admin.customization.sections.setting.repeater', [ $row->page_slug, $row->section_slug, $row->id ]);
            },
        ]);

        $this->addAction([
            'index' => 'setting_delete',
            'icon'   => 'icon-delete',
            'title'  => 'Delete',
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('wc_customization.section.setting.delete', $row->id);
            },
        ]);

    }

    /**
     * Placeholder function for preparing mass actions.
     * This function is intended to handle preparations required for mass actions, 
     * but currently lacks implementation. Further development is needed.
     */
    public function prepareMassActions()
    {
    	//
    }
}