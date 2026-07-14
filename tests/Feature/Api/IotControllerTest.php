<?php

namespace Tests\Feature\Api;

use App\Models\IotAuthSession;
use App\Models\Role;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteSubCategory;
use App\Models\UnitMeasured;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IotControllerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPER: Buat data role & user yang dibutuhkan
    // =========================================================================

    private function createUser(string $email = 'pic@test.com'): User
    {
        $role = Role::create(['name' => 'PIC']);

        return User::create([
            'email'     => $email,
            'password'  => bcrypt('password'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }

    private function createWasteSubCategory(): WasteSubCategory
    {
        $category = WasteCategory::create([
            'name'        => 'Sampah Organik',
            'description' => 'Deskripsi kategori',
        ]);

        $unit = UnitMeasured::create([
            'name'   => 'Kilogram',
            'type'   => 'weight',
            'symbol' => 'kg',
        ]);

        return WasteSubCategory::create([
            'id_waste_category'    => $category->id,
            'name'                 => 'Sisa Makanan',
            'id_unit_measured'     => $unit->id,
            'default_measured_qty' => 10.0,
        ]);
    }

    // =========================================================================
    // 1. generateCode - GET /api/iot/generate-code
    // =========================================================================

    /**
     * Test: generate code berhasil dan mengembalikan JSON dengan kode 4 karakter
     */
    public function test_generate_code_returns_success_with_4_char_code(): void
    {
        $response = $this->getJson('/api/iot/generate-code');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'code',
                     'message',
                 ])
                 ->assertJson(['success' => true]);

        $this->assertEquals(4, strlen($response->json('code')));
    }

    /**
     * Test: generate code menyimpan session dengan status 'pending' ke database
     */
    public function test_generate_code_creates_pending_session_in_database(): void
    {
        $this->getJson('/api/iot/generate-code');

        $this->assertDatabaseCount('iot_auth_sessions', 1);
        $this->assertDatabaseHas('iot_auth_sessions', ['status' => 'pending']);
    }

    /**
     * Test: generate code menghapus session lama (pending/paired) sebelum membuat baru
     */
    public function test_generate_code_deletes_old_pending_and_paired_sessions(): void
    {
        // Buat beberapa session lama
        IotAuthSession::create(['code' => 'OLD1', 'status' => 'pending']);
        IotAuthSession::create(['code' => 'OLD2', 'status' => 'paired']);

        $this->assertDatabaseCount('iot_auth_sessions', 2);

        $this->getJson('/api/iot/generate-code');

        // Hanya ada 1 session yang baru
        $this->assertDatabaseCount('iot_auth_sessions', 1);
        $this->assertDatabaseMissing('iot_auth_sessions', ['code' => 'OLD1']);
        $this->assertDatabaseMissing('iot_auth_sessions', ['code' => 'OLD2']);
    }

    /**
     * Test: kode yang dihasilkan berupa huruf besar (uppercase)
     */
    public function test_generate_code_returns_uppercase_code(): void
    {
        $response = $this->getJson('/api/iot/generate-code');
        $code = $response->json('code');

        $this->assertEquals(strtoupper($code), $code);
    }

    // =========================================================================
    // 2. pairCode - POST /api/iot/pair (auth:sanctum)
    // =========================================================================

    /**
     * Test: pair code berhasil ketika kode valid dan user valid
     */
    public function test_pair_code_success_with_valid_code_and_user(): void
    {
        $user = $this->createUser();

        IotAuthSession::create(['code' => 'AB12', 'status' => 'pending']);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/pair', [
                             'code'    => 'AB12',
                             'id_user' => $user->id,
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('iot_auth_sessions', [
            'code'    => 'AB12',
            'status'  => 'paired',
            'id_user' => $user->id,
        ]);
    }

    /**
     * Test: pair code case-insensitive (kode lowercase dikonversi ke uppercase)
     */
    public function test_pair_code_is_case_insensitive(): void
    {
        $user = $this->createUser();

        IotAuthSession::create(['code' => 'AB12', 'status' => 'pending']);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/pair', [
                             'code'    => 'ab12', // lowercase
                             'id_user' => $user->id,
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /**
     * Test: pair code gagal 404 jika kode tidak ditemukan
     */
    public function test_pair_code_returns_404_if_code_not_found(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/pair', [
                             'code'    => 'ZZZZ',
                             'id_user' => $user->id,
                         ]);

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test: pair code gagal 404 jika session sudah berstatus 'paired' (bukan 'pending')
     */
    public function test_pair_code_returns_404_if_session_already_paired(): void
    {
        $user = $this->createUser();

        IotAuthSession::create(['code' => 'XY99', 'status' => 'paired', 'id_user' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/pair', [
                             'code'    => 'XY99',
                             'id_user' => $user->id,
                         ]);

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test: pair code gagal validasi jika field 'code' tidak diisi
     */
    public function test_pair_code_validation_fails_without_code(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/pair', [
                             'id_user' => $user->id,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code']);
    }

    /**
     * Test: pair code gagal validasi jika id_user tidak ada di database
     */
    public function test_pair_code_validation_fails_with_nonexistent_user(): void
    {
        $user = $this->createUser();

        IotAuthSession::create(['code' => 'AB12', 'status' => 'pending']);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/pair', [
                             'code'    => 'AB12',
                             'id_user' => 99999, // tidak ada
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_user']);
    }

    // =========================================================================
    // 3. unpairCode - POST /api/iot/unpair (auth:sanctum)
    // =========================================================================

    /**
     * Test: unpair berhasil untuk session dengan status 'paired'
     */
    public function test_unpair_code_success_for_paired_session(): void
    {
        $user = $this->createUser();

        IotAuthSession::create(['code' => 'AB12', 'status' => 'paired', 'id_user' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/unpair', ['code' => 'AB12']);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('iot_auth_sessions', ['code' => 'AB12']);
    }

    /**
     * Test: unpair berhasil untuk session dengan status 'pending'
     */
    public function test_unpair_code_success_for_pending_session(): void
    {
        $user = $this->createUser();

        IotAuthSession::create(['code' => 'CD34', 'status' => 'pending']);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/unpair', ['code' => 'CD34']);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('iot_auth_sessions', ['code' => 'CD34']);
    }

    /**
     * Test: unpair gagal 404 jika kode tidak ditemukan
     */
    public function test_unpair_code_returns_404_if_code_not_found(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/unpair', ['code' => 'ZZZZ']);

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test: unpair gagal validasi jika field 'code' tidak diisi
     */
    public function test_unpair_code_validation_fails_without_code(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/iot/unpair', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code']);
    }

    // =========================================================================
    // 4. checkStatus - GET /api/iot/check-status/{code}
    // =========================================================================

    /**
     * Test: check status mengembalikan status 'pending' dengan success false
     */
    public function test_check_status_returns_pending_status(): void
    {
        IotAuthSession::create(['code' => 'AB12', 'status' => 'pending']);

        $response = $this->getJson('/api/iot/check-status/AB12');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => false,
                     'status'  => 'pending',
                 ]);
    }

    /**
     * Test: check status mengembalikan status 'paired' dengan data user
     */
    public function test_check_status_returns_paired_status_with_user(): void
    {
        $user = $this->createUser();

        IotAuthSession::create([
            'code'    => 'XY99',
            'status'  => 'paired',
            'id_user' => $user->id,
        ]);

        $response = $this->getJson('/api/iot/check-status/XY99');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'status'  => 'paired',
                     'message' => 'Device is paired',
                 ])
                 ->assertJsonStructure([
                     'success',
                     'status',
                     'user',
                     'message',
                 ]);
    }

    /**
     * Test: check status case-insensitive (kode lowercase dikonversi ke uppercase)
     */
    public function test_check_status_is_case_insensitive(): void
    {
        IotAuthSession::create(['code' => 'AB12', 'status' => 'pending']);

        $response = $this->getJson('/api/iot/check-status/ab12');

        $response->assertStatus(200);
    }

    /**
     * Test: check status mengembalikan 404 jika kode tidak ditemukan
     */
    public function test_check_status_returns_404_if_session_not_found(): void
    {
        $response = $this->getJson('/api/iot/check-status/XXXX');

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    // =========================================================================
    // 5. storeWeight - POST /api/iot/store-weight
    // =========================================================================

    /**
     * Test: store weight berhasil menyimpan data timbangan
     */
    public function test_store_weight_success_saves_waste_entry(): void
    {
        $user        = $this->createUser();
        $subCategory = $this->createWasteSubCategory();

        IotAuthSession::create([
            'code'    => 'AB12',
            'status'  => 'paired',
            'id_user' => $user->id,
        ]);

        $response = $this->postJson('/api/iot/store-weight', [
            'code'                  => 'AB12',
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 25.5,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Weight data saved successfully',
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data',
                 ]);

        $this->assertDatabaseHas('waste_entry', [
            'id_user'               => $user->id,
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 25.5,
            'notes'                 => 'Timbangan Otomatis (IoT)',
        ]);
    }

    /**
     * Test: store weight berhasil dan session TETAP 'paired' (tidak berubah)
     */
    public function test_store_weight_keeps_session_paired_after_storing(): void
    {
        $user        = $this->createUser();
        $subCategory = $this->createWasteSubCategory();

        IotAuthSession::create([
            'code'    => 'AB12',
            'status'  => 'paired',
            'id_user' => $user->id,
        ]);

        $this->postJson('/api/iot/store-weight', [
            'code'                  => 'AB12',
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 10.0,
        ]);

        // Sesi harus tetap 'paired', bukan 'completed'
        $this->assertDatabaseHas('iot_auth_sessions', [
            'code'   => 'AB12',
            'status' => 'paired',
        ]);
    }

    /**
     * Test: store weight gagal 403 jika session tidak berstatus 'paired'
     */
    public function test_store_weight_fails_403_if_session_not_paired(): void
    {
        $subCategory = $this->createWasteSubCategory();

        IotAuthSession::create(['code' => 'AB12', 'status' => 'pending']);

        $response = $this->postJson('/api/iot/store-weight', [
            'code'                  => 'AB12',
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 15.0,
        ]);

        $response->assertStatus(403)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test: store weight gagal validasi jika field 'code' tidak ada di database
     */
    public function test_store_weight_validation_fails_with_nonexistent_code(): void
    {
        $subCategory = $this->createWasteSubCategory();

        $response = $this->postJson('/api/iot/store-weight', [
            'code'                  => 'ZZZZ', // tidak ada di DB
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 10.0,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code']);
    }

    /**
     * Test: store weight gagal validasi jika measured_qty bukan angka
     */
    public function test_store_weight_validation_fails_if_measured_qty_is_not_numeric(): void
    {
        $subCategory = $this->createWasteSubCategory();

        IotAuthSession::create(['code' => 'AB12', 'status' => 'paired']);

        $response = $this->postJson('/api/iot/store-weight', [
            'code'                  => 'AB12',
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 'bukan_angka',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['measured_qty']);
    }

    /**
     * Test: store weight gagal validasi jika id_waste_sub_category tidak ada
     */
    public function test_store_weight_validation_fails_with_nonexistent_sub_category(): void
    {
        $user = $this->createUser();

        IotAuthSession::create([
            'code'    => 'AB12',
            'status'  => 'paired',
            'id_user' => $user->id,
        ]);

        $response = $this->postJson('/api/iot/store-weight', [
            'code'                  => 'AB12',
            'id_waste_sub_category' => 99999, // tidak ada
            'measured_qty'          => 10.0,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_waste_sub_category']);
    }

    /**
     * Test: store weight gagal validasi jika semua field kosong
     */
    public function test_store_weight_validation_fails_with_empty_payload(): void
    {
        $response = $this->postJson('/api/iot/store-weight', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code', 'id_waste_sub_category', 'measured_qty']);
    }
}
