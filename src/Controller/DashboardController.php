<?php

namespace App\Controller;

use App\Entity\User;
use Aws\S3\S3Client;
use Crypt_GPG;
use Crypt_GPG_BadPassphraseException;
use Crypt_GPG_Exception;
use Crypt_GPG_FileException;
use Crypt_GPG_KeyNotFoundException;
use Crypt_GPG_NoDataException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            $date = date('d-m-y',strtotime($files['Contents'][$i]['LastModified']));
            $file[] = ([
                'titre' => $files['Contents'][$i]['Key'],
                'date' => $date
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
    public function createFile(Request $request): Response
    {

        $sharedConfig = [
            'region' => 'eu-west-3',
            'version' => 'latest'
        ];
        $s3 = new S3Client($sharedConfig);
        
        $bucketName = 'espace-partage-epsi-i1-dev';
        
        // Je récupère le fichier uploade
        $json_file = $request->files->get('fileToUpload');

        $fichier = $json_file->getClientOriginalName();
        $url_fichier = $json_file->getPathName();

        $content_file = file_get_contents($url_fichier);
        file_put_contents($fichier, $this->contentEncryption($content_file));

        $s3->putObject([
            'Bucket' => $bucketName,
            'Key' => $fichier,
            'SourceFile' => $url_fichier
        ]);

        return $this->redirectToRoute('dashboard');

    }

    /**
     * @Route("dashboard/download", name="dashboard_downloadfile")
     * @param Request $request
     */
    public function downloadFile(Request $request){

        $sharedConfig = [
            'region' => 'eu-west-3',
            'version' => 'latest'
        ];

        $s3 = new S3Client($sharedConfig);

        $bucketName = 'espace-partage-epsi-i1-dev';
        $file =  $request->get('file');
        $saveAs = dirname(__DIR__).'FileBucket';

        $fileBucket = $s3->getObject([
            'Bucket' => $bucketName,
            'Key' => $file,
            'SaveAs' => $file
        ]);

        dd($fileBucket);

        //$decryptedFile = $this->fileDecryption($encryptedFile);
    }

    /**
     * @param $content
     * @return mixed
     * @throws Crypt_GPG_BadPassphraseException
     * @throws Crypt_GPG_Exception
     * @throws Crypt_GPG_KeyNotFoundException
     * @throws Crypt_GPG_NoDataException
     */
    public function contentEncryption($content)
    {
        $publicKey = file_get_contents(dirname(__DIR__).'/keys/public.txt');
        // ou récupération de la clé publique depuis un serveur AWS S3 par exemple.
        $gpg = new Crypt_GPG();
        $info = $gpg->importKey($publicKey);
        $gpg->addSignKey($info[ 'fingerprint' ]);
        $gpg->addEncryptKey($info[ 'fingerprint' ]);
        $encryptedContent = $gpg->encryptAndSign($content);

        return $encryptedContent;
    }

    /**
     * @param $encryptedContent
     * @return string
     * @throws Crypt_GPG_BadPassphraseException
     * @throws Crypt_GPG_Exception
     * @throws Crypt_GPG_KeyNotFoundException
     * @throws Crypt_GPG_NoDataException
     */
    public function contentDecryption($encryptedContent)
    {
        $privateKey = file_get_contents(dirname(__DIR__).'/keys/private.txt');
        // ou récupération de la clé privée depuis un serveur AWS S3 par exemple.
        $gpg = new Crypt_GPG();
        $info = $gpg->importKey($privateKey);
        $gpg->addSignKey($info[ 'fingerprint' ]);
        $gpg->addDecryptKey($info[ 'fingerprint' ], "decrypt");
        $decryptedContent = $gpg->decryptAndVerify($encryptedContent);

        dd($decryptedContent);

        return $decryptedContent;
    }

}
