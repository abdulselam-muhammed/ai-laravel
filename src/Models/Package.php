<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'access_use_cases' => 'array'
    ];

    public function getAccessUseCasesesAttribute()
    {
        if(in_array("-1", $this->access_use_cases)){
            return SubCategory::where('status', ACTIVE)->select('id')->pluck('id')->toArray();
        }

        return SubCategory::whereIn('id', $this->access_use_cases)->where('status', ACTIVE)->select('id')->pluck('id')->toArray();
    }

}
