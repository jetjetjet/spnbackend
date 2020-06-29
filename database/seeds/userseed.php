<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Model\Menu;
use App\Model\Group;
use App\Model\PositionMenu;
use App\Model\Position;

class Userseed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        $user = User::create([
            'full_name' => 'Super Admin',
            'position_id' => '1',
            'username' => 'admin',
            'email' => 'admin@ratafd.xyz',
            'jenis_kelamin' => 'Laki-laki',
            'nip' => '12345678',
            'password' => bcrypt('admin'),
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

       // factory(User::class, 40)->create();

        //Menu::truncate();
        // $hdr = Menu::create([
        //     'menu_name' => null,
        //     'display' => 'Master',
        //     'url' => '#',
        //     'icon' => 'fa-dashboard',
        //     'isparent' => '1',
        //     'parent_id' => null,
        //     'index' => '1',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);

        // $m0 = Menu::create([
        //     'menu_name' => 'User',
        //     'display' => 'User',
        //     'url' => '/User',
        //     'icon' => 'fa-users',
        //     'isparent' => '0',
        //     'parent_id' => $hdr->id,
        //     'index' => '1',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);

        // $m1 = Menu::create([
        //     'menu_name' => 'Menu',
        //     'display' => 'Menu',
        //     'url' => '/Menu',
        //     'icon' => 'fa-menu',
        //     'isparent' => '0',
        //     'parent_id' => $hdr->id,
        //     'index' => '2',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);

        // $m2 = Menu::create([
        //     'menu_name' => 'Jabatan',
        //     'display' => 'Jabatan',
        //     'url' => '/Jabatan',
        //     'icon' => 'fa-groups',
        //     'isparent' => '0',
        //     'parent_id' => $hdr->id,
        //     'index' => '3',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);

        // $hdr2 = Menu::create([
        //     'menu_name' => null,
        //     'display' => 'Surat',
        //     'url' => '#',
        //     'icon' => 'fa-dashboard',
        //     'isparent' => '1',
        //     'parent_id' => null,
        //     'index' => '2',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);

        // $m3 = Menu::create([
        //     'menu_name' => 'SuratMasuk',
        //     'display' => 'Surat Masuk',
        //     'url' => '/suratMasuk',
        //     'icon' => 'fa-groups',
        //     'isparent' => '0',
        //     'parent_id' => $hdr2->id,
        //     'index' => '1',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);

        // $m4 = Menu::create([
        //     'menu_name' => 'SuratKeluar',
        //     'display' => 'Surat Keluar',
        //     'url' => '/suratKeluar',
        //     'icon' => 'fa-groups',
        //     'isparent' => '0',
        //     'parent_id' => $hdr2->id,
        //     'index' => '2',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);

        Position::truncate();
        Group::truncate();

        $g1 = Group::create([
            'group_code' => 'SA',
            'group_name' => 'Admin Aplikasi',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $p1 = Position::create([
            'group_id' => $g1->id,
            'position_name' => 'Super Admin',
            'position_type' => 'Admin Aplikasi',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        // PositionMenu::truncate();
        // PositionMenu::create([
        //     'position_id' => $p1->id ,
        //     'menu_id' => $hdr->id,
        //     'permissions' => null,
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);
        // PositionMenu::create([
        //     'position_id' => $p1->id ,
        //     'menu_id' => $m0->id,
        //     'permissions' => 'user_view|user_save|user_delete|user_add',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);
        // PositionMenu::create([
        //     'position_id' => $p1->id ,
        //     'menu_id' => $m1->id,
        //     'permissions' => 'menu_read|menu_save|menu_delete|menu_add',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);
        // PositionMenu::create([
        //     'position_id' => $p1->id ,
        //     'menu_id' => $m2->id,
        //     'permissions' => 'jabatan_read|jabatan_save|jabatan_delete|jabatan_add',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);
        // PositionMenu::create([
        //     'position_id' => $p1->id ,
        //     'menu_id' => $hdr2->id,
        //     'permissions' => null ,
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);
        // PositionMenu::create([
        //     'position_id' => $p1->id ,
        //     'menu_id' => $m3->id,
        //     'permissions' => 'sm_read|sm_save|sm_delete|sm_add|sk_disposisi',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);
        // PositionMenu::create([
        //     'position_id' => $p1->id ,
        //     'menu_id' => $m4->id,
        //     'permissions' => 'sk_read|sk_save|sk_delete|sk_add|sk_disposisi',
        //     'active' => '1',
        //     'created_at' => now()->toDateTimeString(),
        //     'created_by' => '1'
        // ]);

    }
}
