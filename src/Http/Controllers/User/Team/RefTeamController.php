<?php

namespace App\Http\Controllers\User\Team;
use App\Http\Controllers\Controller;
use App\Models\Link_Team;
use App\Models\User;
use Illuminate\Http\Request;

class RefTeamController extends Controller
{
    public function ref_team_for_register($link_code , $team_name){
        $ref_url = 'http://localhost:8000/'.$link_code.'/'.$team_name.'-team';

        // $member_user = User::where('url',$ref_url)->get();
        // $count_members = count($member_user);

        // return $member_user;

        $url_check = Link_Team::where('url',$ref_url)->first();

        if(isset($url_check)){
            return view('auth.register');

        }else{
            return 'this url is not found !';
        }
    }
}
