<?php

namespace Tests\Unit\Models;

use App\Models\IotAuthSession;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IotAuthSessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa nama tabel model adalah 'iot_auth_sessions'
     */
    public function test_table_name_is_correct(): void
    {
        $session = new IotAuthSession();
        $this->assertEquals('iot_auth_sessions', $session->getTable());
    }

    /**
     * Test bahwa kolom fillable sudah sesuai
     */
    public function test_fillable_attributes(): void
    {
        $session = new IotAuthSession();
        $this->assertEquals(['code', 'id_user', 'status'], $session->getFillable());
    }

    /**
     * Test bahwa model bisa dibuat dengan data valid
     */
    public function test_can_create_iot_auth_session(): void
    {
        $session = IotAuthSession::create([
            'code'   => 'AB12',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('iot_auth_sessions', [
            'code'   => 'AB12',
            'status' => 'pending',
        ]);
        $this->assertNotNull($session->id);
    }

    /**
     * Test relasi belongsTo ke User
     */
    public function test_belongs_to_user(): void
    {
        $role = Role::create(['name' => 'PIC']);

        $user = User::create([
            'email'     => 'pic@test.com',
            'password'  => bcrypt('password'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $session = IotAuthSession::create([
            'code'    => 'XY99',
            'id_user' => $user->id,
            'status'  => 'paired',
        ]);

        $this->assertInstanceOf(User::class, $session->user);
        $this->assertEquals($user->id, $session->user->id);
        $this->assertEquals('pic@test.com', $session->user->email);
    }

    /**
     * Test bahwa id_user bisa null (session belum dipasangkan)
     */
    public function test_id_user_can_be_null(): void
    {
        $session = IotAuthSession::create([
            'code'   => 'CD34',
            'status' => 'pending',
        ]);

        $this->assertNull($session->id_user);
        $this->assertNull($session->user);
    }

    /**
     * Test status bisa diubah ke 'paired'
     */
    public function test_status_can_be_updated_to_paired(): void
    {
        $session = IotAuthSession::create([
            'code'   => 'EF56',
            'status' => 'pending',
        ]);

        $session->update(['status' => 'paired']);

        $this->assertEquals('paired', $session->fresh()->status);
    }

    /**
     * Test bahwa kode bersifat unik di database
     */
    public function test_code_must_be_unique(): void
    {
        IotAuthSession::create(['code' => 'AA11', 'status' => 'pending']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        IotAuthSession::create(['code' => 'AA11', 'status' => 'pending']);
    }
}
