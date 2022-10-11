<?php

namespace App\Controller;

use App\Entity\Header;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Intercepte la route
    #[Route('/', name: 'home')]
    // Exécute la fonction
    public function index(): Response
    {   
        // On récupère en base de données les produits isBest
        $products = $this->entityManager->getRepository(Product::class)->findByIsBest(1);

        $headers = $this->entityManager->getRepository(Header::class)->findAll();

        // Renvoie la réponse
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'headers' => $headers
        ]);
    }
}
