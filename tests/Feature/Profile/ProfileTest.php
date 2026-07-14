<?php

namespace Tests\Feature\Profile;

use App\Models\AdminDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // TC-010 - Update profil berhasil (nama & email)
    // =========================================================================
    public function test_tc010_admin_can_update_profile_with_valid_data(): void
    {
        // Arrange
        $admin = $this->createAdmin('admin@lama.com');

        // Act
        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'full_name' => 'Admin Baru Polibatam',
            'email'     => 'admin@baru.com',
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Email user berhasil diubah
        $this->assertDatabaseHas('users', ['email' => 'admin@baru.com']);

        // Nama di admin_detail berhasil diubah
        $this->assertDatabaseHas('admin_detail', [
            'id_user'   => $admin->id,
            'full_name' => 'Admin Baru Polibatam',
        ]);
    }

    // =========================================================================
    // TC-010b - Update profil: email unik (tidak konflik dengan email user lain)
    // =========================================================================
    public function test_tc010b_admin_can_keep_same_email_on_profile_update(): void
    {
        // Arrange: email sama boleh dipakai (update tanpa ganti email)
        $admin = $this->createAdmin('admin@sama.com');

        // Act
        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'full_name' => 'Nama Diubah',
            'email'     => 'admin@sama.com', // email sama, tidak konflik
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('admin_detail', [
            'id_user'   => $admin->id,
            'full_name' => 'Nama Diubah',
        ]);
    }

    // =========================================================================
    // TC-011 - Update profil invalid: full_name kosong
    // =========================================================================
    public function test_tc011_profile_update_fails_without_full_name(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'full_name' => '',
            'email'     => 'admin@test.com',
        ]);

        // Assert
        $response->assertSessionHasErrors('full_name');

        // AdminDetail tetap tidak berubah
        $this->assertDatabaseHas('admin_detail', [
            'id_user'   => $admin->id,
            'full_name' => 'Super Admin Test',
        ]);
    }

    // =========================================================================
    // TC-011b - Update profil invalid: format email salah
    // =========================================================================
    public function test_tc011b_profile_update_fails_with_invalid_email_format(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'full_name' => 'Admin Valid',
            'email'     => 'bukan-format-email', // tidak valid
        ]);

        // Assert
        $response->assertSessionHasErrors('email');
    }

    // =========================================================================
    // TC-011c - Update password berhasil
    // =========================================================================
    public function test_tc011c_admin_can_update_password_with_correct_current_password(): void
    {
        // Arrange
        $admin = $this->createAdmin('admin@test.com', 'oldpassword123');

        // Act
        $response = $this->actingAs($admin)->put(route('admin.profile.password'), [
            'current_password'      => 'oldpassword123',
            'password'              => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi password baru berhasil tersimpan
        $admin->refresh();
        $this->assertTrue(Hash::check('newpassword456', $admin->password));
    }

    // =========================================================================
    // TC-011d - Update password gagal: current_password salah
    // =========================================================================
    public function test_tc011d_password_update_fails_with_wrong_current_password(): void
    {
        // Arrange
        $admin = $this->createAdmin('admin@test.com', 'correctpassword');

        // Act
        $response = $this->actingAs($admin)->put(route('admin.profile.password'), [
            'current_password'      => 'wrongpassword', // salah
            'password'              => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        // Assert
        $response->assertSessionHasErrors('current_password');

        // Password lama tetap berlaku
        $admin->refresh();
        $this->assertTrue(Hash::check('correctpassword', $admin->password));
    }

    // =========================================================================
    // TC-011e - Update password gagal: password terlalu pendek (< 8 karakter)
    // =========================================================================
    public function test_tc011e_password_update_fails_if_new_password_too_short(): void
    {
        // Arrange
        $admin = $this->createAdmin('admin@test.com', 'correctpassword');

        // Act
        $response = $this->actingAs($admin)->put(route('admin.profile.password'), [
            'current_password'      => 'correctpassword',
            'password'              => 'short',  // < 8 karakter
            'password_confirmation' => 'short',
        ]);

        // Assert
        $response->assertSessionHasErrors('password');
    }

    // =========================================================================
    // TC-011f - Update password gagal: konfirmasi tidak cocok
    // =========================================================================
    public function test_tc011f_password_update_fails_if_confirmation_mismatch(): void
    {
        // Arrange
        $admin = $this->createAdmin('admin@test.com', 'correctpassword');

        // Act
        $response = $this->actingAs($admin)->put(route('admin.profile.password'), [
            'current_password'      => 'correctpassword',
            'password'              => 'newpassword456',
            'password_confirmation' => 'tidakcocok789', // berbeda
        ]);

        // Assert
        $response->assertSessionHasErrors('password');
    }

    // =========================================================================
    // TC-010c - Halaman profil bisa diakses oleh admin yang login
    // =========================================================================
    public function test_tc010c_profile_page_accessible_when_authenticated(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->get(route('admin.profile'));

        // Assert
        $response->assertStatus(200);
    }
}
