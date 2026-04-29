<?php
header('Content-Type: application/json'); 
header('Access-Control-Allow-Origin: *');

$url = "https://webapi.bps.go.id/v1/api/domain/type/all/prov/00000/key/5fac61ddd743d80db3867d22d4baf88c/";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $httpCode !== 200) {
    echo json_encode(["error" => "Gagal mengambil data provinsi"]);
    exit;
}

echo $response;
?>