<?php
  
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GoogleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
        
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleGoogleCallback()
    {
        try {
      
            $user = Socialite::driver('google')->user();
       
            $finduser = User::where('google_id', $user->id)->first();
            $befor_email = User::where('google_id', null)->where('email',$user->email)->first();
            if($befor_email){
                User::where('email',$user->email)->update(array(
                    'google_id'=> $user->id
                ));

                $get_this_user = User::where('google_id', $user->id)->first();

                Auth::login($get_this_user);
      
                return redirect()->intended('user/dashboard');
            }
            elseif($finduser){
       
                Auth::login($finduser);
      
                return redirect()->intended('user/dashboard');
       
            }else{
                $newUser = User::create([
                    'first_name' => $user->name,
                    'last_name'=>'',
                    'email' => $user->email,
                    'google_id'=> $user->id,
                    'register_status'=>1,
                    'password' => encrypt('123456dummy')
                ]);
      
                Auth::login($newUser);
      
                return redirect()->intended('user/dashboard');
            }
      
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}