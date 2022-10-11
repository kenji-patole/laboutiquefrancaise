<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/inscription', name: 'register')]
    // Injection de dépendance 
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $notification = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        // Ecoute la requête
        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) {

            // On ajoute à notre instance user les données du formulaire
            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            // SI l'email n'est pas déjà présent en base de données ALORS
            if (!$search_email) {
                $password = $passwordHasher->hashPassword($user, $user->getPassword());
                // dd($password);

                // Définit le nouveau mot de passe crypté
                $user->setPassword($password);

                // Fige la data pour l'enregistrer
                $this->entityManager->persist($user);

                // Exécute 
                $this->entityManager->flush();

                // Envoie d'un email
                $mail = new Mail();

                $content = "Bonjour ". $user->getFirstName()."<br>Bienvenue sur la première boutique dédiée au made in France.<br><br>" ;

                $mail->send($user->getEmail(), $user->getFirstName(), 'Bienvenue sur La Boutique Française', $content);

                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte.";
            } else {
                $notification = "L'email que vous avez renseigné existe déjà.";
            }

        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
