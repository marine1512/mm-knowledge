<?php

namespace App\Controller;

use App\Entity\Lecon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour gérer la boutique en ligne.
 *
 * Ce contrôleur gère la liste des produits et les détails d'un produit individuel.
 */
#[Route('/lecon')]
class LeconController extends AbstractController
{

    #[Route('/', name: 'lecon_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $lecon = $entityManager->getRepository(Lecon::class)->findAll();

        return $this->render('admin/lecon/index.html.twig', [
            'lecon' => $lecon,
        ]);
    }

    #[Route('/new', name: 'new_lecon', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lecon = new Lecon();

        if ($request->isMethod('POST')) {
            $lecon->setNom($request->request->get('nom'));
        

            $entityManager->persist($lecon);
            $entityManager->flush();

            return $this->redirectToRoute('lecon');
        }

        return $this->render('admin/lecon/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit_lecon', methods: ['GET', 'POST'])]
    public function edit(Lecon $lecon, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($lecon)
            ->add('nom')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('lecon');
        }

        return $this->render('admin/lecon/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete_lecon', methods: ['POST'])]
    public function delete(Lecon $lecon, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($lecon);
        $entityManager->flush();

        return $this->redirectToRoute('lecon');
    }
}