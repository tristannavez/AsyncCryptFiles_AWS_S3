<?php

namespace App\Controller;

use Aws\Exception\AwsException;
use Aws\Iam\IamClient;
use Aws\S3\S3Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("dashboard", name="dashboard")
     */
    public function index(): Response
    {
        // The same options that can be provided to a specific client constructor can also be supplied to the Aws\Sdk class.
        // Use the us-west-2 region and latest version of each client.
        $sharedConfig = [
            'region' => 'eu-west-2',
            'version' => 'latest'
        ];

        // Create an SDK class used to share configuration across clients.
        $sdk = new S3Client($sharedConfig);
        $bucketName = 'espace-partage-epsi-i1-dev';

        $result = $sdk->listObjects(['Bucket' => $bucketName]);

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'file' => $result
        ]);

    }

    
}
