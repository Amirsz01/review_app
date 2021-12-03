<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $em;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->clientRegistry = $clientRegistry;
    }

    #[Route('{_locale}/user/{id}', name: 'user_page')]
    public function index($id): Response
    {
        $user = $existingUser = $this->em->getRepository(User::class)
            ->findOneBy(['id' => $id]);
        $reviews = $user->getReviews();
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'reviews' => $reviews,
        ]);
    }
    #[Route('{_locale}/logout', name: 'logout')]
    public function logout()
    {

    }
}
