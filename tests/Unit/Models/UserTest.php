<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa nama tabel model adalah 'users'
     */
    public function test_table_name_is_correct(): void
    {
        $user = new User();
        $this->assertEquals('users', $user->getTable());
    }

    /**
     * Test bahwa kolom fillable sudah sesuai
     */
    public function test_fillable_attributes(): void
    {
        $user = new User();
        $expected = ['email', 'password', 'role_id', 'is_active', 'created_at', 'photo'];
        $this->assertEquals($expected, $user->getFillable());
    }

    /**
     * Test bahwa password tersembunyi dari serialisasi
     */
    public function test_password_is_hidden(): void
    {
        $user = new User();
        $this->assertContains('password', $user->getHidden());
    }

    /**
     * Test bahwa password di-cast sebagai hashed
     */
    public function test_password_is_cast_to_hashed(): void
    {
        $user = new User();
        $casts = $user->getCasts();
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('hashed', $casts['password']);
    }

    /**
     * Test bahwa is_active di-cast sebagai boolean
     */
    public function test_is_active_is_cast_to_boolean(): void
    {
        $user = new User();
        $casts = $user->getCasts();
        $this->assertArrayHasKey('is_active', $casts);
        $this->assertEquals('boolean', $casts['is_active']);
    }

    /**
     * Test bahwa user bisa dibuat dengan data valid
     */
    public function test_can_create_user(): void
    {
        $role = Role::create(['name' => 'Admin']);

        $user = User::create([
            'email'     => 'admin@test.com',
            'password'  => 'password',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email'     => 'admin@test.com',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
        $this->assertNotNull($user->id);
    }

    /**
     * Test bahwa password otomatis di-hash
     */
    public function test_password_is_automatically_hashed(): void
    {
        $role = Role::create(['name' => 'PIC']);

        $user = User::create([
            'email'     => 'pic@test.com',
            'password'  => 'plainpassword',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        // Password harus berbeda dari plain text karena sudah di-hash
        $this->assertNotEquals('plainpassword', $user->password);
        // Verifikasi hash valid menggunakan password_verify
        $this->assertTrue(password_verify('plainpassword', $user->password));
    }

    /**
     * Test relasi belongsTo ke Role
     */
    public function test_belongs_to_role(): void
    {
        $role = Role::create(['name' => 'PIC']);

        $user = User::create([
            'email'     => 'pic2@test.com',
            'password'  => 'secret',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals('PIC', $user->role->name);
    }

    /**
     * Test relasi hasMany ke WasteEntry
     */
    public function test_has_many_waste_entries(): void
    {
        $role = Role::create(['name' => 'PIC']);

        $user = User::create([
            'email'     => 'pic3@test.com',
            'password'  => 'secret',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        // Relasi harus mengembalikan instance HasMany (tanpa perlu data nyata)
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $user->wasteEntries()
        );
    }

    /**
     * Test relasi hasOne ke AdminDetail
     */
    public function test_has_one_admin_detail_relationship_exists(): void
    {
        $role = Role::create(['name' => 'Admin']);

        $user = User::create([
            'email'     => 'admin2@test.com',
            'password'  => 'secret',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $user->adminDetail()
        );
    }

    /**
     * Test relasi hasOne ke PicDetail
     */
    public function test_has_one_pic_detail_relationship_exists(): void
    {
        $role = Role::create(['name' => 'PIC']);

        $user = User::create([
            'email'     => 'pic4@test.com',
            'password'  => 'secret',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $user->picDetail()
        );
    }

    /**
     * Test bahwa photo bisa null
     */
    public function test_photo_can_be_null(): void
    {
        $role = Role::create(['name' => 'PIC']);

        $user = User::create([
            'email'     => 'pic5@test.com',
            'password'  => 'secret',
            'role_id'   => $role->id,
            'is_active' => true,
            'photo'     => null,
        ]);

        $this->assertNull($user->photo);
    }
}
