<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSettings extends Model
{
    use HasFactory;
    protected $table= 'settings';
    protected $fillable = [
        'id',
        'logo',
        'website_name',
        'fav_icon',
        'phone',
        'email',
        'address',
        'sub_heading',
        'copy_right',
        'title',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'header_script',
        'footer_script',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'android_app',
        'ios_app',
        'currency',
        'latitude',
        'longitude',
        'created_at',
        'updated_at'
    ];
    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('assets/general-settings-image/'. $value);
        } else {
            return asset('assets/general-settings-image/default-image.png');
        }
    }
    public function getLogoAttribute($value)
    {
        if ($value) {
            return asset('assets/general-settings-image/'. $value);
        } else {
            return asset('assets/general-settings-image/default-image.png');
        }
    }
    public function getFavIconAttribute($value)
    {
        if ($value) {
            return asset('assets/general-settings-image/'. $value);
        } else {
            return asset('assets/general-settings-image/default-image.png');
        }
    }
}
