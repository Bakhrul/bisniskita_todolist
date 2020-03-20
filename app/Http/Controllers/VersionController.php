<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class VersionController extends Controller
{
    public function checkversion($id)
    {
        $olderVersion = DB::table('d_version')->where('v_id',$id)->where('v_isactived','N')->exists();
        if (!$olderVersion) {
        $latestVersion = DB::table('d_version')->orderBy('v_releasedate','DESC')->where('v_isactived','!=','N')->orwhere('v_isactived',NULL)->first();
        if($latestVersion){
            if ($id == $latestVersion->v_id) {
            return response()->json('Normal');
            }else if ($id < $latestVersion->v_id ) {
                return response()->json('Warning');
            }
        }else{
            return response()->json('Normal');
        }
        }else{
            return response()->json('Expired');
        }
        
    }
    public function cekversi_aplikasi(Request $request){

        $isTerbaru = DB::table('d_version')->orderBy('v_id','DESC')->first();
        $cekversiDB = DB::table('d_version')->where('v_id',$request->version)->first();
        if($isTerbaru->v_id == $request->version){
            return response()->json([
                'status' => 'sudah terbaru',
            ]);
        }
        if($isTerbaru->v_id != $request->version){
            if($isTerbaru->v_id > $request->version){
                if($cekversiDB->v_isactived == 'Y'){
                    return response()->json([
                        'status' => 'rekomendasi update',
                    ]);    
                }else{
                        return response()->json([
                            'status' => 'wajib update',
                        ]);    
                }    
            }else{
                return response()->json([
                    'status' => 'sudah terbaru',
                ]);
            }
            
            
        }
    }
}
