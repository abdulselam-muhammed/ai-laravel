<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;
    public $fillable=[
        'affiliate_name',
        'url',
        'User_id',
        'visitors_ip'
    ];

    public function User(){
        return $this->belongsTo(User::class,'User_id');
    }
}
