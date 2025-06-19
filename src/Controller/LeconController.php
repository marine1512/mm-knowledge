<?php

namespace App\Controller;

use App\Entity\Lecon;
use App\Entity\User;
use App\Entity\UserPurchase;
use App\Entity\Certification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

   #[Route('/{id}/validate', name: 'validate_lecon', methods: ['POST'])]
public function validateLecon(
    Lecon $lecon,
    EntityManagerInterface $entityManager,
    \Psr\Log\LoggerInterface $logger
): RedirectResponse {
    $currentUser = $this->getUser();

    if (!$currentUser instanceof User) {
        $this->addFlash('error', 'Vous devez être connecté pour valider cette leçon.');
        return $this->redirectToRoute('app_login');
    }

    $userPurchase = $entityManager->getRepository(UserPurchase::class)
        ->findOneBy(['user' => $currentUser, 'lecon' => $lecon]);

    if (!$userPurchase) {
        $this->addFlash('error', 'Cette leçon n\'a pas été achetée.');
        return $this->redirectToRoute('user_achat');
    }

    if ($userPurchase->isValidated()) {
        $this->addFlash('info', 'Vous avez déjà validé cette leçon.');
        return $this->redirectToRoute('user_achat');
    }

    $userPurchase->setIsValidated(true);
    $entityManager->persist($userPurchase);

    $cursus = $lecon->getCursus();
    $theme = null;
    if ($cursus) {
        $allLeconsValidated = true;

        foreach ($cursus->getLecons() as $cursusLecon) {
            $purchase = $entityManager->getRepository(UserPurchase::class)
                ->findOneBy(['user' => $currentUser, 'lecon' => $cursusLecon]);

            if (!$purchase || !$purchase->isValidated()) {
                $allLeconsValidated = false;
                break;
            }
        }

        if ($allLeconsValidated) {
            $logger->info("Toutes les leçons du cursus '{$cursus->getNom()}' sont validées. Validation du cursus !");
            $cursus->setIsValidated(true);
            $entityManager->persist($cursus);

            $theme = $cursus->getTheme();
            if ($theme) {
                $validatedCursusCount = 0;
                foreach ($theme->getCursus() as $relatedCursus) {
                    if ($relatedCursus->isValidated()) {
                        $validatedCursusCount++;
                    }
                }

                if ($validatedCursusCount >= 2) {
                    $logger->info("Au moins deux cursus du thème '{$theme->getNom()}' sont validés. Validation du thème !");
                    $theme->setValide(true);
                    $entityManager->persist($theme);

                    $certification = $theme->getCertification();
                    if (!$certification) {
                        $certification = new Certification();
                        $certification->setTheme($theme);
                        $logger->info("Une nouvelle certification a été créée pour le thème '{$theme->getNom()}'.");
                    }
                    $certification->setCreatedAt(new \DateTime());
                    $entityManager->persist($certification);
                } else {
                    $logger->info("Le thème '{$theme->getNom()}' n'est pas encore entièrement validé : {$validatedCursusCount}/2 cursus validés.");
                }
            }
        } else {
            $logger->error("Certaines leçons du cursus '{$cursus->getNom()}' ne sont pas validées.");
        }
    }

    $entityManager->flush();

    $this->addFlash('success', 'Leçon validée avec succès.');

    return $this->redirectToRoute('user_achat');
}
}