<?php

class SampleController //This is a sample laravel Controller

{

    ////////////////////////////////////////////
    ///******** AUTH **********////
    ////////////////////////////////////////////

    /////////////// LOGIN
    /**
     * @OA\Post(
     *     path="/spnbackend/public/api/login",
     *     tags={"Auth"},
     *     summary="",
     *     operationId="id",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 example={"email": "admin@admin.com", "password": "********"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/logout",
     *     tags={"Auth"},
     *     summary="",
     *     operationId="id",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */

     /////////////// Unit
    /**
     * @OA\GET(
     *     path="/spnbackend/public/api/unit/list",
     *     tags={"Unit"}, 
     *     summary="List Unit",
     *     description="List Unit",
     *     operationId="auth",
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    /**
     * @OA\GET(
     *     path="/spnbackend/public/api/unit/view",
     *     tags={"Unit"}, 
     *     summary="Detail Unit",
     *     description="Unit By ID",
     *     operationId="auth",
     *     @OA\Parameter(
     *          name="id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    /**
     * @OA\Post(
     *     path="/spnbackend/public/api/unit/save",
     *     tags={"Unit"}, 
     *     summary="Tambah atau edit Unit",
     *     description="Tambah atau edit Unit",
     *     @OA\Parameter(
     *          name="id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="group_name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="group_code",
     *                     type="string"
     *                 ),
     *                 example={"group_name": "Ikhwan Komputer", "group_code": "IKE"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/unit/delete",
     *     tags={"Unit"}, 
     *     summary="Hapus Unit",
     *     description="Hapus Unit",
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/unit/search",
     *     tags={"Unit"}, 
     *     summary="Hapus Unit",
     *     description="Hapus Unit",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /////////////// Jabatan
    /**
     * @OA\GET(
     *     path="/spnbackend/public/api/jabatan/list",
     *     tags={"Jabatan"}, 
     *     summary="List Jabatan",
     *     description="List Jabatan",
     *     operationId="auth",
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    /**
     * @OA\GET(
     *     path="/spnbackend/public/api/jabatan/view",
     *     tags={"Jabatan"}, 
     *     summary="Detail Jabatan",
     *     description="Jabatan By ID",
     *     operationId="auth",
     *     @OA\Parameter(
     *          name="id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    /**
     * @OA\Post(
     *     path="/spnbackend/public/api/jabatan/save",
     *     tags={"Jabatan"}, 
     *     summary="Tambah atau edit Jabatan",
     *     description="Tambah atau edit Jabatan",
     *     @OA\Parameter(
     *          name="id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="group_id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="position_name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="position_type",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="detail",
     *                     type="string"
     *                 ),
     *                 example={"group_id": "1", "position_name": "Kepala Dinas", "position_type": "Gol. III", "detail": "Tanggung Jawab Untuk"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/jabatan/delete",
     *     tags={"Jabatan"}, 
     *     summary="Hapus Jabatan",
     *     description="Hapus Jabatan",
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/jabatan/search",
     *     tags={"Jabatan"}, 
     *     summary="Cari Jabatan",
     *     description="Cari Jabatan",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /////////////// USER
    /**
     * @OA\GET(
     *     path="/spnbackend/public/api/user/list",
     *     tags={"User"}, 
     *     summary="List User",
     *     description="List User",
     *     operationId="auth",
     *     @OA\Parameter(
     *          name="skip",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="take",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    /**
     * @OA\GET(
     *     path="/spnbackend/public/api/user/view",
     *     tags={"User"}, 
     *     summary="Detail User",
     *     description="Detail ID",
     *     operationId="auth",
     *     @OA\Parameter(
     *          name="id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    /**
     * @OA\Post(
     *     path="/spnbackend/public/api/user/save",
     *     tags={"User"}, 
     *     summary="Tambah atau edit User",
     *     description="Tambah atau edit User",
     *     @OA\Parameter(
     *          name="id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="username",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="full_name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="address",
     *                     type="string"
     *                 ),
     *                 example={"username": "Simba", 
     *                      "full_name": "Simba The Cat", 
     *                      "email": "simba@thecat.dev.id", 
     *                      "password": "*****", 
     *                      "phone": "084212312",
     *                      "address": "Jl. Ridwan Rais Depok, Jawa Barat"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     ///Ubah Password
     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/user/changePassword",
     *     tags={"User"}, 
     *     summary="Ubah Password User",
     *     description="Ubah Password User dari ID",
     *     @OA\Parameter(
     *          name="id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 example={"password": "*****"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/user/delete",
     *     tags={"User"}, 
     *     summary="Hapus User",
     *     description="Hapus User",
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/user/search",
     *     tags={"User"}, 
     *     summary="Cari User",
     *     description="Cari User",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/user/uploadPhoto/1",
     *     tags={"User"}, 
     *     summary="Upload poto User",
     *     description="upload poto User",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /////////////// MENU
    /**
     * @OA\GET(
     *     path="/spnbackend/public/api/menu/list",
     *     tags={"Menu"}, 
     *     summary="List Menu",
     *     description="List Menu",
     *     operationId="auth",
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    /**
     * @OA\GET(
     *     path="/spnbackend/public/api/Menu/view",
     *     tags={"Menu"}, 
     *     summary="Detail Menu",
     *     description="Menu By ID",
     *     operationId="auth",
     *     @OA\Parameter(
     *          name="id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     //Menu SideBar
     /**
     * @OA\GET(
     *     path="/spnbackend/public/api/Menu/sidebar",
     *     tags={"Menu"}, 
     *     summary="Sidebar Nav",
     *     description="Menu Sidebar",
     *     operationId="auth",
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    /**
     * @OA\Post(
     *     path="/spnbackend/public/api/menu/save",
     *     tags={"Menu"}, 
     *     summary="Tambah atau edit Menu",
     *     description="Tambah atau edit Menu",
     *     @OA\Parameter(
     *          name="id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="menu_name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="display",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="url",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="icon",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="isparent",
     *                     type="boolean"
     *                 ),
     *                 @OA\Property(
     *                     property="parent_id",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="index",
     *                     type="integer"
     *                 ),
     *                 example={"menu_name": "menu", 
     *                      "display": "Menu", 
     *                      "url": "/Menu", 
     *                      "icon": "fa-menu", 
     *                      "isparent": true,
     *                      "parent_id": null,
     *                      "index": 1}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/menu/delete",
     *     tags={"Menu"}, 
     *     summary="Hapus Menu",
     *     description="Hapus Menu",
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */


    //Surat Keluar

    /**
     * @OA\Post(
     *     path="/spnbackend/public/api/suratKeluar/save",
     *     tags={"Surat Keluar"}, 
     *     summary="Tambah atau edit Surat Keluar",
     *     description="Tambah atau edit Surat Keluar",
     *     @OA\Parameter(
     *          name="id",
     *          description="Project id",
     *          required=false,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="jenis_surat",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="klasifikasi_surat",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="sifat_surat",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="tujuan_surat",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="hal_surat",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="lampiran_surat",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="approval_user",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="to_user",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="file",
     *                     type="file"
     *                 ),
     *                 example={
     *                      "jenis_surat": "Penting", 
     *                      "klasifikasi_surat": "IT", 
     *                      "sifat_surat": "Segera", 
     *                      "tujuan_surat": "Ikhwan Komputer",
     *                      "hal_surat": "-",
     *                      "lampiran_surat": "-",
     *                      "approval_user": 1,
     *                      "to_user": 2,
     *                      "file": "file"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/suratKeluar/disposisi",
     *     tags={"Surat Keluar"}, 
     *     summary="Tambah atau edit Surat Keluar",
     *     description="Tambah atau edit Surat Keluar",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="surat_keluar_id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="tujuan_user",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="file_id",
     *                     type="file"
     *                 ),
     *                 @OA\Property(
     *                     property="keterangan",
     *                     type="string"
     *                 ),
     *                 example={ 
     *                      "surat_keluar_id": "3", 
     *                      "tujuan_user": "1", 
     *                      "file_id": "[]", 
     *                      "keterangan": "Mohon Respon"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/suratKeluar/read",
     *     tags={"Surat Keluar"}, 
     *     summary="Ubah status baca disposisi",
     *     description="ubah Status Baca Disposisi",
     *     @OA\Parameter(
     *          name="idDisposisi",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
}

