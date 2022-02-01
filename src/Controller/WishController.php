<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Service\Censurator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/', name: 'wish_', host: '%host%')]
class WishController extends AbstractController
{
    #[Route('/', name: 'list')]
    #[Template]
    public function list(EntityManagerInterface $entityManager)
    {
        $wishRepository = $entityManager->getRepository(Wish::class);

        $bucketArray = $wishRepository->findBy(['isPublished'=>true], ['dateCreated' => 'DESC']);

        return compact('bucketArray');
    }

    #[Route('/souhait/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    #[Template]
    public function detail(EntityManagerInterface $entityManager, int $id)
    {
        $wishRepository = $entityManager->getRepository(Wish::class);

        $wish = $wishRepository->find($id);

        if(empty($wish)) {
            throw $this->createNotFoundException('Souhait introuvable');
        }
        return compact('wish');
    }

    #[Route('/create', name: 'create')]
    #[IsGranted("ROLE_USER", message: "Veuillez vous connecter avant d'accéder à la création d'une idée")]
    public function create(Censurator $censurator, Security $security, Request $request, EntityManagerInterface $manager)
    {
        $wish = new Wish();
        $wish->setAuthor($security->getUser()->getUserIdentifier()); // Methode 2
        $form = $this->createForm(WishType::class, $wish);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wish->setIsPublished(true);
            $wish->setDateCreated(new \DateTime());
            $wish->setDescription($censurator->purify($wish->getDescription()));
            $manager->persist($wish);
            $manager->flush();

            $this->addFlash('success', 'Souhait publié !');
            return $this->redirectToRoute('wish_list');
        }

        return $this->render('wish/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
