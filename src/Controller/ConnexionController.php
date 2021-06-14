<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConnexionController extends AbstractController
{
    /**
     * @Route("/", name="connexion")
     */
    public function index(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $task['email']]);
            
            dump($user);

            if(count($user) > 0){
                return $this->redirectToRoute('dashboard');
            }else{
                return $this->redirectToRoute('L\'utilisateur n\existe pas.');
            }
            
        }

        return $this->render('connexion/index.html.twig', [
            'controller_name' => 'ConnexionController',
            'form' => $form->createView()
        ]);
    }
}
