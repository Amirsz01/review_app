<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Review;
use App\Entity\User;
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

    #[Route('{_locale<%app.locales%>}/test', name: 'test')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $this->em->flush();
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
