<?php

namespace App\Controller;

use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;
use Aws\Iam\IamClient;
use App\Entity\User;
use Aws\S3\S3Client;
use Crypt_GPG;
use Crypt_GPG_BadPassphraseException;
use Crypt_GPG_Exception;
use Crypt_GPG_KeyNotFoundException;
use Crypt_GPG_NoDataException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{

    /**
     * @Route("dashboard", name="dashboard")
     */
    public function index(): Response
    {

        $sharedConfig = [
            'region' => 'eu-west-3',
            'version' => 'latest'
        ];

        $s3 = new S3Client($sharedConfig);
        $bucketName = 'espace-partage-epsi-i1-dev';

        $files = $s3->listObjects(['Bucket' => $bucketName]);
        $file = array();

        for($i=0;$i<count($files['Contents']);$i++){
            $file[] = ([
                'titre' => $files['Contents'][$i]['Key'],
                'date' => $files['Contents'][$i]['LastModified']
            ]);
        }

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'file' => $file,
            'users' => $users
        ]);

    }

    /**
     * @Route("dashboard/createFile", name="dashboard_createfile")
     * @param Request $request
     * @return Response
     */
    public function createFile(Request $request, InputInterface $input, OutputInterface $output): Response
    {
        $gpg = new Crypt_GPG();

        $sharedConfig = [
            'region' => 'eu-west-3',
            'version' => 'latest'
        ];
        $s3 = new S3Client($sharedConfig);
        
        $bucketName = 'espace-partage-epsi-i1-dev';
        
        // Je récupère le fichier uploade
        $json_file = $request->files->get('fileToUpload');
        // Je récupère l'extension du fichier
        // Ajout du nom du fichier dans l'objet File
        $fichier = $json_file->getClientOriginalName();
        $url_fichier = $json_file->getPathName();

        $s3->putObject([
            'Bucket' => $bucketName,
            'Key' => $fichier,
            'SourceFile' => $url_fichier
        ]);

        return $this->redirectToRoute('dashboard');

    }

    /**
     * @param $csv
     * @return mixed
     * @throws Crypt_GPG_BadPassphraseException
     * @throws Crypt_GPG_Exception
     * @throws Crypt_GPG_KeyNotFoundException
     * @throws Crypt_GPG_NoDataException
     */
    public function csvEncryption($csv)
    {
        $publicKey = file_get_contents("/var/www/html/keys/client_public_key.txt");
        // ou récupération de la clé publique depuis un serveur AWS S3 par exemple.
        $gpg = new Crypt_GPG();
        $info = $gpg->importKey($publicKey);
        $gpg->addDecryptKey($info[ 'fingerprint' ]);
        $encryptedCsv = $gpg->encrypt($csv);

        return $encryptedCsv;
    }

    /**
     * @param $encryptedCsv
     * @return string
     * @throws Crypt_GPG_BadPassphraseException
     * @throws Crypt_GPG_Exception
     * @throws Crypt_GPG_KeyNotFoundException
     * @throws Crypt_GPG_NoDataException
     */
    public function csvDecryption($encryptedCsv)
    {
        $this->logger->info('Déchiffrement');
        $privateKey = file_get_contents("/var/www/html/keys/client_private_key.txt");
        // ou récupération de la clé privée depuis un serveur AWS S3 par exemple.
        $gpg = new Crypt_GPG();
        $info = $gpg->importKey($privateKey);
        $gpg->addDecryptKey($info[ 'fingerprint' ], "azerty123"); //azerty123 = passphrase
        $decryptedCsv = $gpg->decrypt($encryptedCsv);

        return $decryptedCsv;
    }

}
