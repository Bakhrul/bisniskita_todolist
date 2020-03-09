<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use App\User;
use Hash;
use Carbon\Carbon;
use File;

class UserController extends Controller
{
      public function user()
    {
        $user = DB::table('m_users')
            ->where('us_id', Auth::user()->us_id)
            ->first();

        return response()->json($user);
    }

    public function update(Request $request)
    {
    	DB::BeginTransaction();
    	try {
    		if($request->ispassword == 'Y'){

	        if(Hash::check($request->oldpassword,Auth::user()->us_password)){

	            if($request->newpassword == $request->confirmpassword){

	                $data = User::find(Auth::user()->us_id);
			    	$data->us_password = bcrypt($request->newpassword);
			    	$data->update();
			    	DB::commit();
	                return response(['status'=>'success','data'=>Auth::user()->us_image],200);

	            }else{

	                return response()->json([
	                    'status' => 'password baru tidak sama',
	                ]);
	            }
	        }else{
	            return response()->json([
	                'status' => 'password lama tidak sama',
	                'msg' => $request->oldpassword,
	            ]);
	        }

	    }else{
	    	$data = User::find(Auth::user()->us_id);
	    	$data->us_name = $request->name;
	    	$data->us_phone = $request->phone;
	    	$data->us_address = $request->address;
	    	$data->update();
	    	DB::commit();
	    	 return response(['status'=>'success','data'=>Auth::user()->us_image],200);
	    }
    	
    	} catch (Exception $e) {
    		DB::rollback();
    		return $e;
    	}
    }

     public function updateProfile(Request $request)
    {
        DB::BeginTransaction();
        try {
            $ext = pathinfo($request->pathname, PATHINFO_EXTENSION);
            $ext = str_replace("'", "", $ext);
            $image = $request->file64;
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);

            $imageName = date("ymdhis").'_'.Auth::user()->us_name.'.'.$ext;
            $path = storage_path(). '/image/profile/' ;

            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            \File::put($path . $imageName, base64_decode($image));
            
            $data = User::find(Auth::user()->us_id);
            $data->us_image = $imageName;
            $data->update();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $imageName
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

   public function register(Request $request){

    	if($request->type_platform == 'android'){
            $request = json_decode($request->data);
        }

        // return $request->all();
        DB::beginTransaction();

        try {
       	$emailAvailable = DB::table('m_users')->where('us_email',$request->email)->first();
        if($emailAvailable != null){
            if($emailAvailable->us_isactive >= "T"){
        	   return response()->json([
        		  'status' => 'emailnotavailable',
        	   ]);
            }elseif ($emailAvailable->us_isactive >= "F") {
                 DB::table('m_user')->where('us_email',$request->email)->update([
                'us_isactive'       => "T",
                'us_update_time'    => Carbon::now(),
                'password' => bcrypt($request->password),
            ]); 
            }

        }
        else{
        DB::table('m_users')->insert([
            'us_email'          => $request->email,
            'us_name'           => $request->namalengkap,
            'us_password'       => bcrypt($request->password),
            // 'us_isactive'       => "T",
            'us_created'    => Carbon::now(),
            'us_updated'    => Carbon::now(),
        ]);  

        }  
       	
        DB::commit();
        return response([
            'status' => 'success'
        ],200);
        
        
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
            return response()->json('status','error');
        }
    }

     public function destroyProfile(Request $request)
    {
        DB::BeginTransaction();
        try {
            $data = User::find(Auth::user()->us_id);
            unlink(storage_path('profile/'.Auth::user()->us_image));
            $data->us_image = NULL;
            $data->update();
            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return $th;
        }
    }

    public function detail_user($id){
        $User = DB::table('m_users')->where('us_id',$id)->first();

        return response()->json($User);
    }
}
