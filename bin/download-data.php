<?php

define('ROOT_DIR', realpath(__DIR__ . '/..'));

$currentFile = ROOT_DIR . '/var/input/current.csv';
$archiveFile = ROOT_DIR . sprintf("/var/input/%s-brasil-covid-data.csv", date('Y-m-d'));

if (file_exists($currentFile) && date('Y-m-d H', filemtime($currentFile)) == date('Y-m-d H')) {
    die ("   File was recently updated. No need to download it again.\n");
}

echo "   Downloading current data...\n";
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

echo "   Parsing data to CSV format...\n";
$contents = json_decode($result, true);
$data = file_get_contents($contents['results']['0']['arquivo']['url']);

echo "   Saving CSV data to archive file...\n";
file_put_contents($archiveFile, $data);

echo "   Updating current.csv symlink...";
@unlink($currentFile);
symlink ($archiveFile, $currentFile);

echo "   Download complete!\n";
