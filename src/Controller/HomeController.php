<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    // Intercepte la route
    #[Route('/', name: 'home')]
    // Exécute la fonction
    public function index(): Response
    {   
        // Renvoie la réponse
        return $this->render('home/index.html.twig');
    }
}
