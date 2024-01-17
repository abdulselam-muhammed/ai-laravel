<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link_team extends Model
{
    use HasFactory;
    public $fillable=[
        'url',
        'members_id'
    ];
}
