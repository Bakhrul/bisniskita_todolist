<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Auth;
use DB;

class NotificationsController extends Controller
{
    public function get_notif(){
    	$notifikasi = DB::table('d_notifications_todolist')
    	->join('m_users as tablepengirim','nt_fromuser','tablepengirim.us_id')
    	->join('m_users as tablepenerima','nt_touser','tablepenerima.us_id')
    	->join('m_notifications','nt_notifications','n_id')
    	->leftJoin('d_project','nt_project','p_id')
    	->leftJoin('d_todolist','nt_todolist','tl_id')
    	->where('nt_touser',Auth::user()->us_id)
    	->select('nt_notifications','n_title','n_message','tablepenerima.us_name as namapenerima','tablepengirim.us_name as namapengirim','nt_status','nt_todolist','n_title','p_name','tl_title','nt_id')
    	->orderBy('nt_id','DESC')
    	->get();
    	DB::table('d_notifications_todolist')->where('nt_touser',Auth::user()->us_id)->update([
    		'nt_status' => 'Y'
    	]);
    	return response()->json([
    		'notifikasi' => $notifikasi,
    	]);
    }
    public function delete_notif(Request $request){
    	DB::table('d_notifications_todolist')->where('nt_id',$request->idnotif)->delete();
    	return response()->json([
'status' => 'success',
    	]);
    }
}
