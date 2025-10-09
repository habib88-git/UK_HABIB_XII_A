<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = Users::orderBy('user_id', 'desc')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:tbl_users,email',
            'sandi'     => 'required|min:8',
            'no_telp'   => 'nullable|string|min:12',
            'alamat'    => 'nullable|string',
            'role'      => 'required|in:admin,kasir',
        ]);

        Users::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'sandi'     => bcrypt($request->sandi),
            'no_telp'   => $request->no_telp,
            'alamat'    => $request->alamat,
            'role'      => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Users::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Users::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:tbl_users,email,' . $id . ',user_id',
            'sandi'     => 'nullable|min:8',
            'no_telp'   => 'nullable|string|min:12',
            'alamat'    => 'nullable|string',
            'role'      => 'required|in:admin,kasir',
        ]);

        $updateData = [
            'name'      => $request->name,
            'email'     => $request->email,
            'no_telp'   => $request->no_telp,
            'alamat'    => $request->alamat,
            'role'      => $request->role,
        ];

        if ($request->filled('sandi')) {
            $updateData['sandi'] = bcrypt($request->sandi);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Users::where('user_id', $id)->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}
