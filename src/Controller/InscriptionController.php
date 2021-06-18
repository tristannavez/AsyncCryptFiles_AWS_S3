<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;

class InscriptionController extends AbstractController
{
    
    /**
     * @Route("/", name="inscription")
     */
    public function index(Request $request): Response
    {

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $task = $form->getData();

            $email = $task->getEmail();
            $password = $task->getPassword();

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $user->setEmail($email);
            $user->setPassword($passwordHash);

            $entityManager->persist($user);
            $entityManager->flush();
            
        }

        return $this->render('inscription/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
