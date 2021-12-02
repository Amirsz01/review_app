<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Like;
use App\Entity\Review;
use App\Entity\User;
use App\Form\Type\ImageType;
use App\Form\Type\TagType;
use App\Service\FileManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    private EntityManagerInterface $em;
    private FileManagerService $fms;

    public function __construct(EntityManagerInterface $em, FileManagerService $fms)
    {
        $this->em = $em;
        $this->fms = $fms;
    }

    #[Route('/review/id{reviewId}', name: 'review')]
    public function index($reviewId, Request $request): Response
    {
        $review = $this->em->getRepository(Review::class)->find($reviewId);

        if($this->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            $comment = new Comment($review, $this->getUser());
            $comment_form = $this->createFormBuilder($comment)
                ->add('text', TextType::class, ['required' => true, 'label'=> false])
                ->add('save', SubmitType::class, ['label' => 'Отправить'])
                ->getForm();

            $comment_form->handleRequest($request);

            if($comment_form->isSubmitted())
            {
                $comment = $comment_form->getData();
                $review->addComment($comment);
                $this->em->persist($comment);
                $this->em->flush();
                return $this->redirectToRoute('review', ['reviewId' => $reviewId]);
            }
            return $this->renderForm('review/index.html.twig', [
                'review' => $review,
                'comment_form' => $comment_form,
            ]);
        }
        return $this->render('review/index.html.twig', [
            'review' => $review,
        ]);
    }

    #[Route('/review/id{reviewId}/like', name: 'review_like')]
    public function likeAction($reviewId, Request $request): Response
    {
        $review = $this->em->getRepository(Review::class)->find($reviewId);
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
            $like = new Like($this->getUser(), $review);
            $this->em->persist($like);
            $review->addLike($like);
        }
        $this->em->flush();
        return $this->redirectToRoute('review', ['reviewId' => $reviewId]);
    }

    public function createFormReview(Review $review) : FormInterface
    {
        return $this->createFormBuilder($review)
            ->add('title', TextType::class, ['required' => true])
            ->add('text', TextareaType::class, ['required' => true])
            ->add('tags', TagType::class, ['required'=> true])
            ->add('images', ImageType::class, [
                'multiple' => true,
                'required' => false,
                'attr' => ['data-controller' => 'mydropzone', 'multiple' => 'multiple'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('save', SubmitType::class, ['label' => 'Отправить'])
            ->getForm();
    }
    #[Route('/review/create', name: 'create_review')]
    public function createReview(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $review = new Review();
        $form = $this->createFormReview($review);
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

    #[Route('/review/id{reviewId}/edit', name: 'edit_review')]
    public function editReview($reviewId, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $review = $this->em->getRepository(Review::class)->find($reviewId);
        /**
         * @var User $author
         */
        $author = $review->getAuthor();
        if($author->getId() != $this->getUser()->getId())
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
                $oldImages = $review->getImages();
                /**@var $oldImage Image*/
//                foreach ($oldImages as $oldImage) {
//                    $this->fms->imageRemove($oldImage->getSlug());
//                }
                foreach ($images as $image) {
                    $review->addImage($image);
                }
                $review = $form->getData();
                $this->addFlash('success', 'Обзор изменен');
                $this->em->flush();

                return $this->redirectToRoute('home_page'); //success add
            }
            return $this->renderForm('review/editReview.html.twig', [
                'form' => $form,
            ]);
        }
    }
}
