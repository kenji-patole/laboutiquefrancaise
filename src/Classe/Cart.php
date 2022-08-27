<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**************** PANIER *******************/
class Cart 
{
  private $requestStack;
  private $entityManager;

  public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
  {
    $this->requestStack = $requestStack;
    $this->entityManager = $entityManager;
  }

  // AJOUTE au panier
  public function add($id) 
  {
    $session = $this->requestStack->getSession();
    // On récupère les informations du panier à l'aide de la session
    $cart = $session->get('cart', []);

    // Si dans le panier il y a un produit déjà inséré
    if(!empty($cart[$id])) {
      // On incrémente
      $cart[$id]++;
    } else {
      $cart[$id] = 1;
    }

    // On stocke les informations du panier dans une session (cart)
    $session->set('cart', $cart);
  }

  // AFFICHE le panier
  public function get() 
  {
    $session = $this->requestStack->getSession();
    return $session->get('cart');
  }

  // SUPPRIME le panier
  public function remove() 
  {
    $session = $this->requestStack->getSession();
    return $session->remove('cart');
  }

  // SUPPRIME un produit du panier
  public function delete($id) 
  {
    $session = $this->requestStack->getSession();

    $cart = $session->get('cart', []);

    unset($cart[$id]);

    // On redéfinit la nouvelle valeur dans la session cart
    return $session->set('cart', $cart);

  }


  public function decrease($id) 
  {
    $session = $this->requestStack->getSession();

    $cart = $session->get('cart', []);

    if($cart[$id] > 1) {
      // retirer une quantité (-1)
      $cart[$id]--;
    } else {
      // supprimer mon produit
      unset($cart[$id]);
    }

    // On redéfinit la nouvelle valeur dans la session cart
    return $session->set('cart', $cart);
  }

  public function getFull()
  {
    $cartComplete = [];

    if($this->get()) {
      foreach ($this->get() as $id => $quantity) {
        // Je récupère l'ID du produit en base de données
        $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);

        // SI le produit n'existe pas
        if (!$product_object) {
          // On le supprime du panier
          $this->delete($id);
          continue;
        }

        $cartComplete[] = [
          'product' => $product_object,
          'quantity' => $quantity
        ];
      } 
    }

    return $cartComplete;

  }

}