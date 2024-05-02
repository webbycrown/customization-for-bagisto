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
 * Represents a customized data grid component specialized for a specific section.
 * Extends functionality from the DataGrid class.
 */
class CustomizationSectionDataGrid extends DataGrid
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

    	$queryBuilder = DB::table('customization_sections')->where( 'page_slug', $page_slug )->select('customization_sections.*');

    	return $queryBuilder;
    }

    /**
     * Prepares columns for data processing.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'title',
            'label' => 'Title',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return CustomizationHelpers::get_string_with_breack_with_space( $row->title, 35 );
            }
        ]);

        $this->addColumn([
            'index' => 'slug',
            'label' => 'Slug',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return CustomizationHelpers::get_string_with_breack_without_space( $row->slug, 35 );
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
            'index' => 'section_edit',
            'icon' => 'icon-edit',
            'title' => 'Edit',
            'method' => 'GET',
    		'url'    => function ($row) {
    			return route( 'wc_customization.section.edit', $row->id );
    		},
    	]);

        $this->addAction([
            'index' => 'section_exit',
            'icon' => 'icon-edit-save',
            'title' => 'Section',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('wc_customization.admin.customization.sections.index', [ $row->page_slug, $row->slug ]);
            },
        ]);

        $this->addAction([
            'index' => 'section_settings',
            'icon' => 'icon-settings',
            'title' => 'Section Section',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('wc_customization.admin.customization.sections.setting', [ $row->page_slug, $row->slug ]);
            },
        ]);

        $this->addAction([
            'index' => 'section_delete',
            'icon'   => 'icon-delete',
            'title'  => 'Delete',
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('wc_customization.section.delete', $row->id);
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
    }
}