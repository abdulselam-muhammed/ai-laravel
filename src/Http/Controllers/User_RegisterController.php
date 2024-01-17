<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Redirect;

class User_RegisterController extends Controller
{

    public $guide_status = false;

    public function check_code(Request $request){
        $auth_id = Auth::id();
        $code = $request->code;
        $user = User::where('id',$auth_id)->first();

        if($user->code == $code){
            User::where('id',$auth_id)->update(array(
                'register_status'=>1,
                'code'=>null
            ));
            return redirect(route('user.dashboard'))->with('success', __('Login Successful'));
        }
        else{
            return redirect::back()->with(['msg'=>'Code is failed']);
        }
        
    }

    public function check_code_email(Request $request,$visitor_email){
        $code = $request->code;
        
        $user = User::where('email',$visitor_email)->first();


        
        if($user->code == $code){
            User::where('email',$visitor_email)->update(array(
                'register_status'=>1,
                'code'=>null
            ));

            auth()->login($user);
            return redirect(route('user.dashboard'))->with('success', __('Login Successful'));
        }
        else{
            return redirect::back()->with(['msg'=>'Code is failed']);
        }
    }

    public function complate_register(){
    return view('complate_register');
    }

    public function complate_code(Request $request){

        $visitor_email =$request->email;
        $user = User::where('email',$visitor_email)->first();
            if(isset($user->email)){
                if($user->register_status == 1){
                    return redirect::back()->withErrors(['msg'=>"you'r register alredy completed !"]);
                }
                // else{
                //     User::where('email',$visitor_email)->update(array(
                //         'code'=>null
                //     ));
                // }
                        // // ------------------strat for mail--------------------------------------------------------
                        //         $used_chars = array();
                        //         $random_string = '';
                        //         $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
                        //         $chars_length = strlen($chars);

                        //         while (strlen($random_string) < 8) {
                        //         $random_number = random_int(0, $chars_length - 1);
                        //         $random_char = $chars[$random_number];
                        //             if (!in_array($random_char, $used_chars)) {
                        //                 $random_string .= $random_char;
                        //                 array_push($used_chars, $random_char);
                        //             }
                        //         }
                                
                        //         $details = [
                        //             'title' => 'WeboMax AIkalem',
                        //             'body' => 'For Complated Register Section plasce Add Your Code',
                        //             'code' => $random_string
                        //         ];

                        //         \Mail::to($visitor_email)->send(new \App\Mail\Mail_register($details));
                        // // -------------------finish for mail------------------------------------------------------
                // User::where('email',$visitor_email)->update(array(
                //     'code'=>$random_string
                // ));
                $this->guide =true;
                $guide = $this->guide ;
                return view('complate_register',compact('guide','visitor_email') );

            }else{
                return redirect::back()->withErrors(['msg'=>'This email is not found ! :/']);
            }
    }

}
