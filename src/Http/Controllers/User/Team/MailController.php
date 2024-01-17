<?php

namespace App\Http\Controllers\User\Team;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
class MailController extends Controller
{
    public function team_invit(Request $request)
    {
        $sender_email = session()->get('sender_email');
        $link = session()->get('link');

        $check_email = User::where('email',$request->email)->first();
        if(isset($check_email)){
            return 'this email is exist alredy';
        }else{
            $details =[
                'title' => 'Hello from Kalem.AI',
                'body' => "$sender_email sent you an invite to join the team!",
                'link' => "The link to join the team is: $link",
            ];
            \Mail::to($request->email)->send(new \App\Mail\Team_invit($details));
            return 'mail sended';
        }
    }
    
}