<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman katalog buku dengan fitur pencarian, filter, sorting, dan paginasi.
     */
    public function index(Request $request)
{
    $search   = $request->input('search');
    $penerbit = $request->input('penerbit');
    $status   = $request->input('status');
    $sort     = $request->input('sort');

    // daftar penerbit untuk dropdown filter
    $listPenerbit = Buku::select('penerbit')
        ->whereNotNull('penerbit')
        ->distinct()
        ->orderBy('penerbit')
        ->pluck('penerbit');

    $buku = Buku::query()
        ->when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%");
            });
        })
        ->when($penerbit, fn ($q) => $q->where('penerbit', $penerbit))
        ->when($status === 'tersedia', fn ($q) => $q->where('stok', '>', 0))
        ->when($status === 'habis', fn ($q) => $q->where('stok', '<=', 0));

    switch ($sort) {
        case 'judul':
            $buku->orderBy('judul', 'asc');
            break;
        case 'tahun':
            $buku->orderBy('tahun_terbit', 'desc');
            break;
        case 'stok':
            $buku->orderBy('stok', 'desc');
            break;
        default:
            $buku->latest();
    }

    $buku = $buku->paginate(5)->withQueryString();

    return view('home', compact('buku', 'search', 'penerbit', 'status', 'sort', 'listPenerbit'));
}
}
