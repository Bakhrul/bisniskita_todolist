<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kategori;
use DB;
use Carbon\Carbon;
use Auth;
class KategoriController extends Controller
{
    public function getDataKategori(){
    	$category = Kategori::get();

    	return response()->json($category);
    }

    public function tambah_kategori(Request $request){
    	DB::BeginTransaction();
    	try{
    		$cekData  = Kategori::where('c_name',$request->nama_kategori)->where('c_user',null)->first();
    		$cekFromUser = Kategori::where('c_name',$request->nama_kategori)->where('c_user',Auth::user()->us_id)->first();
    		if($cekData != null || $cekFromUser != null){
    			return response()->json([
    				'status' => 'sudah ada',
    			]);
    		}
    		$Kategori  = new Kategori;
    		$Kategori->c_name = $request->nama_kategori;
    		$Kategori->c_user = Auth::user()->us_id;
    		$Kategori->c_created = Carbon::now('Asia/Jakarta');
    		$Kategori->c_updated = Carbon::now('Asia/Jakarta');
    		$Kategori->save();

    		DB::commit();
    		return response()->json([
    			'status' => 'success',
    		]);

    	}catch (Exception $e) {
    		DB::rollback();
            return $e;
        }
    }
}