<?php

namespace App\Http\Controllers;

use App\Models\Pelanggans;
use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\{Province, City, District, Village};


class PelangganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pelanggans = Pelanggans::with(['province', 'city', 'district', 'village'])->get();
        return view('pelanggan.index', compact('pelanggans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = Province::pluck('name', 'code');
        return view('pelanggan.create', compact('provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|max:100',
            'alamat'         => 'required',
            'nomor_telepon'  => 'required|max:15',
            'province_id'    => 'required',
            'city_id'        => 'required',
            'district_id'    => 'required',
            'village_id'     => 'required',
        ]);

        Pelanggans::create($request->all());

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $pelanggan = Pelanggans::findOrFail($id);
        return view('pelanggan.show', compact('pelanggan'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pelanggan = Pelanggans::findOrFail($id);

        // Ambil semua provinsi
        $provinces = Province::pluck('name', 'code');

        // Ambil data wilayah sesuai pelanggan
        $cities     = City::where('province_code', $pelanggan->province_id)->pluck('name', 'code');
        $districts  = District::where('city_code', $pelanggan->city_id)->pluck('name', 'code');
        $villages   = Village::where('district_code', $pelanggan->district_id)->pluck('name', 'code');

        return view('pelanggan.edit', compact(
            'pelanggan',
            'provinces',
            'cities',
            'districts',
            'villages'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_pelanggan' => 'required|max:100',
            'alamat'         => 'required',
            'nomor_telepon'  => 'required|max:15',
            'province_id'    => 'required',
            'city_id'        => 'required',
            'district_id'    => 'required',
            'village_id'     => 'required',
        ]);

        $pelanggan = Pelanggans::findOrFail($id);
        $pelanggan->update($request->all());

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pelanggan = Pelanggans::findOrFail($id);
        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil dihapus.');
    }
}
