<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORM\SearchCriteriaProvider;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $em;
    private ?int $userId;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->clientRegistry = $clientRegistry;
    }

    #[Route('{_locale<%app.locales%>}/user/id{id}', name: 'user_page')]
    public function index(int $id, Request $request, DataTableFactory $dataTableFactory): Response
    {
        if(!$this->isGranted('IS_AUTHENTICATED_FULLY') || ($id != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')))
        {
            return $this->redirectToRoute('home_page');
        }
        $user = $this->em->getRepository(User::class)->find($id);
        if($user == null)
        {
            return $this->redirectToRoute('home_page');
        }
        $this->userId = $user->getId();
        $table = $dataTableFactory->create()
            ->add('title', TextColumn::class, [
                'orderable' => true,
                'searchable' => true,
                'render' => function($value, $context) {
                    return sprintf('<a href="%s">%s</a>', $this->generateUrl('review', ['reviewId' => $context->getId()]), $context->getTitle());
                },
                'label' => 'fields.title',
            ])
            ->add('category', TextColumn::class, [
                'orderable' => true,
                'searchable' => true,
                'label' => 'fields.category',
                'field' => 'category.name'
            ])
            ->add('buttons', TwigColumn::class, [
                'template' => 'user/tables/buttons.html.twig',
                'label' => 'fields.buttons'
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Review::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('e')
                        ->from(Review::class, 'e')
                        ->leftJoin('e.category', 'category')
                        ->where('e.author = :val')
                        ->setParameter('val', $this->userId);
                },
            ])
            ->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'datatable' => $table]);
    }

    #[Route('{_locale<%app.locales%>}/admin', name: 'admin_page')]
    public function adminPanel()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $this->em->getRepository(User::class)->findAll();
        return $this->render('user/adminPanel.html.twig', ['users' => $users]);
    }

    #[Route('{_locale}/logout', name: 'logout')]
    public function logout()
    {

    }
}
