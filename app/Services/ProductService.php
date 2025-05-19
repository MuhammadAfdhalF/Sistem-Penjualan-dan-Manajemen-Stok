<?php

namespace App\Services;

use App\Contracts\ProductInterface;
use App\Models\Produk;

class ProductService implements ProductInterface
{
    public function getAll()
    {
        return Produk::all();
    }

    public function getProduk()
    {
        $args = func_get_args();
        $jumlah = count($args);

        return match ($jumlah) {
            0 => Produk::all(),
            1 => Produk::find($args[0]),
            2 => Produk::where($args[0], $args[1])->get(),
            default => null,
        };
    }




    public function getById(int $id)
    {
        return Produk::find($id);
    }

    public function create(array $data)
    {
        return Produk::create($data);
    }

    public function update(int $id, array $data)
    {
        $produk = Produk::find($id);
        if ($produk) {
            $produk->update($data);
            return $produk;
        }
        return null;
    }

    public function delete(int $id)
    {
        $produk = Produk::find($id);
        if ($produk) {
            return $produk->delete();
        }
        return false;
    }
}
