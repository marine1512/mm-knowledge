<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur pour gérer les utilisateurs.
 *
 * Fournit des fonctionnalités pour créer des administrateurs
 * ou des utilisateurs ainsi que l'affichage des utilisateurs.
 */
class UserController extends AbstractController
{
    /**
     * Action pour créer un utilisateur administrateur.
     *
     * Cette méthode crée un utilisateur avec des rôles administrateurs et
     * le sauvegarde dans la base de données, si aucun administrateur n'existe déjà.
     *
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour interagir avec Doctrine.
     * @param UserPasswordHasherInterface $passwordHasher Le hacheur de mot de passe pour sécuriser les mots de passe.
     *
     * @return JsonResponse Une réponse JSON indiquant le succès ou l'échec de la création de l'administrateur.
     *
     * @throws \Exception Si une erreur survient avec Doctrine ou le hachage du mot de passe.
     */
    #[Route('/create-admin', name: 'create_admin', methods: ['POST'])]
    public function createUser(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // Vérifier si un utilisateur admin existe déjà
        $existingAdmin = $entityManager->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);
    
        if ($existingAdmin) {
            return $this->json([
                'error' => 'Un administrateur existe déjà.'
            ], 400);
        }
    
        // Création d'un nouvel utilisateur
        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('admin@example.com'); 
        $user->setRoles(['ROLE_ADMIN']);
        
        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);
    
        // Sauvegarder dans la base de données
        $entityManager->persist($user);
        $entityManager->flush();
    
        return $this->json(['message' => 'Utilisateur admin créé avec succès']);
    }

    /**
     * Action pour créer un utilisateur client.
     *
     * Cette méthode crée un utilisateur avec des rôles client et
     * le sauvegarde dans la base de données, si aucun utilisateur similaire n'existe déjà.
     *
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour interagir avec Doctrine.
     * @param UserPasswordHasherInterface $passwordHasher Le hacheur de mot de passe pour sécuriser les mots de passe.
     *
     * @return JsonResponse Une réponse JSON indiquant le succès ou l'échec de la création de l'utilisateur client.
     *
     * @throws \Exception Si une erreur survient avec Doctrine ou le hachage du mot de passe.
     */
    #[Route('/create-client', name: 'create_client', methods: ['POST'])]
    public function createClient(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // Vérifier si l'utilisateur existe déjà
        $existingClient = $entityManager->getRepository(User::class)
            ->findOneBy(['username' => 'client']);
    
        if ($existingClient) {
            return $this->json([
                'error' => 'Un utilisateur avec ce nom existe déjà.'
            ], 400);
        }
    
        // Créer un nouveau client
        $user = new User();
        $user->setUsername('client'); 
        $user->setEmail('client@example.com'); 
        $user->setRoles(['ROLE_USER']);
    
        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, 'client');
        $user->setPassword($hashedPassword);
    
        // Persist et flush
        $entityManager->persist($user);
        $entityManager->flush();
    
        return $this->json(['message' => 'Utilisateur client créé avec succès']);
    }

    /**
     * Action d'index pour afficher les utilisateurs.
     *
     * Cette méthode affiche les utilisateurs directement via un template Twig.
     *
     * @return Response La réponse HTTP contenant le rendu du template utilisateur.
     */
    #[Route('/user', name: 'app_user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => [], 
        ]);
    }
}