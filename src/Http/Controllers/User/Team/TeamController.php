<?php

namespace App\Http\Controllers\User\Team;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Link_Team;
use App\Models\Team;
use App\Models\User;
use Auth;
class TeamController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function form(){
        return view('Team.form');
    }

    public function create(Request $request){
       
        
        $link_code = uniqid('', true);
        $link_code = str_replace('.', '', $link_code);
        $link_code = substr($link_code, 0, 10);

        $ref_url = 'http://localhost:8000/'.$link_code.'/'.$request->team_name.'-team';
        if(Auth::user()->team_status != 1){
            Team::create([
                'team_name' => $request->team_name,
                'members_limit' => "1-35",
                'url'=>$ref_url,
                'owner_id' => Auth::id()
            ]);

            Link_Team::create([
                'url'=>$ref_url
            ]);
            user::where('id',Auth::id())->update(array(
                'team_status'=>1,
                'team_role'=>1
            ));
        
            return redirect::back()->withErrors(['msg'=>'Seccesfully']);
        }
        else{
            return redirect::back()->withErrors(['msg'=>'you are alredy in the team in this app']);
        }
    }
    public function result_team(){
        return view('user.result_team');
    }
    
    public function delete_member($id){
        User::where('id',$id)->update(array(
            'url'=>null,
            'team_status'=>null,
            'team_role'=>null
        ));
        return redirect::back()->withErrors(['msg'=>'deleted Seccesfully']);
    }



    public function update_role_member($member_id,$role_value){
        if($role_value == 1){
            User::where('id',$member_id)->update(array(
                'team_role'=>1
            ));
            return redirect::back()->withErrors(['msg'=>'updated seccessfully']);
        }elseif($role_value == 0){
            User::where('id',$member_id)->update(array(
                'team_role'=>null
            ));
            return redirect::back()->withErrors(['msg'=>'updated seccessfully']);
        }
    }

    public function Team_logout($User_id){
        User::where('id',$User_id)->update(array(
            'url'=>null,
            'team_status'=>null,
            'team_role'=>null
        ));
        return redirect::back()->withErrors(['msg'=>'Seccessfully']);
    }
}
