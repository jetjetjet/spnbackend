<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Model\Menu;
use App\Model\GroupMenu;
use App\Model\Group;

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
        User::create([
            'full_name' => 'Afdal Zikri',
            'username' => 'Afdal',
            'email' => 'afd.zik@gmail.com',
            'jenis_kelamin' => 'Laki-laki',
            'nip' => '12345678',
            'password' => bcrypt('afd'),
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        factory(User::class, 40)->create();

        Menu::truncate();
        $hdr = Menu::create([
            'menu_name' => null,
            'display' => 'Master',
            'url' => '#',
            'icon' => 'fa-dashboard',
            'isparent' => '1',
            'parent_id' => null,
            'index' => '1',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        Menu::create([
            'menu_name' => 'User',
            'display' => 'User',
            'url' => '/User',
            'icon' => 'fa-users',
            'isparent' => '0',
            'parent_id' => $hdr->id,
            'index' => '1',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        Menu::create([
            'menu_name' => 'Menu',
            'display' => 'Menu',
            'url' => '/Menu',
            'icon' => 'fa-menu',
            'isparent' => '0',
            'parent_id' => $hdr->id,
            'index' => '2',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        Menu::create([
            'menu_name' => 'Jabatan',
            'display' => 'Jabatan',
            'url' => '/Jabatan',
            'icon' => 'fa-groups',
            'isparent' => '0',
            'parent_id' => $hdr->id,
            'index' => '3',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $hdr2 = Menu::create([
            'menu_name' => null,
            'display' => 'Surat',
            'url' => '#',
            'icon' => 'fa-dashboard',
            'isparent' => '1',
            'parent_id' => null,
            'index' => '2',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        Menu::create([
            'menu_name' => 'SuratMasuk',
            'display' => 'Surat Masuk',
            'url' => '/suratMasuk',
            'icon' => 'fa-groups',
            'isparent' => '0',
            'parent_id' => $hdr2->id,
            'index' => '1',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        Menu::create([
            'menu_name' => 'SuratKeluar',
            'display' => 'Surat Keluar',
            'url' => '/suratKeluar',
            'icon' => 'fa-groups',
            'isparent' => '0',
            'parent_id' => $hdr2->id,
            'index' => '2',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
    }
}
