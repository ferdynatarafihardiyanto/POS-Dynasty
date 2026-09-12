<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.karyawan.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,kasir'],
            'password' => ['required', 'string', 'min:4'],
        ], [
            'name.required' => 'Nama karyawan wajib diisi.',
            'email.required' => 'Email atau Username login wajib diisi.',
            'email.unique' => 'Email/Username ini sudah terdaftar.',
            'password.required' => 'Password akun wajib diisi.',
            'password.min' => 'Password minimal 4 karakter.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.karyawan.index')->with('success', 'Akun karyawan baru berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,kasir'],
            'password' => ['nullable', 'string', 'min:4'],
        ], [
            'name.required' => 'Nama karyawan wajib diisi.',
            'email.required' => 'Email atau Username login wajib diisi.',
            'email.unique' => 'Email/Username ini sudah terdaftar.',
            'password.min' => 'Password minimal 4 karakter.',
        ]);

        $dataToUpdate = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        $user->update($dataToUpdate);

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if (Auth::id() == $id) {
            return redirect()->route('admin.karyawan.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.karyawan.index')->with('success', 'Akun karyawan berhasil dihapus.');
    }
}
