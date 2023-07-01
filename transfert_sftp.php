<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\Common\PublicKey;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dirPochetteSucre   = $_ENV['TARGET'];
$dirPochetteGDC     = $_ENV['SOURCE'];
$gdcHost            = $_ENV['HOST'];
$sftpUser           = $_ENV['SFTP_USER'];
$sftpKeyPath        = $_ENV['PATH_TO_SFTP_KEY'];

// Ouvre la connexion SFTP par vérification de clés ssh et avec un port autre que celui par défaut
try {
    $sftp = new SFTP($gdcHost, 2222);
    $key = PublicKeyLoader::load(file_get_contents($sftpKeyPath));

    // Vérification du type de clé ssh : on s'attend à une clé privée
    echo $keyType = $key instanceof PublicKey ? "clé publique \n" : ($key instanceof PrivateKey ? "clé privée \n" : "Aucune clé valide !\n");

    if (!$sftp->login($sftpUser, $key)) {
        throw new Exception('Impossible de se connecter au serveur !');
    }
    $sftp->enableDatePreservation();
    $totalLoadTime = 0;
    $sftp->chdir('download');
    $files = $sftp->nlist();
    foreach ($files as $file) {
        if (!$sftp->is_dir($file)) {
            $loadTime = 0;
            $filename = basename($file);
            $startTime = microtime(true);
            $sftp->get($dirPochetteGDC . '/' . $filename, $dirPochetteSucre . '/' . $filename);
            $endTime = microtime(true);
            $loadTime = $endTime - $startTime;
            $totalLoadTime += $loadTime;
        }
    }
    try {
        echo 'Temps de chargement : ' . round($totalLoadTime, 2) . ' seconde(s).';
    } catch (Exception $e) {
        echo sprintf('Problème avec le calcul du temps : %s', $e->getMessage());
    }
}catch (Exception $e) {
    exit(sprintf('La tentative de connexion SFTP a échoué : %s', $e->getMessage()));
}



