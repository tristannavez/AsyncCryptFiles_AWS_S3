<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Crypt_GPG;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DashboardController extends AbstractController
{
    /**
     * @Route("dashboard", name="dashboard")
     */
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController'
        ]);
    }

    protected function configure()
    {
// ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
// ...
        $output->writeln('fichier de base : ' . $file);
        $encryptedFile = $this->fileEncryption($file);
        $output->writeln('fichier chiffré : ' . $encryptedFile);
        $decryptedFile = $this->fileDecryption($encryptedFile);
        $output->writeln('fichier déchiffré : ' . $decryptedFile);
// ...
    }

    /**
     * @param $file
     * @return mixed
     */
    private function fileEncryption($file)
    {
        $publicKey = file_get_contents("/var/www/html/keys/client_public_key.txt");
// ou récupération de la clé publique depuis un serveur AWS S3 par exemple.
        $gpg = new Crypt_GPG();
        $info = $gpg->importKey($publicKey);
        $gpg->addencryptkey($info[ 'fingerprint' ]);
        $encryptedFile = $gpg->encrypt($file);

        return $encryptedFile;
    }

    /**
     * @param $encryptedFile
     * @return mixed
     */
    private function fileDcryption($encryptedFile)
    {
        $this->logger->info('Déchiffrement');
        $privateKey = file_get_contents("/var/www/html/keys/client_private_key.txt");
// ou récupération de la clé privée depuis un serveur AWS S3 par exemple.
        $gpg = new Crypt_GPG();
        $info = $gpg->importKey($privateKey);
        $gpg->addDecryptKey($info[ 'fingerprint' ], "azerty123"); //azerty123 = passphrase
        $decryptedFile = $gpg->decrypt($encryptedFile);

        return $decryptedFile;
    }

    
}
