<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Team;
use App\Models\Link_Team;
use Auth;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::code_site;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    protected $limit = 'NOT_FULL';

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'first_name.required' => 'This field is required.',
            'last_name.required' => 'This field is required.',
            'email.required' => 'This field is required.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        DB::beginTransaction();
        $url = session()->get('url');
        $rgister_af_url = session()->get('register_af_url');

        if(isset($rgister_af_url) && $rgister_af_url == 'http://localhost:8000/register'){
            
            $rgister_af_url=null;
            
        }

        if(isset($url) && $url == 'http://localhost:8000/register'){
            $url = null;
        }
        $member_user = User::where('url',$url)->get();
        $count_members = count($member_user);

        $team = Team::where('url',$url)->first();
        if(isset($team)){
                $members_limit =  $team->members_limit ;
                $limit = explode('-',$members_limit);
        }
                // $limit[1]; => for example  100-1000  so $limit[1] it 1000
                if(isset($limit) && $count_members ==  $limit[1]){
                    $this->limit = 'FULL';
                }

        
        //------------------strat for mail--------------------------------------------------------
            $used_chars = array();
            $random_string = '';
            $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $chars_length = strlen($chars);

            while (strlen($random_string) < 8) {
            $random_number = random_int(0, $chars_length - 1);
            $random_char = $chars[$random_number];
                if (!in_array($random_char, $used_chars)) {
                    $random_string .= $random_char;
                    array_push($used_chars, $random_char);
                }
            }
            
            $details = [
                'title' => 'WeboMax Kalem.Ai',
                'body' => 'For Complated Register Section plasce Add Your Code',
                'code' => $random_string
            ];

            // \Mail::to($data['email'])->send(new \App\Mail\Mail_register($details));
            // \Mail::to($data['email'])->send(new \App\Mail\Mail_register($details));

        //-------------------finish for mail------------------------------------------------------


        try {
            if($this->limit == 'NOT_FULL'){
            $user =  User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => USER_ROLE_USER,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'code'=>$random_string,
                'register_status'=>0,
                'register_af_url'=>$rgister_af_url,
                'url' => $url
            ]);
            $user_members = User::where('url',$url)->get();
            
            $members_id=array();
            
            if(count($user_members)!=0){
                foreach($user_members as $user_member){
                    $members_id[] = $user_member->id;
                }
            }
            Link_Team::where('url',$url)->update(array(
                'members_id'=>implode('|',$members_id)
            ));
            if($user->url != null){
                User::where('id',$user->id)->update(array(
                    'team_status'=>1
                ));
            }

            }else{

                $user =  User::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'role' => USER_ROLE_USER,
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'code'=>$random_string,
                    'register_status'=>0,
                    'register_af_url'=>$rgister_af_url,
                    'url' => 'limit FULL from :'.$url
                ]);
            }


            $duration = (int)getOption('trail_duration', 1);

            $defaultPackage = Package::where(['is_trail' => ACTIVE])->first();
            setUserPackage($user->id, $defaultPackage, $duration);
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back();
        }
    }
}


