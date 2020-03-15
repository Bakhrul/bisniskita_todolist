<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class TokenController extends Controller
{
    public function sendNotif($title,$body,$id){

      try{
        
        // $request->validate([
        //     'id'=>'required|integer'
        // ]);

        $getUser = DB::table('d_user_token')
                            ->where('ut_user',$id)
                            ->first();

        $getUserNewestLoginOnApp = DB::table('d_user_token')
                                      ->where('ut_token',$getUser->ut_token)
                                      ->orderBy('ut_updated','DESC')
                                      ->first();
        if($getUser->ut_user == $getUserNewestLoginOnApp->ut_user){

        $url = "https://fcm.googleapis.com/fcm/send";

        $serverKey = 'AAAAr6wV1sE:APA91bHw3h_ufljuWG1DVXTI-kZ9s7BvDQgt9EtF4CVzaqsuuJ33YGTfnEOEOgaf21RuU1Kria1WncWICosphS7yWkm_G3IrigZ-tjHCbSgFPS5mzzbygeaZHOUpstRy71Lm91m_ae_U';

        $notification = array(
            'title' =>$title , 
            'body' => $body,
            // 'image' => 'https://cdn.dribbble.com/users/3294167/screenshots/6852538/form_1.jpg',
            "click_action"=>"FLUTTER_NOTIFICATION_CLICK",
            'sound' => 'default', 
            'badge' => '1');
        $arrayToSend = array(
            'to' => $getUser->ut_token, 
            'notification' => $notification,
            'priority'=>'high');

        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        //Send the request
        $response = curl_exec($ch);
        //Close request
        if ($response === FALSE) {
            return response(['error'=>"error","data"=>curl_error($ch)],401);
        }

        curl_close($ch);

      }

        return true;


      } catch(\Exception $e) {

        return response(['error'=>"error","data"=>$e->getMessage()],401);

      }

        
    }

    public function getToken(Request $request){


      try{

        $request->validate([
            'id'=>'required|integer'
        ]);

        $fcm = DB::table('d_user_token');

        $getUser = $fcm->where('ut_user',$request->id)
                            ->first();

        return response(['success'=>'success','data'=>$getUser]);

      } catch(\Exception $e){

        return response(['error'=>'error','data'=>$e->getMessage()]);

      }

        
        
    }

    public function updateToken(Request $request){

    
    try{

        $request->validate([
            "id"=>"required|integer",
            "token"=>"required"
          ]);
  
          $fcm = DB::table('d_user_token');
  
          $count = $fcm->where('ut_user',$request->id)->count();
  
          if($count == 1){
              $fcm = DB::table('d_user_token')->where('ut_user',$request->id)
                  ->update([
                      "ut_token" => $request->token,
                      "ut_updated" => Carbon::now()
                  ]);
          }else{
              $fcm->insert([
                      "ut_user"     => $request->id,
                      "ut_token"   => $request->token,
                      "ut_created"  => Carbon::now(),
                      "ut_updated"  => Carbon::now()
                  ]);
          }

        return response(["success"=>"success","data"=>$request->token],200);

    }catch(\Exception $e){

        return response(["error"=>"error","data"=>$e->getMessage()],401);

    }

    }
}
