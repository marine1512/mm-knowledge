<?php

namespace App\Controller;

use App\Entity\Cursus;
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
#[Route('/cursus')]
class CursusController extends AbstractController
{

    #[Route('/', name: 'cursus_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $cursus = $entityManager->getRepository(Cursus::class)->findAll();

        return $this->render('admin/cursus/index.html.twig', [
            'cursus' => $cursus,
        ]);
    }

    #[Route('/new', name: 'new_cursus', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cursus = new Cursus();

        if ($request->isMethod('POST')) {
            $cursus->setNom($request->request->get('nom'));
        

            $entityManager->persist($cursus);
            $entityManager->flush();

            return $this->redirectToRoute('cursus');
        }

        return $this->render('admin/cursus/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit_cursus', methods: ['GET', 'POST'])]
    public function edit(Cursus $cursus, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($cursus)
            ->add('nom')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('cursus');
        }

        return $this->render('admin/cursus/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete_cursus', methods: ['POST'])]
    public function delete(Cursus $cursus, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($cursus);
        $entityManager->flush();

        return $this->redirectToRoute('cursus');
    }
}