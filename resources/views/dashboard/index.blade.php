@extends('layouts.mantis')

@section('title')
Halaman Dashboard
@endsection

@section('content')
<div class="container">
    {{-- Dashboard Info (Mockup placeholder) --}}
    <div class="border p-3 mb-4 rounded  text-muted" style="min-height: 150px;">
        @include('dashboard.d_info')
    </div>

    {{-- Dashboard Stok Barang (Mockup placeholder) --}}
    <div class="border p-3 mb-4 rounded  text-muted" style="min-height: 150px;">
        @include('dashboard.d_keuangan')

    </div>



    {{-- Dashboard Ringkasan Keuangan (Mockup placeholder) --}}
    <div class="border p-3 mb-4 rounded  text-muted" style="min-height: 150px;">
        @include('dashboard.d_stok')

    </div>

</div>
@endsection