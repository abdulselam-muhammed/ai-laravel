<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Team extends Model
{
    use HasFactory;
    public $fillable=[
        'team_name',
        'members_limit',
        'url',
        'team_role',
        'owner_id'
    ];

    public function User(){
        return $this->belongsTo(User::class,'owner_id');
    }
}
