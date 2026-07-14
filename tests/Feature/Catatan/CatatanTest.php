<?php

namespace Tests\Feature\Catatan;

use App\Models\Report;
use App\Models\CategoryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatatanTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPER: buat kategori laporan
    // =========================================================================
    private function createReportCategory(string $name = 'Masalah Kendaraan'): CategoryReport
    {
        return CategoryReport::create(['name' => $name]);
    }

    // =========================================================================
    // TC-023 - Catatan operasional (laporan kendala) valid: PIC berhasil simpan
    // =========================================================================
    public function test_tc023_pic_can_store_laporan_kendala_with_valid_data(): void
    {
        // Arrange
        $this->createAdmin(); // pastikan admin role id=1 ada
        $pic      = $this->createPic();
        $category = $this->createReportCategory();

        // Act
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/laporan-kendala', [
            'id_category_report' => $category->id,
            'title'              => 'Kendaraan Pengangkut Rusak',
            'content'            => 'Truck pengangkut sampah mengalami kerusakan di roda belakang.',
        ]);

        // Assert
        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('report', [
            'id_user'            => $pic->id,
            'id_category_report' => $category->id,
            'title'              => 'Kendaraan Pengangkut Rusak',
        ]);
    }

    // =========================================================================
    // TC-023b - Catatan operasional: bisa diambil kembali via API (show)
    // =========================================================================
    public function test_tc023b_laporan_kendala_can_be_retrieved_by_id(): void
    {
        // Arrange
        $this->createAdmin();
        $pic      = $this->createPic();
        $category = $this->createReportCategory('Alat Berat Rusak');

        $report = Report::create([
            'id_user'            => $pic->id,
            'id_category_report' => $category->id,
            'title'              => 'Alat Berat Tidak Berfungsi',
            'content'            => 'Detail laporan alat berat.',
        ]);

        // Act
        $response = $this->actingAs($pic, 'sanctum')->getJson("/api/laporan-kendala/{$report->id}");

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.id', $report->id)
                 ->assertJsonPath('data.title', 'Alat Berat Tidak Berfungsi');
    }

    // =========================================================================
    // TC-023c - Daftar kategori kendala bisa diambil
    // =========================================================================
    public function test_tc023c_kategori_kendala_can_be_retrieved(): void
    {
        // Arrange
        $this->createAdmin();
        $pic = $this->createPic();
        $this->createReportCategory('Masalah Kendaraan');
        $this->createReportCategory('Kendala Cuaca');

        // Act
        $response = $this->actingAs($pic, 'sanctum')->getJson('/api/kategori-kendala');

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonCount(2, 'data');
    }

    // =========================================================================
    // TC-023d - Show laporan yang tidak ada mengembalikan 404
    // =========================================================================
    public function test_tc023d_show_nonexistent_report_returns_404(): void
    {
        // Arrange
        $this->createAdmin();
        $pic = $this->createPic();

        // Act
        $response = $this->actingAs($pic, 'sanctum')->getJson('/api/laporan-kendala/99999');

        // Assert
        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    // =========================================================================
    // TC-024 - Catatan invalid: field wajib kosong
    // =========================================================================
    public function test_tc024_laporan_kendala_fails_without_required_fields(): void
    {
        // Arrange
        $this->createAdmin();
        $pic = $this->createPic();

        // Act: kirim tanpa field apapun
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/laporan-kendala', []);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'id_category_report',
                     'title',
                     'content',
                 ]);

        $this->assertDatabaseCount('report', 0);
    }

    // =========================================================================
    // TC-024b - Catatan invalid: id_category_report tidak ada di database
    // =========================================================================
    public function test_tc024b_laporan_kendala_fails_with_nonexistent_category(): void
    {
        // Arrange
        $this->createAdmin();
        $pic = $this->createPic();

        // Act
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/laporan-kendala', [
            'id_category_report' => 9999, // tidak ada
            'title'              => 'Laporan Test',
            'content'            => 'Isi laporan.',
        ]);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_category_report']);
    }

    // =========================================================================
    // TC-024c - Catatan invalid: title melebihi max:255 karakter
    // =========================================================================
    public function test_tc024c_laporan_kendala_fails_with_title_exceeding_255_chars(): void
    {
        // Arrange
        $this->createAdmin();
        $pic      = $this->createPic();
        $category = $this->createReportCategory();

        // Act
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/laporan-kendala', [
            'id_category_report' => $category->id,
            'title'              => str_repeat('A', 256), // 256 karakter (maks 255)
            'content'            => 'Isi laporan.',
        ]);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    // =========================================================================
    // TC-024d - Catatan: endpoint butuh autentikasi sanctum
    // =========================================================================
    public function test_tc024d_laporan_kendala_requires_authentication(): void
    {
        // Arrange: tidak ada auth
        $this->createAdmin();
        $category = $this->createReportCategory();

        // Act
        $response = $this->postJson('/api/laporan-kendala', [
            'id_category_report' => $category->id,
            'title'              => 'Test Tanpa Auth',
            'content'            => 'Tidak boleh lolos.',
        ]);

        // Assert: 401 Unauthorized
        $response->assertStatus(401);
    }

    // =========================================================================
    // TC-023e - Admin dapat melihat daftar laporan di web panel
    // =========================================================================
    public function test_tc023e_admin_can_view_pic_report_list(): void
    {
        // Arrange
        $admin    = $this->createAdmin();
        $pic      = $this->createPic('pic@test.com');
        $category = $this->createReportCategory();

        Report::create([
            'id_user'            => $pic->id,
            'id_category_report' => $category->id,
            'title'              => 'Laporan Cuaca Buruk',
            'content'            => 'Hujan deras menghambat pengangkutan.',
        ]);

        // Act
        $response = $this->actingAs($admin)->get(route('admin.pic-report.index'));

        // Assert
        $response->assertStatus(200);
    }
}
