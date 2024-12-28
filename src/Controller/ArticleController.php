<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/app/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article')]
    public function index(EntityManagerInterface $entityManager, ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();


        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'app_article_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();

        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $article->setAuthor($this->getUser());
            $article->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($article);
            $entityManager->flush();


            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show')]
    public function show($id, Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager) : Response
    {
        $article = $articleRepository->find($id);

        if(!$article) {
            return $this->redirectToRoute('app_home');
        }

        $comments = $article->getComments();

        $comment = new Comment();

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setArticle($article);
            $comment->setAuthor($this->getUser());

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_show', ['id' => $id]);
        }

        return $this->render('/article/show.html.twig',[
            'form' => $form,
            'article' => $article,
            'comments' => $comments,
        ]);

    }

    #[Route('/{id}/delete}', name: 'app_article_delete')]
    public function delete(EntityManagerInterface $entityManager, Article $article) : Response
    {
        $user = $this->getUser();

        if($user->getId() != $article->getAuthor()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'No permission to make changes!');
            return $this->redirectToRoute('app_article');
        }

        $entityManager->remove($article);
        $entityManager->flush();


        $this->addFlash('success', 'Article was deleted!');
        return $this->redirectToRoute('app_article');

    }


    #[Route('/comment/{id}', name: "app_comment_delete" )]
    public function deleteComment(EntityManagerInterface $entityManager, Comment $comment, Request $request)
    {
        if($this->getUser()->getId() !== $comment->getAuthor()->getId() || !$this->isGranted('ROLE_ADMIN') ) {
            $this->addFlash('error', 'You have no permission to delete this comment!');

            return $this->redirectToRoute('app_article');
        }

        $entityManager->remove($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Comment deleted successfully!');

        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        }


        return $this->redirectToRoute('app_article');

    }

}
