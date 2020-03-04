<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ReminderPasswordEmail;
use Illuminate\Support\Facades\Mail;
use DB;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    public function reminder_password(Request $request){

	 	DB::BeginTransaction();
	 	try {
	 		$number = mt_rand(100000, 999999);
	 		$get_user = DB::table('m_users')->where('us_email',$request->email)->first();	
	 		if ($get_user == null) {
	 			return response()->json("emailnotavailable");
	 		}
	 		$username = substr($request->email, 0, strpos($request->email, '@'));
	 		$name = $get_user->us_name;
	 		$data =array('email'=>$request->email, 'name' => $name, 'password' => $number);
	 		$email  = $request->email;
	 		Mail::send('reminder-password', $data, function($message) use ($email, $name) {
	 			$message->from('customer@bisniskita.com','Customer Services BisnisKita - Tudulis');
                $message->to($email, $name)
                ->subject('Reset Password Akun');

                });
	 		DB::table('m_users')->where('us_email',$request->email)->update([
	 				'us_password' => bcrypt($number)
	 		]);
	 		DB::Commit();
			return response()->json([
				'status' => 'success',
			]);
	 	} catch (Exception $e) {
	 		return $e;
	 		db::rollback();
	 	}
    }
}
