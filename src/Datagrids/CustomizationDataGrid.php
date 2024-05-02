<?php

/**
 * Namespace declaration for custom datagrid functionality in the Webbycrown project.
 * Contains classes and utilities for customizing and working with datagrids.
 */
namespace Webbycrown\Customization\Datagrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webbycrown\Customization\Helpers\CustomizationHelpers;

/**
 * CustomizationDataGrid: 
 * 
 * This class extends the functionality of the DataGrid class to provide custom features
 * or behavior specific to your application's needs. It serves as a specialized version 
 * of the standard DataGrid, potentially offering additional functionality or 
 * customization options.
 */
class CustomizationDataGrid extends DataGrid
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
    protected $sortOrder = 'desc';

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
    	$queryBuilder = DB::table('customization_pages')->select('customization_pages.*');

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
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => 'Title',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
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
            'filterable' => true,
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
    		'title' => 'edit',
    		'method' => 'GET',
    		'icon' => 'icon-exit',
    		'url'    => function ($row) {
    			return route('wc_customization.admin.customization.pages.index', $row->slug);
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