<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DetailProdukController extends Controller
{
      public function index(Request $request)
    {
       

        return view('mobile.detail_produk');
    }
}
