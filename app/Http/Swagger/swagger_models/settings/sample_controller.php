<?php

class SampleController //This is a sample laravel Controller

{

    ////////////////////////////////////////////
    ///******** AUTH **********////
    ////////////////////////////////////////////

    /////////////// LOGIN
    /**
     * @OA\POST(
     *     path="/spnbackend/public/api/login",
     *     tags={"Auth"},
     *     summary="Loginnya kena ERROR",
     *     operationId="createUsersWithListInput",
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
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
     *     path="/spnbackend/public/api/jabatan/view/{id}",
     *     tags={"Jabatan"}, 
     *     summary="Detail Jabatan",
     *     description="Jabatan By ID",
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
     *     path="/spnbackend/public/api/jabatan/save/{id?}",
     *     tags={"Jabatan"}, 
     *     summary="Tambah atau edit Jabatan",
     *     description="Tambah atau edit Jabatan",
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
     *     )
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/jabatan/delete/{id}",
     *     tags={"Jabatan"}, 
     *     summary="Hapus Jabatan",
     *     description="Hapus Jabatan",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
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
     *     path="/spnbackend/public/api/user/view/{id}",
     *     tags={"User"}, 
     *     summary="Detail User",
     *     description="User By ID",
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
     *     path="/spnbackend/public/api/user/save/{id?}",
     *     tags={"User"}, 
     *     summary="Tambah atau edit User",
     *     description="Tambah atau edit User",
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
     *     )
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/user/delete/{id}",
     *     tags={"User"}, 
     *     summary="Hapus User",
     *     description="Hapus User",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
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
     *     path="/spnbackend/public/api/Menu/view/{id}",
     *     tags={"Menu"}, 
     *     summary="Detail Menu",
     *     description="Menu By ID",
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
     *     path="/spnbackend/public/api/menu/save/{id?}",
     *     tags={"Menu"}, 
     *     summary="Tambah atau edit Menu",
     *     description="Tambah atau edit Menu",
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
     *     )
     * )
     */

     /**
     * @OA\Post(
     *     path="/spnbackend/public/api/menu/delete/{id}",
     *     tags={"Menu"}, 
     *     summary="Hapus Menu",
     *     description="Hapus Menu",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */
}

