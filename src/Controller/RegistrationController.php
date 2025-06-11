<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;


/**
 * Contrôleur de gestion des inscriptions et de la vérification des emails.
 *
 * Ce contrôleur gère :
 * - L'inscription d'un nouvel utilisateur avec génération d'un token de vérification.
 * - La vérification de l'email de l'utilisateur via un lien unique.
 * - L'affichage de pages d'inscription et de confirmation.
 */
class RegistrationController extends AbstractController
{
    /**
     * Service pour la vérification des emails.
     *
     * @var EmailVerifier
     */
    private EmailVerifier $emailVerifier;

    /**
     * Constructeur du contrôleur.
     *
     * @param EmailVerifier $emailVerifier Service pour la vérification des emails.
     */

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * Route pour l'inscription d'un nouvel utilisateur.
     *
     * @param Request $request La requête HTTP contenant les données du formulaire.
     * @param UserPasswordHasherInterface $userPasswordHasher Service pour le hachage des mots de passe.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour la base de données.
     *
     * @return Response La réponse HTTP avec le formulaire d'inscription ou une redirection après succès.
     */

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
        
            // Hashage du mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // **Attribuer le rôle par défaut ROLE_USER**
            $user->setRoles(['ROLE_USER']);
        
            // Générer un token unique pour la vérification par email
            $emailVerificationToken = Uuid::v4()->toRfc4122(); // Format UUID
            $user->setEmailVerificationToken($emailVerificationToken);
        
            $entityManager->persist($user);
            $entityManager->flush();
        
            // Envoi de l'email de confirmation avec le token
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('mignomarine@gmail.com', 'Mail Bot'))
                    ->to((string) $user->getEmail())
                    ->subject('Merci de confirmer votre mail')
                    ->html(
                        '<h1>Bienvenue sur notre application !</h1>' .
                        '<p>Merci de vous être inscrit. Veuillez confirmer votre adresse email en cliquant sur le lien suivant :</p>' .
                        '<a href="http://127.0.0.1:8000/verify/email?token=' . $emailVerificationToken . '">Confirmer mon adresse</a>'
                    )
            );
        
            $this->addFlash('success', 'Un email de confirmation a été envoyé. Veuillez vérifier votre boîte de réception.');
        
            return $this->redirectToRoute('app_register_confirmation');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    
    #[Route('/verify/email', name: 'app_verify_email')]
    /**
     * Vérifie l'email de l'utilisateur en utilisant le token fourni dans l'URL.
     *
     * @param Request $request La requête HTTP contenant le token de vérification.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour la base de données.
     *
     * @return Response La réponse HTTP avec un message de succès ou une redirection.
     */
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager): Response
    {
        $token = $request->query->get('token'); // Récupérer le token depuis l'URL
    
        if (!$token) {
            throw $this->createNotFoundException('Token non fourni pour la vérification.');
        }
    
        // Récupérer un utilisateur avec ce token
        $user = $entityManager->getRepository(User::class)->findOneBy(['emailVerificationToken' => $token]);
    
        if (!$user) {
            throw $this->createNotFoundException('Aucun utilisateur trouvé pour ce token.');
        }
    
        // Marquer l'utilisateur comme vérifié
        if (!$user->isVerified()) {
            $user->setIsVerified(true);
            $user->setEmailVerificationToken(null); // Supprimer le token après utilisation
            $entityManager->flush(); // Sauvegarder les modifications
        }
    
        $this->addFlash('success', 'Votre email a été confirmé avec succès.');
    
        return $this->redirectToRoute('home');
    }

    #[Route('/register/confirmation', name: 'app_register_confirmation')]
    /**
     * Affiche une page de confirmation après l'inscription.
     *
     * Cette méthode est appelée après qu'un utilisateur ait soumis le formulaire d'inscription.
     * Elle affiche un message de succès et invite l'utilisateur à vérifier son email.
     *
     * @return Response La réponse HTTP contenant la vue de confirmation.
     */
    public function confirmEmailNotification(): Response
    {
        return $this->render('registration/confirmation_email.html.twig');
    }
}