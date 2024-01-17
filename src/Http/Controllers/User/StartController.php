<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use App\Models\Document;
use App\Models\Folder;
use Carbon\Carbon;
use App\Models\FileManager;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StartController extends Controller
{
    public function home(){
        $documents = Document::where('User_id',Auth::id())->where('Folder_id',null)->get();
        $folders  = Folder::where('User_id',Auth::id())->where('Folder_id',null)->get();
        return view('user.content.Star.home',compact('documents','folders'));
    }

    public function favorite_page(){
        $documents = Document::where('User_id',Auth::id())->where('favorite',1)->get();
        $folders   = Folder::where('User_id',Auth::id())->where('favorite',1)->get();
        $all_folders = Folder::all();
        return view('user.content.Star.favorites',compact('documents','folders','all_folders'));
    }
}
