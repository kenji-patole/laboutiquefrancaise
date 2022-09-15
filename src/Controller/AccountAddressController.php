<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountAddressController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }
    

    #[Route('/compte/adresses', name: 'account_address')]
    public function index(): Response
    {
        return $this->render('account/address.html.twig');
    }


    #[Route('/compte/ajouter-une-adresse', name: 'account_address_add')]
    public function add(Cart $cart, Request $request): Response
    {   
        $address = new Address;

        // Je passe en paramètres à ma fonction createForm() le type du formulaire et l'objet
        $form = $this->createForm(AddressType::class, $address);

        // Ecoute la requête
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());

            // Fige la data
            $this->entityManager->persist($address);

            // Exécute
            $this->entityManager->flush();

            // S'il y a un produit dans le panier
            if ($cart->get()) {
                // JE redirige vers commande
                return $this->redirectToRoute('order');
            } else {
                return $this->redirectToRoute('account_address');
            }

           
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/compte/modifier-une-adresse/{id}', name: 'account_address_edit')]
    public function edit(Request $request, $id): Response
    {   
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

        // S'il n'y a aucune adresse OU que l'utilisateur ne correspond pas à celui actuellement connecté
        if (!$address || $address->getUser() != $this->getUser()) {
            return $this->redirectToRoute('account_address');
        }

        // Je passe en paramètres à ma fonction createForm() le type du formulaire et l'objet
        $form = $this->createForm(AddressType::class, $address);

        // Ecoute la requête
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // Exécute
            $this->entityManager->flush();

            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/compte/supprimer-une-adresse/{id}', name: 'account_address_delete')]
    public function delete($id): Response
    {   
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

        // S'il y a une adresse ET que l'utilisateur correspond à celui actuellement connecté
        if ($address || $address->getUser() == $this->getUSer()) {

            // SUPPRIME l'objet en base de données
            $this->entityManager->remove($address);
            
            // Exécute
            $this->entityManager->flush();
        }

    
        return $this->redirectToRoute('account_address');

    }
}
