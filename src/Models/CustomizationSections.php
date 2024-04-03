<?php

namespace Webbycrown\Customization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webbycrown\Customization\Contracts\CustomizationPages as CustomizationPagesContract;

class CustomizationSections extends Model implements CustomizationPagesContract
{
    use HasFactory;

    protected $table = 'customization_sections';

    protected $fillable = [
        'page_slug',
        'title',
        'slug',
        'created_at',
        'updated_at',
    ];
}