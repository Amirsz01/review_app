<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/test', name: 'test')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $category1 = new Category();
        $category1->setName('Films');

        $category2 = new Category();
        $category2->setName('Games');

        $category3 = new Category();
        $category3->setName('Books');

        $this->em->persist($category1);
        $this->em->persist($category2);
        $this->em->persist($category3);

        $this->em->flush();
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
