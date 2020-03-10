<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Carbon\Carbon;

class FriendListController extends Controller
{
    public function get_friendlist(Request $request)
    {
        $friendList = DB::table('d_friendlist')
                     ->join('m_users', 'fl_friend', 'us_id')
                    ->where('fl_users', Auth::user()->us_id)->get();

        return response()->json($friendList);
    }

    public function get_friendlist_filter($nama)
    {
        $friendList = DB::table('d_friendlist')
                     ->join('m_users', 'fl_friend', 'us_id')
                    ->where('fl_users', Auth::user()->us_id)
                    ->where('fl_approved','!=', NULL )
                    ->select('us_name AS name', 'fl_users AS user', 'fl_friend AS friend', 'us_image AS image','us_email AS email');
                    
          if ($nama == 'all') {
              $friendList = $friendList->get();
          }else {
              $friendList = $friendList->where('us_name', 'LIKE', "%$nama%")->get();
          }                      
        
        if (count($friendList) > 0) {
            return response()->json($friendList);
        } else {
            return response()->json('notfound');
        }
    }
    
    public function tambah_teman(Request $request)
    {
        DB::BeginTransaction();
        try {
            $cariFriend = DB::table('m_users')->where('us_email', $request->email)->first();
            if ($cariFriend == null) {
                return response()->json([
                    'status' => 'email tidak ditemukan',
                    'message' => 'Email Ini Belum Memiliki Akun Pengguna',
                ]);
            }
            $availableFriend = DB::table('d_friendlist')
                                ->where('fl_users', Auth::user()->us_id)
                                ->where('fl_friend', $cariFriend->us_id)
                                ->first();

            if ($availableFriend != null) {
                if ($availableFriend->fl_approved == null && $availableFriend->fl_denied == null) {
                    return response()->json([
                        'status' => 'menunggu persetujuan',
                        'message' => 'Anda Sudah Meminta Pertemanan Dan Menunggu persetujuan Email Tujuan',
                    ]);
                } elseif ($availableFriend->fl_approved != null) {
                    return response()->json([
                        'status' => 'sudah berteman',
                        'message' => 'Anda Sudah Berteman Dengan Pengguna Ini',
                    ]);
                }
            }
            DB::table('d_friendlist')
            ->where('fl_users', Auth::user()->us_id)
            ->where('fl_friend', $cariFriend->us_id)
            ->delete();

            DB::table('d_friendlist')->insert([
                'fl_users' => Auth::user()->us_id,
                'fl_friend' => $cariFriend->us_id,
                'fl_added' => Carbon::now('Asia/Jakarta'),
                'fl_approved' => null,
                'fl_denied' => null,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil!',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function konfirmasiTeman(Request $request)
    {
        setlocale(LC_TIME, 'IND');
        DB::BeginTransaction();
        try {
            if ($request->type_confirmation == 'terima') {
                $dateAcc = Carbon::now('Asia/Jakarta');
                $dateDenied = null;
            } else {
                $dateAcc = null;
                $dateDenied = Carbon::now('Asia/Jakarta');
            }
            DB::table('d_friendlist')->where('fl_users', $request->friend)->where('fl_friend', Auth::user()->us_id)->update([
                'fl_approved' => $dateAcc,
                'fl_denied' => $dateDenied,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function hapus_teman(Request $request)
    {
        DB::BeginTransaction();
        try {
            DB::table('d_friendlist')->where('fl_users', Auth::user()->us_id)->where('fl_friend', $request->friend)->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function get_confirmation_friend()
    {
        $confirmationFriend = DB::table('d_friendlist')
                             ->join('m_users', 'fl_users', 'us_id')
                             ->where('fl_friend', Auth::user()->us_id)
                             ->where('fl_approved', null)
                             ->where('fl_denied', null)
                             ->get();
        return response()->json($confirmationFriend);
    }
    public function get_friend_acc(Request $request){
    	$friend = DB::table('d_friendlist')
    			->join('m_users','fl_friend','us_id')
    			->where('fl_users',Auth::user()->us_id)
    			->where('fl_approved','!=', null);

    	if($request->search != null){
    		$friend = $friend->where('us_name', 'LIKE', $request->search .'%')->get();
    	}else{
    		$friend = $friend->get();
    	}

    	return response()->json($friend);
    }
}
