<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PicDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('picDetail')
            ->where('role_id', 2) // PIC role
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhereHas('picDetail', fn($q) => $q->where('full_name', 'like', "%{$search}%"));
            });
        }

        $users = $query->paginate(10)->withQueryString();
        return view('pages.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => ['required', 'string', 'max:100'],
            'nik'          => ['required', 'string', 'max:20'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'password'     => ['required', Password::min(8)],
        ]);

        $user = User::create([
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role_id'   => 2, // PIC
            'is_active' => true,
        ]);

        PicDetail::create([
            'id_user'   => $user->id,
            'full_name' => $validated['full_name'],
            'nik'       => $validated['nik'],
        ]);

        return back()->with('success', "Akun PIC {$validated['full_name']} berhasil dibuat.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'nik'       => ['required', 'string', 'max:20'],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
        ]);

        $user->update(['email' => $validated['email']]);

        $user->picDetail()->updateOrCreate(
            ['id_user' => $user->id],
            ['full_name' => $validated['full_name'], 'nik' => $validated['nik']]
        );

        if ($request->filled('password')) {
            $request->validate(['password' => ['required', Password::min(8)]]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', "Data PIC berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        $name = $user->picDetail?->full_name ?? $user->email;
        $user->delete();
        return back()->with('success', "Akun {$name} berhasil dihapus.");
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun berhasil {$status}.");
    }
}
