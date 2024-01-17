<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Fodler;
Use App\Models\User;
class Document extends Model
{
    use HasFactory;    

    public $fillable=[
        'title','content','Folder_id','Father_id','User_id','favorite'
    ];

    public function Folder(){
        return $this->belongsTo(Folder::class,'Folder_id');
    }

    public function User(){
        return $this->belongsTo(User::class,'User_id');
    }
}
