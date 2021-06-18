<?php

namespace App\Controller;

use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;
use Aws\Iam\IamClient;
use App\Entity\User;
use Aws\S3\S3Client;
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
        // Je récupère l'extension du fichier
        // Ajout du nom du fichier dans l'objet File
        $fichier = $json_file->getClientOriginalName();
        $url_fichier = $json_file->getPathName();

        $s3->putObject([
            'Bucket' => $bucketName,
            'Key' => $fichier,
            'SourceFile' => $url_fichier
        ]);

        return $this->redirectToRoute('dashboard', [], 204);

    }
    
}
