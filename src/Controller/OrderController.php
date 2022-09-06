<?php

namespace App\Controller;

use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/commande', name: 'order')]
    public function index(): Response
    {
        // Si l'utilisateur n'a pas d'adresses ALORS
        if (!$this->getUser()->getAddresses()->getValues()) {
            // On le redirige vers la page d'ajout d'adresse
            return $this->redirectToRoute('account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);
        

        return $this->render('order/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
