<?php

namespace Webbycrown\Customization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webbycrown\Customization\Contracts\CustomizationSettings as CustomizationSettingsContract;

class CustomizationSettings extends Model implements CustomizationSettingsContract
{
    use HasFactory;

    protected $table = 'customization_settings';

    protected $fillable = [
        'page_slug',
        'section_slug',
        'title',
        'name',
        'type',
        'required',
        'multiple',
        'status',
        'parent_id',
        'setting_type',
        'other_settings',
        'created_at',
        'updated_at',
    ];
}