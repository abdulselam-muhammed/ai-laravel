<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Http;
class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $validateSerch=[
            'are you chatgpt',
            'you chatgpt',
            'are you chatgpt',
            'are you use chatgpt'
        ];
        $phraseToSearch = "you chatgpt";
        $search = $request->easy_input;
        if (in_array(strtolower($search), $validateSerch) || strpos(strtolower($search), $phraseToSearch) !== false) {
            $botMsg = 'I am an AI language model. My purpose is to assist and communicate with users in a conversational manner.';
        } else {
            $data = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer your-sk-key',
            ])
                ->timeout(60)
                ->post("https://api.openai.com/v1/chat/completions", [
                    "model" => "gpt-3.5-turbo",
                    'messages' => [ 
                        [
                            "role" => "user",
                            "content" => $search
                        ] 
                    ],
                    'temperature' => 0.5,
                    "max_tokens" => 200,
                    "top_p" => 1.0,
                    "frequency_penalty" => 0.52,
                    "presence_penalty" => 0.5,
                    "stop" => ["11."],
                ])
                ->json();
            
            $botMsg = json_decode(json_encode($data['choices'][0]['message']['content']), true);
        }
    

        $subscriptionExpired = false; // true يعني انتهت الباقة
    
        if (isset($data['usage']) && isset($data['usage']['total_tokens'])) {
            $totalTokens = $data['usage']['total_tokens'];
    
            if ($totalTokens >= 4000000) {
                // مشان نتحقق من انتهاء الباقة او لا 
                $subscriptionExpired = true;
            }
        }
    
        if ($subscriptionExpired) {
            $botMsg .= "aboelik bitti";
        }
    
        return response()->json(['message' => $botMsg]);
    }
    
}

?>
