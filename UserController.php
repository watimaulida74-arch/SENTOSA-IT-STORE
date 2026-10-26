<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna sistem (Admin Only)
     */
    public function index(Request $request)
    {
        $role = $request->query('role');
        $search = $request->query('search');

        $users = User::query()
            ->when($role, function ($q, $role) {
                return $q->where('role', $role);
            })
            ->when($search, function ($q, $search) {
                return $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'role', 'search'));
    }

    /**
     * Form buat pengguna baru
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Simpan pengguna baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,staf,user'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini telah digunakan pengguna lain.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'role.in' => 'Role harus salah satu dari: admin, staf, atau user.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$validated['name']} dengan role [{$validated['role']}] berhasil dibuat.");
    }

    /**
     * Form edit pengguna
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Perbarui data pengguna
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:admin,staf,user'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus pengguna (Proteksi: tidak bisa menghapus diri sendiri)
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif login.');
        }

        // Cek jika user memiliki transaksi
        if ($user->sales()->exists() || $user->purchases()->exists()) {
            return back()->with('error', "Pengguna '{$user->name}' tidak dapat dihapus karena tercatat dalam riwayat transaksi.");
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
