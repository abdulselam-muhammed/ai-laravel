<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SearchResultItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'search_result_id',
        'output',
        'react',
        'is_favorite',
        'total_word',
        'total_characters',
    ];
}
