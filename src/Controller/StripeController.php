<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session/{reference}', name: 'stripe_create_session')]
    public function index(EntityManagerInterface $entitymanager, Cart $cart, $reference)
    {
        $products_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';

        // ON récupère la commande en base de données à l'aide de la référence
        $order = $entitymanager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order) {
            new JsonResponse(['error' => 'order']);
        }
        // PRODUITS
        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_objet = $entitymanager->getRepository(Product::class)->findOneByName($product->getProduct());
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN."/uploads/".$product_objet->getIllustration()]
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        // TRANSPORTEUR
        $products_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN]
                ],
            ],
            'quantity' => 1
        ];

        // STRIPE
        Stripe::setApiKey('sk_test_51LgWZPEhoXXfslZ7tj1K9fiX5qNi97OccQn4O6gP8C845VvFOMJ8FrirIRhAhBUoWB4fxV0j5nmELkBUhliA7dq400PQhbAENG');

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => [
                'card'
            ],
            'line_items' => [[
                $products_for_stripe
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        // On ajoute à notre objet $order la session de stripe
        $order->setStripeSessionId($checkout_session->id);

        // Exécute
        $entitymanager->flush();

        $response = new JsonResponse(['id' => $checkout_session->id]);
        return $response;
    }
}
