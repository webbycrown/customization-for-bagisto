<?php

namespace Webbycrown\Customization\Datagrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

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

    public function prepareQueryBuilder()
    {
    	$queryBuilder = DB::table('customization_pages')->select('customization_pages.*');

    	return $queryBuilder;
    }

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
        ]);

        $this->addColumn([
            'index' => 'slug',
            'label' => 'Slug',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

    }

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

    	// $this->addAction([
    	// 	'title' => 'edit',
    	// 	'method' => 'GET',
    	// 	'icon' => 'icon-edit',
    	// 	'route' => 'admin.blog.edit',
    	// 	'url'    => function ($row) {
    	// 		return route('admin.blog.edit', $row->id);
    	// 	},
    	// ]);

    	// $this->addAction([
    	// 	'title' => 'delete',
    	// 	'method' => 'POST',
    	// 	'icon' => 'icon-delete',
    	// 	'route' => 'admin.blog.delete',
    	// 	'url'    => function ($row) {
    	// 		return route('admin.blog.delete', $row->id);
    	// 	},
    	// ]);
    }

    public function prepareMassActions()
    {
    	// $this->addMassAction([
    	// 	'type'   => 'delete',
    	// 	'label'  => trans('admin::app.datagrid.delete'),
    	// 	'title'  => 'Delete',
    	// 	'action' => route('admin.blog.massdelete'),
    	// 	'url' => route('admin.blog.massdelete'),
    	// 	'method' => 'POST',
    	// ]);
    }
}