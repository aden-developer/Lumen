<?php
namespace App\Http\Controllers;

use \MrShan0\CryptoLib\CryptoLib;
use Carbon\Carbon;
use App\Helpers\IPDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Model\ModelMember;

class Token extends Controller
{

    public function model() {
        return dd(ModelMember::all());
    }

    //Request pembuatan Token
    public function get(request $request) {

        // return Response()->json([
        //     getenv('DB_DATABASE'),
        //     'sf'
        // ], 200);
        /**
         * @Time
         * Seting time berdasarkan Timezone dan Timestamp
         */
        $ipdetail = new IPDetail();
        $timezone = $ipdetail->detail($request->ip());
        $carbonTime = new Carbon();
        $carbonTime->setTimezone($timezone['timezone']);
        $carbonTime->addDay('2');
        $timestamp = $carbonTime->timestamp;

        /**
         * @Method
         * Block akses jika metode parsing data tidak bertipekan POST
        */
        if(!$request->isMethod('post')) {
            return response()->json([
                'message' => 'Method Not Allowed !',
                'status' => false,
            ],405);
        }

        /**
         * @Validator
         * Validasi data parsing yang diperlukan
         */
        $validator = Validator::make($request->all(), [
            'api_username' => 'required|regex:/^([A-Za-z\-])+$/',
            'api_password' => 'required|regex:/^([A-Za-z0-9])+$/',
            'api_app' => 'required|regex:/^([A-Za-z0-9])+$/',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Bad request !',
                'status' => false
            ], 403);
        }

        /**
         * @Database*oa_otorisasi
         * Operasikan data untuk keperluan pembuatan Token
         */
        $OA_Otorisasi = OA_Otorisasi::
        where('nama_pengguna_api', '=', $request->api_username)
        ->where('otorisasi_password', '=', $request->api_password)
        ->where('otorisasi_aplikasi', '=', $request->api_app);
        $OA_Otorisasi_exists = $OA_Otorisasi->exists();
        $OA_Otorisasi = $OA_Otorisasi->first();

        if(!$OA_Otorisasi_exists) {
            return response()->json([
                'message' => 'API Not found !',
                'status' => false
            ],404);
        }
        /***
         * Api server dalam sedang perbaikan
        */
        if($OA_Otorisasi->otorisasi_perbaikan_api == 1) {
            return response()->json([
                'message' => 'Mohon maaf, Server dalam masa perbaikan !',
                'status' => false
            ], 403);
        }

        /**
         * @Random Character
         * Membuat random character untuk keperluan pembuatan token
         * berdasarkan keperluan 1 Day
         */
        $random10Character = '0123456789'.
                            'abcdefghijklmnopqrstuvwxyz'.
                            'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
                            '!@#$%^&*()_+{}[];,./?><';
        $random10 = substr(
                str_shuffle(
                    str_repeat(
                        $random10Character,
                        ceil(10/strlen($random10Character)))),1,
                        10);

        $token = md5($timestamp.
                    $random10.
                    $OA_Otorisasi->otorisasi_kunci.
                    openssl_random_pseudo_bytes(30).
                    str_replace(
                        [' ','.'],'',date('YmdHis').microtime()
                    )
                );

        /**
         * @Save Data to Database*oa_autentikasi
         * Simppan data token pada database dan device
         */
        $createToken = OA_Autentikasi::create([
            'autentikasi_token' => $token,
            'autentikasi_kadaluarsa' => $carbonTime,
            'otorisasi_kunci' => $OA_Otorisasi->otorisasi_kunci,
        ]);

        /**
         * @Enkripsi
         * Data Token dienkripsi dengan AES encyrpt
        */
        $encryption = new CryptoLib();
        $token = $encryption->encryptPlainTextWithRandomIV(
            $token,
            $OA_Otorisasi->otorisasi_aplikasi
        );

        return response()->json([
            "message" => "Ok",
            "status" => true,
            "output" => [
                "token" => $token,
                "expire" => $carbonTime,
                "timestamp" => $timestamp,
            ]
        ], 200);
    }

    //Request Check Token
    public function auth(Request $request) {
        /**
         * @Validator
         * Validasi data parsing yang diperlukan
         */
        $validator = Validator::make($request->all(), [
            'api_username' => 'required',
            'api_address' => 'required',
            'api_token' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Bad request !',
                'status' => false
            ], 403);
        }

        /**
         * @Database*oa_otorisasi
         * Operasikan data untuk keperluan check otorisasi
         */
        $OA_Otorisasi = OA_Otorisasi::
        where('nama_pengguna_api', '=', $request->api_username)
        ->where('otorisasi_alamat', '=', $request->api_address);
        $OA_Otorisasi_exists = $OA_Otorisasi->exists();
        $OA_Otorisasi = $OA_Otorisasi->first();

        if(!$OA_Otorisasi_exists) {
            return response()->json([
                'message' => 'User API Not found !',
                'status' => false
            ],404);
        }

        /***
         * Api server dalam sedang perbaikan
        */
        if($OA_Otorisasi->otorisasi_perbaikan_api == 1) {
            return response()->json([
                'message' => 'Mohon maaf, Server dalam masa perbaikan !',
                'status' => false
            ], 403);
        }

        /**
         * @Deskripsi
         * Data User Key dideskripsi dengan AES encyrpt
        */
        $decryption = new CryptoLib();
        $userToken = $decryption->decryptCipherTextWithRandomIV(
            $request->api_token,
            $OA_Otorisasi->otorisasi_aplikasi
        );

        $token = $request->api_token;

        if($userToken != null) { $token = $userToken; }

        /**
         * @Database*oa_autentikasi
         * Operasikan data untuk keperluan chek autentikasi
         */
        $OA_Autentikasi = OA_Autentikasi::
        where('autentikasi_token', '=', $token)
        ->where('otorisasi_kunci', '=', $OA_Otorisasi->otorisasi_kunci);
        $OA_Autentikasi_exists = $OA_Autentikasi->exists();
        $OA_Autentikasi = $OA_Autentikasi->first();

        if(!$OA_Autentikasi_exists) {
            return response()->json([
                'message' => 'Unauthorized !',
                'status' => false
            ],401);
        }

        return Response()->json([
            'message' => 'Ok, Token authorized',
            'status' => true,
            'output' => [
                'token' => $request->api_token,
                'expire' => $OA_Autentikasi->autentikasi_kadaluarsa,
                'timestamp' => strtotime(
                    $OA_Autentikasi->autentikasi_kadaluarsa
                ),
            ]
        ],200);
    }
}
