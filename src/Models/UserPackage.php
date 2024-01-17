<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id','package_id','name','generate_characters','access_use_cases','write_languages', 'write_languageses','access_tones', 'access_toneses','generate_images','plagiarism_checker','access_community','custom_use_cases','dedicated_account','support','monthly_price','yearly_price','device_limit','start_date','end_date','order_id','status','is_trail'];
    
    protected $casts = [
        'access_use_cases' => 'array',
        'write_languageses' => 'array',
        'access_toneses' => 'array',
    ];

    public function getAccessUseCasesesAttribute()
    {
        if(in_array("-1", $this->access_use_cases)){
            return SubCategory::where('status', ACTIVE)->select('id')->pluck('id')->toArray();
        }

        return SubCategory::whereIn('id', $this->access_use_cases)->where('status', ACTIVE)->select('id')->pluck('id')->toArray();
    }
}
