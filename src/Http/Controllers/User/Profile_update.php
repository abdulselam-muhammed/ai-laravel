<?php

namespace App\Http\Controllers\User;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class Profile_update extends Controller
{
    public function profile_update(Request $request){

        $profile_image= $request->file('profile_image');
        $avatar = $request->file('avatar');
        if($profile_image){
            $image_name = rand(100,1000);
            $ext = strtolower($profile_image->getClientOriginalExtension());
            $image_full_name = $image_name.'.'.$ext;
            $path = 'public/profile_image/';
            $profile_image->move($path , $image_full_name);
            $profile_image_url = $path.$image_full_name;
            User::where('id',Auth::id())->update(array(
                'profile_image'=>$profile_image_url,
            ));
        }
        if($avatar){
            $image_name = rand(100,1000);
            $ext = strtolower($avatar->getClientOriginalExtension());
            $image_full_name = $image_name.'.'.$ext;
            $path = 'public/profile_avatar/';
            $avatar->move($path , $image_full_name);
            $avatar_image_url = $path.$image_full_name;
            User::where('id',Auth::id())->update(array(
                'avatar'=>$avatar_image_url
            ));
        }
        User::where('id',Auth::id())->update(array(
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'country'=>$request->country,
            'address'=>$request->address,
            'contact_number'=>$request->phone,
            'city'=>$request->city,
            'postcode'=>$request->postcode,
            'About'=>$request->about,
        ));
        
        return redirect::back()->withErorrs(['msg'=>'updated Seccessfully']);
    }
}
