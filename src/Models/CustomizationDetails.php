<?php

namespace Webbycrown\Customization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webbycrown\Customization\Contracts\CustomizationDetails as CustomizationDetailsContract;

class CustomizationDetails extends Model implements CustomizationDetailsContract
{
    use HasFactory;

    protected $table = 'customization_details';

    protected $fillable = [
        'page_slug',
        'section_slug',
        'field_details',
        'created_at',
        'updated_at',
    ];
}