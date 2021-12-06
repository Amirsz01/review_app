<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\Rating;
use App\Entity\Review;
use App\Entity\User;
use App\Form\Type\ImageType;
use App\Form\Type\TagType;
use App\Service\FileManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Sodium\add;

class ReviewController extends AbstractController
{
    private EntityManagerInterface $em;
    private FileManagerService $fms;

    public function __construct(EntityManagerInterface $em, FileManagerService $fms)
    {
        $this->em = $em;
        $this->fms = $fms;
    }

    #[Route('{_locale<%app.locales%>}/review/id{reviewId}', name: 'review')]
    public function index($reviewId, Request $request): Response
    {
        $review = $this->em->getRepository(Review::class)->findFullData($reviewId);
        $user = $this->getUser();
        if($this->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            $comment = new Comment($review, $user);
            $comment_form = $this->createFormBuilder($comment)
                ->add('text', TextType::class, ['required' => true, 'label'=> false])
                ->add('save', SubmitType::class, ['label' => 'form.buttons.send'])
                ->getForm();

            $comment_form->handleRequest($request);

            if($comment_form->isSubmitted() && $comment_form->isValid())
            {
                $comment->setText($comment_form->get('text')->getData());
                $review->addComment($comment);
                $this->em->persist($comment);
                $this->em->flush();
                return $this->redirectToRoute('review', ['reviewId' => $reviewId]);
            }

            $rating = $this->em->getRepository(Rating::class)->findOneBy([
                'user' => $this->getUser(),
                'review' => $review
            ]);
            $newObj = false;
            if($rating == null)
            {
                $newObj = true;
                $rating = new Rating($review, $user);
            }
            $rating_form = $this->createFormBuilder($rating)
                ->add('value', RangeType::class, [
                    'attr' => [
                        'min' => 1,
                        'max' => 5
                    ],
                    'label' => 'fields.score',
                    'data' => $rating->getValue()
                ])
                ->add('save', SubmitType::class, ['label' => 'form.buttons.send'])
                ->getForm();

            $rating_form->handleRequest($request);

            if($rating_form->isSubmitted() && $rating_form->isValid())
            {
                $rating->setValue($rating_form->get('value')->getData());
                if($newObj)
                {
                    $this->em->persist($rating);
                }
                $this->em->flush();
                return $this->redirectToRoute('review', ['reviewId' => $reviewId]);
            }
            return $this->renderForm('review/index.html.twig', [
                'review' => $review,
                'comment_form' => $comment_form,
                'rating_form' => $rating_form
            ]);
        }
        return $this->render('review/index.html.twig', [
            'review' => $review,
        ]);
    }

    #[Route('{_locale<%app.locales%>}/review/id{reviewId}/like', name: 'review_like')]
    public function likeAction($reviewId, Request $request): Response
    {
        $review = $this->em->getRepository(Review::class)->find($reviewId);
        $user = $this->getUser();
        $likeFind = $this->em->getRepository(Like::class)->findOneBy([
            'user' => $this->getUser(),
            'review' => $review
        ]);
        if($likeFind) {
            $this->em->remove($likeFind);
            $review->removeLike($likeFind);
        }
        else
        {
            $like = new Like($user, $review);
            $this->em->persist($like);
            $review->addLike($like);
        }
        $this->em->flush();
        return $this->redirectToRoute('review', ['reviewId' => $reviewId]);
    }

    public function createFormReview(Review $review, $isNew = false) : FormInterface
    {
        return $this->createFormBuilder($review)
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'fields.title',
                'data' => $isNew ? null : $review->getTitle(true)
            ])
            ->add('text', TextareaType::class, [
                'required' => true,
                'label' => 'fields.text',
            ])
            ->add('tags', TagType::class, [
                'required'=> true,
                'label' => 'fields.tags',
            ])
            ->add('images', ImageType::class, [
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'data-controller' => 'mydropzone',
                    'multiple' => 'multiple'
                ],
                'label' => 'fields.images',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'fields.category',
            ])
            ->add('score', RangeType::class, [
                'required'=> true,
                'attr' => [
                    'min' => 1,
                    'max' => 10
                ],
                'label' => 'fields.score',
            ])
            ->add('save', SubmitType::class, ['label' => 'form.buttons.send'])
            ->getForm();
    }

    #[Route('{_locale<%app.locales%>}/review/create/admin/{id}', name: 'create_review_by_admin')]
    public function createReviewByAdmin($id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $this->em->getRepository(User::class)->find($id);
        if($user == null)
        {
            return $this->redirectToRoute('home_page');
        }
        $review = new Review();
        $form = $this->createFormReview($review, true);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $review = $form->getData();
            $review->setAuthor($user);
            $this->em->persist($review);
            $this->em->flush();
            $this->addFlash('success', 'Обзор добавлен');
            return $this->redirectToRoute('home_page');
        }
        return $this->renderForm('review/createReview.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('{_locale<%app.locales%>}/review/create', name: 'create_review')]
    public function createReview(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $review = new Review();
        $form = $this->createFormReview($review, true);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $review = $form->getData();
            $review->setAuthor($this->getUser());
            $this->em->persist($review);
            $this->em->flush();
            $this->addFlash('success', 'Обзор добавлен');
            return $this->redirectToRoute('home_page'); //success add
        }
        return $this->renderForm('review/createReview.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('{_locale<%app.locales%>}/review/id{reviewId}/edit', name: 'edit_review')]
    public function editReview($reviewId, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $review = $this->em->getRepository(Review::class)->find($reviewId);
        /**
         * @var User $author
         */
        $author = $review->getAuthor();
        if($author->getId() != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('home_page');
        }
        else
        {
            $form = $this->createFormReview($review);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid())
            {
                $images = $form->get('images')->getData();
                foreach ($images as $image) {
                    $review->addImage($image);
                }
                /**@var $review Review*/
                $review = $form->getData();
                $review->setDateUpdate(new \DateTime());
                $this->addFlash('success', 'Обзор изменен');
                $this->em->flush();

                return $this->redirectToRoute('user_page', ['id' => $author->getId()]); //success add
            }
            return $this->renderForm('review/editReview.html.twig', [
                'form' => $form,
            ]);
        }
    }
    #[Route('{_locale<%app.locales%>}/review/id{reviewId}/delete', name: 'delete_review')]
    public function deleteReview($reviewId, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $review = $this->em->getRepository(Review::class)->find($reviewId);
        /**
         * @var User $author
         */
        $author = $review->getAuthor();
        if($author->getId() != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('home_page');
        }
        else
        {
            $this->em->remove($review);
            $this->addFlash('success', 'Обзор успешно удален');
            $this->em->flush();

            return $this->redirectToRoute('user_page', ['id' => $author->getId()]); //success add
        }
    }
}
