<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur responsable de la gestion de la sécurité et des actions liées à l'authentification.
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche la page de connexion et gère les erreurs d'authentification.
     *
     * Si l'utilisateur est déjà authentifié, il est redirigé vers la page d'accueil.
     *
     * @param AuthenticationUtils $authenticationUtils Service Symfony pour gérer les données d'authentification.
     *
     * @return Response La réponse HTTP pour afficher la page de connexion.
     *
     * @throws \LogicException En cas de problème avec la logique du traitement.
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home'); // Redirection si l'utilisateur est connecté.
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Gère le processus de validation des identifiants utilisateur.
     *
     * Cette méthode est interceptée automatiquement par Symfony pour gérer la connexion.
     *
     * @return void
     *
     * @throws \LogicException Cette méthode est laissée vide intentionnellement.
     */
    #[Route(path: '/login_check', name: 'app_login_check')]
    public function loginCheck(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by your security system.');
    }

    /**
     * Gère la déconnexion de l'utilisateur.
     *
     * Cette méthode est interceptée automatiquement par Symfony, et son contenu peut rester vide.
     *
     * @return void
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony gère automatiquement la déconnexion, cette méthode n'a pas besoin d'implémentation.
    }
}