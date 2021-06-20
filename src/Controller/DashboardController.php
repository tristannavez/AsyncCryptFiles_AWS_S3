<?php

namespace App\Controller;

use App\Entity\User;
use Aws\S3\S3Client;
use Crypt_GPG;
use Crypt_GPG_BadPassphraseException;
use Crypt_GPG_Exception;
use Crypt_GPG_KeyNotFoundException;
use Crypt_GPG_NoDataException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        // Création d'un tableau contenant la configuration d'aws
        $sharedConfig = [
            'region' => 'eu-west-3',
            'version' => 'latest'
        ];

        // Instanciation un objet S3 en passant en paramétre le tableau de configuration
        $s3 = new S3Client($sharedConfig);
        // Création d'une variable contenant le nom du bucket
        $bucketName = 'espace-partage-epsi-i1-dev';

        // Récupération des fichiers dans le bucket
        $files = $s3->listObjects(['Bucket' => $bucketName]);

        // On créer un tableau
        $file = array();

        // Parcours de l'ensemble des fichiers du bucket
        for($i=0;$i<count($files['Contents']);$i++){
            // Changement du format de date du fichier
            $date = date('d-m-y',strtotime($files['Contents'][$i]['LastModified']));
            // Ajout du titre et de la date dans notre tableau file
            $file[] = ([
                'titre' => $files['Contents'][$i]['Key'],
                'date' => $date
            ]);
        }

        // Récupération des utilisateurs du site
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        // Envoie des données vers la page dashboard
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
        
        // Récupération du fichier envoyé
        $json_file = $request->files->get('fileToUpload');
        // Récupération du nom du fichier envoyé
        $fichier = $json_file->getClientOriginalName();
        // Récupération de l'url du fichier envoyé
        $url_fichier = $json_file->getPathName();
        // Récupération du contenu du fichier envoyé
        $content_file = file_get_contents($url_fichier);
        // Ajout du contenu précédemment récupéré signé et crypté
        file_put_contents($fichier, $this->contentEncryption($content_file));
        // Envoi du fichier signé et crypté dans le bucket
        $s3->putObject([
            'Bucket' => $bucketName,
            'Key' => $fichier
        ]);
        // Redirection vers la page dashboard une fois l'envoi terminé
        return $this->redirectToRoute('dashboard');

    }

    /**
     * @Route("dashboard/download/{fichier}", name="dashboard_downloadfile")
     */
    public function downloadFile($fichier){

        $sharedConfig = [
            'region' => 'eu-west-3',
            'version' => 'latest'
        ];
        $s3 = new S3Client($sharedConfig);
        $bucketName = 'espace-partage-epsi-i1-dev';

        // Récupération du fichier à télécharger dans le bucket
        $fileBucket = $s3->getObject([
            'Bucket' => $bucketName,
            'Key' => $fichier,
            'SaveAs' => $fichier
        ]);

        $url = $fileBucket['@metadata']['effectiveUri'];

        // Récupération de la clé privée
        $privateKey = file_get_contents(dirname(__DIR__).'/keys/private.txt');
        $gpg = new Crypt_GPG();
        $info = $gpg->importKey($privateKey);
        $gpg->addSignKey($info[ 'fingerprint' ], "decrypt");
        // Ajout de la clé de décryptage accompagné de la phrase secrète
        $gpg->addDecryptKey($info[ 'fingerprint' ], "decrypt");
        // Decryptage et vérification du contenu du fichier envoyé depuis la fonction d'upload
        $decryptedContent = $gpg->decryptAndVerify(file_get_contents($url));
        // Instanciation de l'objet File
        $file = new \Symfony\Component\Filesystem\Filesystem();
        // Déplacement du fichier dans le dossier UploadFile présent dans le dossier public
        if($file->exists($this->getParameter('fichier_directory').$fichier)){
            $content_fichier_upload = file_get_contents($this->getParameter('fichier_directory').$fichier);
            $content = file_get_contents($fichier, $this->contentDecryption($content_fichier_upload));
        }
        // Redirection vers la page dashboard une fois l'envoi terminé
        return $this->redirectToRoute('dashboard');

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
        // Récupération de la clé public
        $publicKey = file_get_contents(dirname(__DIR__).'/keys/public.txt');
        // Instanciation d' l'objet Crypt GPG
        $gpg = new Crypt_GPG();
        // Ajout de la clé récupéré précédemment dans l'objet
        $info = $gpg->importKey($publicKey);
        // Ajout d'une signature dans l'objet via la clé public
        //$gpg->addSignKey($info[ 'fingerprint' ]);
        // Ajout de l'encryptage dans l'objet via la clé public
        $gpg->addEncryptKey($info[ 'fingerprint' ]);
        // Encryptage et signature du contenu du fichier envoyé depuis la fonction d'upload
        $encryptedContent = $gpg->encrypt($content);

        // Retour du contenu encrypté et signé
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
        // Récupération de la clé privée
        $privateKey = file_get_contents(dirname(__DIR__).'/keys/private.txt');
        $gpg = new Crypt_GPG();
        $info = $gpg->importKey($privateKey);
        //$gpg->addSignKey($info[ 'fingerprint' ], "decrypt");
        // Ajout de la clé de décryptage accompagné de la phrase secrète
        $gpg->addDecryptKey($info[ 'fingerprint' ], "decrypt");
        // Decryptage et vérification du contenu du fichier envoyé depuis la fonction d'upload
        $decryptedContent = $gpg->decrypt($encryptedContent);

        return $decryptedContent;
    }

}
