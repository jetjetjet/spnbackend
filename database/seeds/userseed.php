<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Model\Menu;
use App\Model\Group;
use App\Model\PositionMenu;
use App\Model\Position;
use App\Model\KlasifikasiSurat;
use App\Model\TipeSurat;
use App\Model\SifatSurat;

class Userseed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipeSurat::truncate();
        SifatSurat::truncate();
        $tipe = Array(
            'Surat Keputusan',
            'Surat Pemberitahuan',
            'Surat Undangan',
            'Surat Kuasa',
            'Surat Keterangan',
            'Surat Memo atau Nota Dinas',
            'Surat Edaran',
            'Surat Penangar',
            'Surat Perintah',
            'Surat Instruksi',
            'Surat Tugas'
        );

        for($i = 0; $i < count($tipe); $i++){
            $t = TipeSurat::create([
                'tipe_surat' => $tipe[$i],
                'active' => '1',
                'created_at' => now()->toDateTimeString(),
                'created_by' => '1'
            ]);
        }

        $sifat = Array(
            'Umum',
            'Rahasia',
            'Biasa'
        );

        for($i = 0; $i < count($sifat); $i++){
            $t = SifatSurat::create([
                'sifat_surat' => $sifat[$i],
                'active' => '1',
                'created_at' => now()->toDateTimeString(),
                'created_by' => '1'
            ]);
        }

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

        KlasifikasiSurat::truncate();

        $kl1 = KlasifikasiSurat::create([
            'kode_klasifikasi' => '000',
            'nama_klasifikasi' => 'Umum',
            'detail' => 'Umum',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $kl2 = KlasifikasiSurat::create([
            'kode_klasifikasi' => '010',
            'nama_klasifikasi' => 'Urusan Dalam',
            'detail' => 'Urusan Dalam',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        KlasifikasiSurat::create([
            'kode_klasifikasi' => '010',
            'nama_klasifikasi' => 'Urusan Dalam',
            'detail' => 'Urusan Dalam',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        KlasifikasiSurat::create([
            'kode_klasifikasi' => '030',
            'nama_klasifikasi' => 'Kekayaan Daerah',
            'detail' => 'Kekayaan Daerah',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        KlasifikasiSurat::create([
            'kode_klasifikasi' => '900',
            'nama_klasifikasi' => 'Keuangan',
            'detail' => 'Keuangan',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        //factory(User::class, 40)->create();

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
        PositionMenu::truncate();

        $g1 = Group::create([
            'group_code' => 'SA',
            'group_name' => 'Admin Aplikasi',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $kadin = Group::create([
            'group_code' => 'KADIN',
            'group_name' => 'Kepala Dinas',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $sekre = Group::create([
            'group_code' => 'SKR',
            'group_name' => 'Sekretaris',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $subumum = Group::create([
            'group_code' => 'UMUM',
            'group_name' => 'Umum dan Kepegawaian',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $subpep = Group::create([
            'group_code' => 'PEP',
            'group_name' => 'Perencanaan, Evaluasi dan Pelaporan',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $subkeu = Group::create([
            'group_code' => 'KEU',
            'group_name' => 'Keuangan',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $BPD = Group::create([
            'group_code' => 'DIKDAS',
            'group_name' => 'Bidang Pendidikan Dasar',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $BGTK = Group::create([
            'group_code' => 'GTK',
            'group_name' => 'Bidang GTK',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $BPAUD = Group::create([
            'group_code' => 'PAUD',
            'group_name' => 'Bidang PAUD',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $BSP = Group::create([
            'group_code' => 'SARPRAS',
            'group_name' => 'Bidang Sarana Prasarana',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $p1 = Position::create([
            'group_id' => $g1->id,
            'position_name' => 'Super Admin',
            'position_type' => 'SA',
            'is_parent' => '1',
            'parent_id' => null,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $kpl = Position::create([
            'group_id' => $kadin->id,
            'position_name' => 'Kepala Dinas',
            'position_type' => 'Gol. ',
            'is_parent' => '1',
            'parent_id' => null,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $sekretaris = Position::create([
            'group_id' => $sekre->id,
            'position_name' => 'Sekretaris',
            'position_type' => 'Gol. ',
            'is_parent' => '1',
            'parent_id' => null,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $sumum = Position::create([
            'group_id' => $subumum->id,
            'position_name' => 'Kasubbag Umum dan Kepegawaian',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $sekretaris->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $skeu = Position::create([
            'group_id' => $subkeu->id,
            'position_name' => 'Kasubbag Keuangan',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $sekretaris->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $spep = Position::create([
            'group_id' => $subpep->id,
            'position_name' => 'Perencanaan, Evaluasi dan Pelaporan',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $sekretaris->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kabpd = Position::create([
            'group_id' => $BPD->id,
            'position_name' => 'Kepala Bidang Pendidikan Dasar',
            'position_type' => 'Gol. ',
            'is_parent' => '1',
            'parent_id' => null,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasismp = Position::create([
            'group_id' => $BPD->id,
            'position_name' => 'Kasi Kurikulum SMP',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kabpd->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasisd = Position::create([
            'group_id' => $BPD->id,
            'position_name' => 'Kasi Kurikulum SD',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kabpd->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasipmp = Position::create([
            'group_id' => $BPD->id,
            'position_name' => 'Kasi Penjaminan Mutu Pendidikan',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kabpd->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kagtk = Position::create([
            'group_id' => $BGTK->id,
            'position_name' => 'Kepala Bidang GTK',
            'position_type' => 'Gol. ',
            'is_parent' => '1',
            'parent_id' => null,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasik = Position::create([
            'group_id' => $BGTK->id,
            'position_name' => 'Kasi Kesejahteraan',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kagtk->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasisdm = Position::create([
            'group_id' => $BGTK->id,
            'position_name' => 'Kasi SDM',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kagtk->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasimp = Position::create([
            'group_id' => $BGTK->id,
            'position_name' => 'Kasi Mutasi Promosi',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kagtk->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kapaud = Position::create([
            'group_id' => $BPAUD->id,
            'position_name' => 'Kepala Bidang PAUD',
            'position_type' => 'Gol. ',
            'is_parent' => '1',
            'parent_id' => null,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasipaud = Position::create([
            'group_id' => $BPAUD->id,
            'position_name' => 'Kasi PAUD',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kapaud->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasiseja = Position::create([
            'group_id' => $BPAUD->id,
            'position_name' => 'Kasi Kesejahteraan',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kapaud->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasipm = Position::create([
            'group_id' => $BPAUD->id,
            'position_name' => 'Kasi Pendidikan Masyarakat',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kapaud->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kabsp = Position::create([
            'group_id' => $BSP->id,
            'position_name' => 'Kepala Bidang Sarana dan Prasarana',
            'position_type' => 'Gol. ',
            'is_parent' => '1',
            'parent_id' => null,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasissmp = Position::create([
            'group_id' => $BSP->id,
            'position_name' => 'Kasi Sarana SMP',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kabsp->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasissd = Position::create([
            'group_id' => $BSP->id,
            'position_name' => 'Kasi Sarana SD',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kabsp->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $kasispaud = Position::create([
            'group_id' => $BSP->id,
            'position_name' => 'Kasi Sarana PAUD',
            'position_type' => 'Gol. ',
            'is_parent' => '0',
            'parent_id' => $kabsp->id,
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $afif = User::create([
            'full_name' => 'Heriadian Afif',
            'position_id' => $kadin->id,
            'username' => 'h_afif',
            'email' => 'h_afif@ratafd.xyz',
            'jenis_kelamin' => 'Laki-laki',
            'nip' => '123456789012345678',
            'password' => bcrypt('password'),
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $septian = User::create([
            'full_name' => 'Septian Permana',
            'position_id' => $kapaud->id,
            'username' => 'septian_p',
            'email' => 'septian_p@ratafd.xyz',
            'jenis_kelamin' => 'Laki-laki',
            'nip' => '123456789087654321',
            'password' => bcrypt('password'),
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $yuda = User::create([
            'full_name' => 'Yudha Prasetyo',
            'position_id' => $sumum->id,
            'username' => 'yudha_p',
            'email' => 'yudha_p@ratafd.xyz',
            'jenis_kelamin' => 'Laki-laki',
            'nip' => '123456789056784321',
            'password' => bcrypt('password'),
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        $vera = User::create([
            'full_name' => 'Vera',
            'position_id' => $kasipaud->id,
            'username' => 'vera',
            'email' => 'vera@ratafd.xyz',
            'jenis_kelamin' => 'Perempuan',
            'nip' => '123456789056784390',
            'password' => bcrypt('password'),
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
        
        $evi = User::create([
            'full_name' => 'Evi Nurhayati',
            'position_id' => $sekretaris->id,
            'username' => 'evi_n',
            'email' => 'evi_n@ratafd.xyz',
            'jenis_kelamin' => 'Perempuan',
            'nip' => '123456789056634390',
            'password' => bcrypt('password'),
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        //afif
        $gpkadin = PositionMenu::create([
            'position_id' => $kadin->id,
            'menu_id' => '0',
            'permissions' => 'suratKeluar_list,suratKeluar_view,suratKeluar_sign,suratMasuk_list,suratMasuk_view,suratMasuk_disposition,suratMasuk_close',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        //yudha_p
        $gpkasubbag = PositionMenu::create([
            'position_id' => $sumum->id,
            'menu_id' => '0',
            'permissions' => 'suratKeluar_list,suratKeluar_view,suratMasuk_list,,suratKeluar_agenda,suratMasuk_view,suratMasuk_save,suratMasuk_delete,suratMasuk_disposition,suratMasuk_close',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        //evie
        $gpSekretaris = PositionMenu::create([
            'position_id' => $sekretaris->id,
            'menu_id' => '0',
            'permissions' => 'suratKeluar_list,suratKeluar_view,suratKeluar_verify,suratMasuk_list,suratMasuk_view,suratMasuk_disposition,suratMasuk_close',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        //septian
        $gpkapaud = PositionMenu::create([
            'position_id' => $kapaud->id,
            'menu_id' => '0',
            'permissions' => 'suratKeluar_list,suratKeluar_view,suratKeluar_approve,suratMasuk_list,suratMasuk_view,suratMasuk_disposition,suratMasuk_close',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        //vera
        $gpKasiPaud = PositionMenu::create([
            'position_id' => $kasipaud->id,
            'menu_id' => '0',
            'permissions' => 'suratKeluar_list,suratKeluar_view,suratKeluar_save,suratKeluar_delete,suratMasuk_list,suratMasuk_view',
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);
    }
}
