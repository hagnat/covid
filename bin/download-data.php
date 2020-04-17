<?php

define('ROOT_DIR', __DIR__ . '/..');

$inputFile = ROOT_DIR . '/var/input/current.csv';
$archiveFile = ROOT_DIR . sprintf("/var/input/%s-brasil-covid-data.csv", date('Y-m-d'));

if (date('Y-m-d H', filemtime($inputFile)) == date('Y-m-d H')) {
	die ("  File was recently updated. No need to download it again.\n");
}

echo "  Downloading current data...\n";

$headers = array(
    'X-Parse-Application-Id: unAFkcaNDeXajurGB7LChj8SgQYS2ptm',
    'TE: Trailers',
);

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'https://xx9p7hp1p7.execute-api.us-east-1.amazonaws.com/prod/PortalGeral',
    CURLOPT_HTTPHEADER => $headers
]);
$result = curl_exec($curl);

echo "  Parsing data to CSV format...\n";

$contents = json_decode($result, true);
$data = file_get_contents($contents['results']['0']['arquivo']['url']);

echo "  Saving CSV data to input files...\n";
@unlink($inputFile);
file_put_contents($inputFile, $data);
file_put_contents($archiveFile, $data);

echo "  Download complete!\n";