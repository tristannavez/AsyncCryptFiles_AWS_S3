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
     * @Route("connexion", name="connexion")
     */
    public function index(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $task = $form->getData();

            $email = $task->getEmail();
            $password = $task->getPassword();

            $userconnted = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email, 'password' => $password]);

            if($userconnted){
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

    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request): Response
    {

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $task = $form->getData();

            $email = $task->getEmail();
            $password = $task->getPassword();

            $user->setEmail($email);
            $user->setPassword($password);

            $entityManager->persist($user);
            $entityManager->flush();
            
        }

        return $this->render('inscription/index.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }

}
