<?php
namespace App\Helpers;

class Helper
{
  public static $responses = array( 'state_code' => 500, 'success' => false, 'messages' => array(), 'data' => Array());

  public static function mapFilter($req){
    $filter = new \stdClass();

    $filter->limit = $req->input('take') !== null ? $req->input('take') : 10 ;
    $filter->offset = $req->input('skip') !== null ? $req->input('skip') : 0;

    // Sort columns.
    $filter->sortColumns = array();
    $orderColumns = $req->input('sort') != null ? $req->input('sort') : array();
    if ($orderColumns){
        $orderParse = json_decode($orderColumns, true);
        $filterColumn = new \stdClass();
        $filterColumn->column = $orderParse[0]['selector'];
        $filterColumn->order = $orderParse[0]['desc'] == true ? 'DESC' : 'ASC';
        array_push($filter->sortColumns, $filterColumn);
    }

    //Search Column
    $filter->search = $req->input('filter') != null ? json_decode($req->input('filter'), true) : array();
    
    return $filter;
  }

  public static function prepareFile($inputs, $subFolder)
  {
    $file = new \StdClass;
    try {
      $file = isset($inputs['file']) ? $inputs['file'] : null;
      $file->path = base_path() . $subFolder;
      $file->newName = time()."_".$file->getClientOriginalName();
      $file->originalName = explode('.',$file->getClientOriginalName())[0];
      $file->move($file->path ,$file->newName);
    } catch (Exception $e){
        // supress
    }
    return $file;
  }

  public static function prepFile($file, $subFolder)
  {
    $newFile = new \StdClass;
    try {
      $newFile->path = base_path() . $subFolder;
      $newFile->newName = time()."_".$file->getClientOriginalName();
      $newFile->originalName = explode('.',$file->getClientOriginalName())[0];
      $file->move($newFile->path ,$newFile->newName);
    } catch (Exception $e){
        // supress
    }
    return $newFile;
  }

  public static function createCertificate($info)
  {
    $rootdir=base_path() .'/stack';
    $certdir=$rootdir;
    $name=$info['name'];

    $infoMail=$info['email'];

    if( $name ){
      function create( $rootdir='c:/wwwroot', $certdir=false, $certname='certificate', $dn=array(), $passphrase=null, $createpem=true, $overwrite=true, $infoMail=null ){
        if( !empty( $certdir ) && !empty( $certname ) && !empty( $dn ) ){

          $out = new \stdClass;
          $days = 365;

          /* !!! configuration and location of your own openssl.conf file is important !!! */
          putenv( sprintf( 'OPENSSL_CONF=%s/openssl.cnf', $rootdir ) );

          $config=array(
            'config'            =>  'C:\xampp\php\extras\openssl\openssl.cnf',
            'digest_alg'        =>  'AES-128-CBC',
            'private_key_bits'  =>  4096,
            'private_key_type'  =>  OPENSSL_KEYTYPE_RSA,
            'encrypt_key'       =>  false
          );

          /*
              Primary configuration is overwritten at runtime
              by including parameters in the "$dn" argument
          */
          $dn=array_merge( array(
            "countryName"               => "ID",
            "stateOrProvinceName"       => "Jambi",
            "localityName"              => "Indonesia",
            "organizationName"          => "ratafd.xyz",
            "organizationalUnitName"    => "Dikjar Kerinci",
            "commonName"                => $certname,
            "emailAddress"              => $infoMail
          ), $dn );

          $privkey = openssl_pkey_new( $config );
          $csr = openssl_csr_new( $dn, $privkey, $config );
          $cert = openssl_csr_sign( $csr, null, $privkey, $days, $config, 0 );

          openssl_x509_export( $cert, $out->pub );
          openssl_pkey_export( $privkey, $out->priv, $passphrase );
          openssl_csr_export( $csr, $out->csr );

          # Create the base private & public directories if they do not exist
          $privdir = $certdir . '\\certificates\\private\\';
          $pubdir  = $certdir .'\\certificates\\public\\';

          @mkdir( $privdir, 0777, true );
          @mkdir( $pubdir, 0777, true );


          $pkpath=$privdir . $certname . '.key';
          $cert=$pubdir . $certname . '.crt';
          //$csr=$privdir . $certname . '.csr';
          //$pem=$pubdir . $certname . '.pem';


          if( !file_exists( $pkpath ) or ( $overwrite==true && file_exists( $pkpath ) ) ) {

            openssl_pkey_export_to_file( $privkey, $pkpath, $passphrase, $config );

            file_put_contents( $cert, $out->pub, FILE_TEXT );
          //  file_put_contents( $csr, $out->csr, FILE_TEXT );

            // if( $createpem ){
            //     unset( $out->csr );
            //     file_put_contents( $pem, implode( '', get_object_vars( $out ) ) );
            // }
          }
          openssl_pkey_free( $privkey );
          clearstatcache();

          /* return useful stuff for display porpoises */
          return (object)array(
            'public'        =>  str_replace( $certdir, '', $pubdir ),
            'private'       =>  str_replace( $certdir, '', $privdir ),
            'pubsize'       =>  filesize( $cert ),
            'privsize'      =>  filesize( $pkpath ),
            'pkpath'        =>  $pkpath,
            'cert'          =>  $cert,
            // 'csr'           =>  $csr,
            //'pem'           =>  $pem,
            'certificate'   =>  $certname
          );
        }
        return false;
      }

      /* configure the DN array */
      $dn=array(
        'countryName'               =>  'ID',
        'localityName'              =>  'Indonesia',
        'stateOrProvinceName'       =>  'Jambi',
        'organizationName'          =>  $name,
        'organizationalUnitName'    =>  'Dikjar Kerinci',
        'emailAddress'              =>  $infoMail
      );
      $result=create( $rootdir, $certdir, $name, $dn );
    }
  }
}