<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * @Route("/admin/login", name="app_login")
     */
    public function index(AuthenticationUtils $outils): Response
    {
        $erreur = $outils->getLastAuthenticationError();
        $identifiant = $outils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'erreur' => $erreur !== null,
            'identifiant' => $identifiant,
        ]);
    }

    /**
     * Allows an administrator to log out
     * 
    * @Route("/admin/logout", name="admin_logout")
    */
    public function logout(){
        
    }
}
