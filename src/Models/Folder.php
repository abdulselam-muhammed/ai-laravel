<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Document;
class Folder extends Model
{
    use HasFactory;

    public $fillable=[
        'title','User_id','Folder_id','Father_id','favorite'
    ];

    public function User(){
        return $this->belongsTo(User::class,'User_id');
    }
    public function Documents(){
        return $this->hasMany(Document::class);
    }
}
