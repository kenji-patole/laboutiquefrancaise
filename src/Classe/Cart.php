<?php

namespace App\Classe;

use Symfony\Component\HttpFoundation\RequestStack;

/**************** PANIER *******************/
class Cart 
{
  private $requestStack;

  public function __construct(RequestStack $requestStack)
  {
    $this->requestStack = $requestStack;
  }

  // AJOUTER au panier
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

  // AFFICHER le panier
  public function get() 
  {
    $session = $this->requestStack->getSession();
    return $session->get('cart');
  }

  // SUPPRIMER le panier
  public function remove() 
  {
    $session = $this->requestStack->getSession();
    return $session->remove('cart');
  }

}