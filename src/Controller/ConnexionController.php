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

            $userconnted = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);

            if(password_verify($password, $userconnted->getPassword())){
                return $this->redirectToRoute('dashboard');
            }else{
                return $this->redirectToRoute('L\'utilisateur n\existe pas.');
            }
            
        }

        return $this->render('connexion/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
