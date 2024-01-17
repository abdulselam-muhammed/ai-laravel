<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Link;

class RefAffiliateController extends Controller
{
    public function ref_affiliate(Request $request, $affiliate_code , $affiliate_name){

        $url = 'http://localhost:8000/'.$affiliate_code.'/-ref-/'.$affiliate_name;
        $clint_ip = $request->getClientIp();
        $visitors_ip=array();
        $all_ip = Link::where('url',$url)->first();

        $check_ip = Link::where('url',$url)
                        ->where('visitors_ip', 'like', '%' . $clint_ip . '%')
                        ->first();
                        
        $visitors_ip = explode('|',$all_ip->visitors_ip);
        foreach($visitors_ip as $visitor_ip){
            if($clint_ip != $visitor_ip ){
                $visitors[]=$visitor_ip;
            }
        }
        if(!isset($check_ip)){
            $visitors[]=$clint_ip;   

            Link::where('url',$url)->update(array(
                'visitors_ip'=>implode('|',$visitors)
            ));
        } 
        return view('auth.register');
    }
}
