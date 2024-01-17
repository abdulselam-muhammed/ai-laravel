<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactStoreRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\Visitor;
use Auth;
class Referrals extends Controller
{
  
    public function referral($name , $code){
        $url = 'https://aikalem.com/ref';
        $link_full = $url.'/'.$name.'/'.$code;
        $check_user = Link::where('link',$link_full)->first();
        $gude='';
        $client_ip =request()->ip();
        $all_ip=array();
        $all_clinet_ip=array();
        $link='';
        $ips=array();
        
        
        $msg='';
        $visitors = array();
        $total = array();
        $link_s = Link::where('link',$link_full)->get();
        
        $check_ip = Visitor::where('link',$link_full)->get();
        if($check_user){
            foreach($check_ip as $check){
                if($check){
                    $all_ip = explode('|',$check->visitors);
                } 
            }
            $i=0;
            foreach($all_ip as $ip){
                if($ip == $client_ip)
                {
                    $gude = $ip;
                    $gude_number=$i;
                }
                $all_clinet_ip[]=$ip;
                $i++;
            }
            $all_clinet_ip[]= $client_ip;
            if(isset($gude_number)){
            unset($all_clinet_ip[$gude_number]);
            }
            if(count($check_ip)!=0){
                Visitor::where('link',$link_full)->update(array(
                    'visitors'=>implode('|',$all_clinet_ip)
                ));
                 foreach($link_s as $link){
                    $visitors = Visitor::where('link',$link->link)->get();
                }
                
                foreach($visitors as $visitor){
                    $total = explode('|',$visitor->visitors);
                }
                $count = count($total);
                $amount = $count/3;

                Link::where('link',$link_full)->update(array(
                    'amount'=>$amount
                ));
                
                return redirect('/');
            }else{
                Visitor::create([
                    'link'=>$link_full,
                    'visitors'=>implode('|',$all_clinet_ip)
                ]);
                 foreach($link_s as $link){
                    $visitors = Visitor::where('link',$link->link)->get();
                }
                
                foreach($visitors as $visitor){
                    $total = explode('|',$visitor->visitors);
                }
                $count = count($total);
                $amount = $count/3;

                Link::where('link',$link_full)->update(array(
                    'amount'=>$amount
                ));
                
                return redirect('/');
            }
        } 
    }


}

?>