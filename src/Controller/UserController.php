<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{

    #[Route('/create-admin', name: 'create_admin', methods: ['POST'])]
    public function createUser(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $existingAdmin = $entityManager->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);
    
        if ($existingAdmin) {
            return $this->json([
                'error' => 'Un administrateur existe déjà.'
            ], 400);
        }
        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('admin@example.com'); 
        $user->setRoles(['ROLE_ADMIN']);
        
        $hashedPassword = $passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);
    
        $entityManager->persist($user);
        $entityManager->flush();
    
        return $this->json(['message' => 'Utilisateur admin créé avec succès']);
    }

    #[Route('/create-client', name: 'create_client', methods: ['POST'])]
    public function createClient(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $existingClient = $entityManager->getRepository(User::class)
            ->findOneBy(['username' => 'client']);
    
        if ($existingClient) {
            return $this->json([
                'error' => 'Un utilisateur avec ce nom existe déjà.'
            ], 400);
        }
    
        $user = new User();
        $user->setUsername('client'); 
        $user->setEmail('client@example.com'); 
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $passwordHasher->hashPassword($user, 'client');
        $user->setPassword($hashedPassword);
    
        $entityManager->persist($user);
        $entityManager->flush();
    
        return $this->json(['message' => 'Utilisateur client créé avec succès']);
    }

    #[Route('/user', name: 'app_user_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        return $this->render('admin/user/index.html.twig', [
            'users' => $users, 
        ]);
    }

     #[Route('/user/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('username')
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER']);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

     #[Route('/user/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createFormBuilder($user)
            ->add('username')
            ->add('email')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index');
    }
}