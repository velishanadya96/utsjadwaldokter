<?php
header('Content-Type: application/json'); 
header('Access-Control-Allow-Origin: *');

$url = "https://webapi.bps.go.id/v1/api/domain/type/all/prov/00000/key/5fac61ddd743d80db3867d22d4baf88c/";

$response = @file_get_contents($url);

if ($response === FALSE) {
    echo json_encode(["error" => "Gagal mengambil data"]);
    exit;
}

echo $response;
?>