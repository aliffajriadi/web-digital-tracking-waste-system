<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IotAuthSession;
use App\Models\PicDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    private const ROLE_PIC = 2;

    public function index(Request $request)
    {
        $query = User::with('picDetail')
            ->where('role_id', self::ROLE_PIC)
            ->withCount('wasteEntries')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhereHas('picDetail', fn ($q) => $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->paginate(10)->withQueryString();
        return view('pages.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => ['required', 'string', 'max:100'],
            'nik'          => ['required', 'string', 'max:20', 'unique:pic_detail,nik'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'phone'        => ['nullable', 'string', 'max:25'],
            'password'     => ['required', Password::min(8)],
        ], [
            'nik.unique' => 'NIK sudah terdaftar pada akun lain.',
            'email.unique' => 'Email sudah terdaftar pada akun lain.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role_id'   => self::ROLE_PIC,
                'is_active' => true,
            ]);

            PicDetail::create([
                'id_user'   => $user->id,
                'full_name' => $validated['full_name'],
                'nik'       => $validated['nik'],
                'phone'     => $validated['phone'] ?? null,
            ]);
        });

        return back()->with('success', "Akun PIC {$validated['full_name']} berhasil dibuat.");
    }

    public function update(Request $request, User $user)
    {
        $this->ensurePic($user);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'nik'       => ['required', 'string', 'max:20', Rule::unique('pic_detail', 'nik')->ignore($user->id, 'id_user')],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone'     => ['nullable', 'string', 'max:25'],
            'password'  => ['nullable', Password::min(8)],
        ], [
            'nik.unique' => 'NIK sudah terdaftar pada akun lain.',
            'email.unique' => 'Email sudah terdaftar pada akun lain.',
        ]);

        $user->update(['email' => $validated['email']]);

        $user->picDetail()->updateOrCreate(
            ['id_user' => $user->id],
            [
                'full_name' => $validated['full_name'],
                'nik' => $validated['nik'],
                'phone' => $validated['phone'] ?? null,
            ]
        );

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
            // Kata sandi direset admin: paksa login ulang di aplikasi
            $user->tokens()->delete();
        }

        return back()->with('success', 'Data PIC berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->ensurePic($user);

        $hasTransactions = $user->wasteEntries()->exists()
            || DB::table('waste_out_data')->where('id_user', $user->id)->exists()
            || DB::table('processed_waste_data')->where('id_user', $user->id)->exists()
            || DB::table('report')->where('id_user', $user->id)->exists();

        if ($hasTransactions) {
            return back()->with('error', 'Akun tidak dapat dihapus karena sudah memiliki riwayat transaksi. Nonaktifkan akun ini sebagai gantinya.');
        }

        $name = $user->picDetail?->full_name ?? $user->email;

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            IotAuthSession::where('id_user', $user->id)->delete();
            $user->picDetail()->delete();
            $user->delete();
        });

        return back()->with('success', "Akun {$name} berhasil dihapus.");
    }

    public function toggleStatus(User $user)
    {
        $this->ensurePic($user);

        $user->update(['is_active' => !$user->is_active]);

        if (!$user->is_active) {
            // Putus semua sesi aplikasi & timbangan milik akun yang dinonaktifkan
            $user->tokens()->delete();
            IotAuthSession::where('id_user', $user->id)->delete();
        }

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun berhasil {$status}.");
    }

    private function ensurePic(User $user): void
    {
        // Halaman ini hanya untuk akun PIC; akun admin tidak boleh diubah dari sini
        abort_unless((int) $user->role_id === self::ROLE_PIC, 404);
    }
}
