<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use App\Models\AdminDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // TC-001 - Login berhasil dengan kredensial admin yang valid
    // -------------------------------------------------------------------------
    public function test_tc001_admin_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        // Arrange
        $admin = $this->createAdmin('admin@polibatam.ac.id', 'adminpass123');

        // Act
        $response = $this->post(route('login.post'), [
            'email'    => 'admin@polibatam.ac.id',
            'password' => 'adminpass123',
        ]);

        // Assert
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    // -------------------------------------------------------------------------
    // TC-002 - Login gagal: password salah
    // -------------------------------------------------------------------------
    public function test_tc002_login_fails_with_wrong_password(): void
    {
        // Arrange
        $this->createAdmin('admin@polibatam.ac.id', 'correct_password');

        // Act
        $response = $this->post(route('login.post'), [
            'email'    => 'admin@polibatam.ac.id',
            'password' => 'wrong_password',
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // TC-002b - Login gagal: email tidak terdaftar
    // -------------------------------------------------------------------------
    public function test_tc002b_login_fails_with_nonexistent_email(): void
    {
        // Arrange - tidak ada user

        // Act
        $response = $this->post(route('login.post'), [
            'email'    => 'notexist@example.com',
            'password' => 'anypassword',
        ]);

        // Assert
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // TC-002c - Login gagal: user PIC tidak bisa masuk ke dashboard admin
    // -------------------------------------------------------------------------
    public function test_tc002c_login_fails_if_user_is_not_admin_role(): void
    {
        // Arrange: buat admin role dulu (agar id=1), lalu buat PIC role
        $adminRole = Role::create(['name' => 'admin']); // id=1
        $picRole   = Role::create(['name' => 'pic']);   // id=2

        // Pastikan admin role benar-benar id=1 (yang dicek controller)
        $this->assertEquals(1, $adminRole->id);

        $pic = User::create([
            'email'     => 'pic@test.com',
            'password'  => Hash::make('password'),
            'role_id'   => $picRole->id, // id=2, bukan 1
            'is_active' => true,
        ]);

        // Act
        $response = $this->post(route('login.post'), [
            'email'    => 'pic@test.com',
            'password' => 'password',
        ]);

        // Assert: ditolak karena bukan admin (role_id != 1)
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // TC-002d - Login gagal: akun admin tidak aktif
    // -------------------------------------------------------------------------
    public function test_tc002d_login_fails_if_admin_account_is_inactive(): void
    {
        // Arrange
        $role = Role::firstOrCreate(['name' => 'admin']);
        User::create([
            'email'     => 'inactive@test.com',
            'password'  => Hash::make('password'),
            'role_id'   => $role->id,
            'is_active' => false, // tidak aktif
        ]);

        // Act
        $response = $this->post(route('login.post'), [
            'email'    => 'inactive@test.com',
            'password' => 'password',
        ]);

        // Assert
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // TC-002e - Login gagal: field email kosong (validasi required)
    // -------------------------------------------------------------------------
    public function test_tc002e_login_fails_with_empty_email(): void
    {
        // Act
        $response = $this->post(route('login.post'), [
            'email'    => '',
            'password' => 'password',
        ]);

        // Assert
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // TC-003 - Logout berhasil dan diredirect ke halaman login
    // -------------------------------------------------------------------------
    public function test_tc003_authenticated_admin_can_logout(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->post(route('logout'));

        // Assert
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // TC-003b - Halaman dashboard memerlukan autentikasi
    // -------------------------------------------------------------------------
    public function test_tc003b_unauthenticated_user_redirected_to_login(): void
    {
        // Act
        $response = $this->get(route('admin.dashboard'));

        // Assert
        $response->assertRedirect(route('login'));
    }
}
