<?php

namespace App\Controller;

use DateTime;
use App\Classe\Mail;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/mot-de-passe-oublie', name: 'reset_password')]
    public function index(Request $request): Response
    {
        // SI l'utilisateur est connecté
        if($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // On recupère à l'aide de request le nom du champ dans le formulaire (email)
        if ($request->get('email')) {
            // Vérifie si l'email correspond à celui enregistré en base de données
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));

            // SI l'utilisateur existe
            if ($user) {
                // 1 : Enregistrer en base la demande de reset_password avec user, token, createdAt.                
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new DateTime());
                // Fige la data
                $this->entityManager->persist($reset_password);
                // Exécute
                $this->entityManager->flush();

                // 2 : Envoyer un email à l'utilisateur avec un lien lui permettant de mettre à jour son mot de passe.

                // Génère une url et envoie le token 
                $url = $this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ]);

                $content = "Bonjour ".$user->getFirstname()."<br>Vous avez demandé à réinitialiser votre mot de passe sur le site la Boutique Française.<br><br>";

                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='.$url.'>mettre à jour votre mot de passe</a>";

                $mail = new Mail();
                $mail->send($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), 'Réinitialiser votre mot de passe sur La Boutique Française', $content);

                $this->addFlash('notice', 'Vous allez recevoir dans quelques secondes un mail avec la procédure pour réinitialiser votre mot de passe.');
            } else {
                $this->addFlash('notice', 'Cette adresse email est inconnue.');
            }
        }



        return $this->render('reset_password/index.html.twig');
    }


    #[Route('/modifier-mon-mot-de-passe/{token}', name: 'update_password')]
    public function update(Request $request, $token, UserPasswordHasherInterface $passwordHasher): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$reset_password) {
           return $this->redirectToRoute('reset_password');
        }
        
        $now = new DateTime();

        // Vérifier si le createdAt = now - 3h
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            $this->addFlash('notice', 'Votre demande de mot de passe a expiré merci de la renouveler.');
            return $this->redirectToRoute('reset-password');
        }

        // Rendre une vue avec mot de passe et confirmez votre mot de passe
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // On récupère le new_password 
            $new_pwd = $form->get('new_password')->getData();

            // ENCODAGE DES MOTS DE PASSE
            // On crypte le mot de passe
            $password = $passwordHasher->hashPassword($reset_password->getUser(), $new_pwd);
            // Définit le nouveau mot de passe crypté
            $reset_password->getUser()->setPassword($password);

            // Flush en base de données
            $this->entityManager->flush();
            
            // Redirection de l'utilisateur vers la page de connexion
            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form'=> $form->createView()
        ]);


    }
}
