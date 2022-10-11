<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/merci/{stripeSessionId}', name: 'order_success')]
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        // SI la commande n'existe pas OU que l'utilisateur ne correspond pas à celui actuellement connecté ALORS
        if (!$order || $order->getUser() != $this->getUser()) {
           return $this->redirectToRoute('home');
        } 

        // SI la commande est en statut NON payé
        if (!$order->isIsPaid()) {
            // Vider la session "cart"
            $cart->remove();

            // Modifier le statut isPaid de notre commande en mettant 1
            $order->setIsPaid(1);
            
            // Exécute
            $this->entityManager->flush();

            // Envoyer un email à notre client pour lui confirmer sa commande   
            $mail = new Mail();

            $content = "Bonjour ". $order->getUser()->getFirstName()."<br>Merci pour votre commande.<br><br>" ;

            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstName(), 'Votre commande sur La Boutique Française est bien validée.', $content); 
        }

        return $this->render('order_success/index.html.twig', [
             // Afficher les quelques informations de la commande de l'utilisateur
            'order' => $order
        ]);
    }
}
