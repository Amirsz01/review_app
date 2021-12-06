<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'root')]
    public function redirectToHomePage()
    {
        return $this->redirectToRoute('home_page');
    }

    #[Route('{_locale<%app.locales%>}/', name: 'home_page')]
    public function index(): Response
    {
        $reviewsByLast = $this->em->getRepository(Review::class)->findByLastUpdate();
        $reviewsByRating = $this->em->getRepository(Review::class)->findByMostRating();
        return $this->renderForm('default/index.html.twig', [
            'reviewsLast' => $reviewsByLast,
            'reviewsRating' => $reviewsByRating
        ]);
    }

    public function searchBar()
    {
        $form = $this->createFormBuilder(null)
            ->add('search', SearchType::class, [
                'attr' => [
                    'class' => 'form-control search-query m-1',
                    'placeholder' => 'search.search_placeholder',
                ],
                'label' => false,
            ])
            ->setAction($this->generateUrl('handleSearch'))
            ->setMethod('POST')
            ->setAttribute('class', 'col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3')
            ->getForm();

        return $this->render('default/searchBar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('{_locale<%app.locales%>}/search', name: 'handleSearch')]
    public function handleSearch(Request $request): Response
    {
        $data = $request->request->get('form')['search'];
        $reviews = $this->em->getRepository(Review::class)->findByText($data);
        return $this->render('default/handleSearch.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route("/autocomplete", name: "tag_autocomplete")]
    public function autocompleteAction(Request $request)
    {
        $names = array();
        $term = explode(",", (strip_tags($request->get('term'), ",")));
        $term = trim($term[count($term)-1]);

        $tags = $this->em->getRepository(Tag::class)->createQueryBuilder('c')
            ->where('c.name LIKE :name')
            ->setParameter('name', '%'.$term.'%')
            ->getQuery()
            ->getResult();

        /** @var $tag Tag */
        foreach ($tags as $tag)
        {
            $names[] = $tag->getName();
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }
}
