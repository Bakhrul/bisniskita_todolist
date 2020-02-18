<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kategori;

class KategoriController extends Controller
{
    public function getDataKategori(){
    	$category = Kategori::get();

    	return response()->json($category);
    }
}
