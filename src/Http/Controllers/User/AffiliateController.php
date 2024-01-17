<?php

namespace App\Http\Controllers\User;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\User;
use Auth;

class AffiliateController extends Controller
{
    public function form(){
        return view('Affiliate.form');
    }

    public function create(Request $request){
        
        
        if(isset($_POST['affiliate_name'])){
            $affiliate_name = $_POST['affiliate_name'];
            $affiliate_code = uniqid('', true);
            $affiliate_code = str_replace('.', '', $affiliate_code);
            $affiliate_code = substr($affiliate_code, 0, 6);

            $url = 'http://localhost:8000/'.$affiliate_code.'/-ref-/'.$affiliate_name;

             if(Auth::user()->affiliate_status !=1){
                Link::create([
                    'affiliate_name'=>$affiliate_name,
                    'url'=>$url,
                    'User_id'=>Auth::id()
                ]);
            }else{

                return 'you are alredy have affiliate !';
            }

            User::where('id',Auth::id())->update(array(
                'affiliate_status'=>1
            ));

            if (auth()->user()->Link->visitors_ip) {
                $visitors = explode('|', auth()->user()->Link->visitors_ip);
                $all_ip = array();
                foreach ($visitors as $visitor) {
                    $all_ip[] = trim($visitor); //trim  for delete emty area in before and after elemenet
                }
                // $visitor_count = count(array_filter($all_ip));
                $visitor_count = count(array_filter($all_ip)); //array_filter()  لازالة العناصر الفارغة وتحديث القيمة
            } else {
                $visitor_count = 0;
            }
            // $registered_user = App\Models\User::where('register_af_url',auth()->user()->Link->url)->get();
            $registered_user = User::where('register_af_url', auth()->user()->Link->url)->get();
            $count_registered_user = count($registered_user);
            $Auth_url=auth()->user()->Link->url;

            $responseData = [
                'Auth_url' => $Auth_url,
                'visitor_count' => $visitor_count,
                'count_registered_user' => $count_registered_user
            ];
            // change from array to json
            $jsonData = json_encode($responseData);

            return response($jsonData)->header('Content-Type', 'application/json');

        }else{
            return 'no';
        }
    }

    public function testAffiliate(){
        return view('user.testaffiliate');
    }


}
