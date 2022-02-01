<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $wishRepository = $entityManager->getRepository(Wish::class);

        $bucketArray = $wishRepository->loadAdminWishes();
        return $this->render('admin/index.html.twig', compact('bucketArray'));
    }

    #[Route('/edit/{wish}', name: 'edit')]
    #[ParamConverter("wish", class: "App\Entity\Wish")]
    public function edit(Request $request, EntityManagerInterface $entityManager, Wish $wish)
    {
        $form = $this->createForm(WishType::class, $wish);

        // C'est le même form que sur la partie publique, mais je veux pouvoir en plus modifier la date de création
        $form->remove('Valider');
        $form->add('dateCreated', null, ['label' => 'Date de création du souhait']);
        $form->add('Valider', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($wish);
            $entityManager->flush();

            $this->addFlash('success', 'Souhait modifié !');
            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/unpublish/{id}', name: 'unpublish')]
    public function unpublish(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $wishRepository = $entityManager->getRepository(Wish::class);
        $wish = $wishRepository->find($id);

        $form = $this->createFormBuilder();
        $form->add('Oui', SubmitType::class);
        $form = $form->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!empty($wish)) {
                $wish->setIsPublished(false);
                $entityManager->persist($wish);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Souhait dépublié');
            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/unpublish.html.twig', ['form' => $form->createView()]);
    }
}
