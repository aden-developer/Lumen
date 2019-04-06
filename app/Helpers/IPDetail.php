<?php
namespace app\Helpers;

class IPDetail {
    public function detail($ip = null) {
        return array(
            "as" =>"AS45320 Departemen Komunikasi dan Informasi Republik Indonesia",
            "country" =>"Indonesia",
            "countryCode" =>"ID",
            "city" =>"Jakarta",
            "regionName" =>"Jakarta",
            "region" =>"JK",
            "zip" =>"",
            "query" =>"172.0.0",
            "isp" =>"KOMINFO",
            "lat" => "-6.1818",
            "lon" => "106.8223",
            "org" =>"",
            "timezone" =>"Asia/Jakarta",
            "status" =>"success",
        );

        if(empty($ip)) {
            return null;
        } else {
            if($ip == '::1' || $ip == '172.0.01') {
                //Kode nanti dipindahkan kesini yaaa
            }
            $ch =       curl_init('http://ip-api.com/json/'.$ip);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result =   curl_exec($ch);
            curl_close($ch);
            return $result['status'];
        }
    }
}