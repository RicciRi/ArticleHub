<?php

namespace App\Controller\admin;

use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/adm/lead')]
class LeadController extends AbstractController
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    #[Route('/', name: 'app_admin_lead')]
    public function index(EntityManagerInterface $entityManager, UserRepository $userRepository, ArticleRepository $articleRepository): Response
    {

        $usersWithArticles = $userRepository->findUserWithCountOfArticles();

        $usersWithNoArticle = $userRepository->findUserWithNoArticle();

        $usersTopByCountOfArticles = $userRepository->getUserByCountOfArticles();

        $usersTopByCountOfComments = $userRepository->getUsersByCountOfComments();

        $articlesTopByCountOfComments = $articleRepository->getArticlesByCountOfComments();

        $articleWithComments = $articleRepository->getArticlesWithComments(3);



        $lastCommentResponse = $userRepository->getUserAndTheirLastComment();

        $userWithComments = [];
        foreach($lastCommentResponse as $u) {
            $latestComment = $u->getComments()[0];
            $userWithComments[] = [
                'user' => $u,
                'latestComment' => $latestComment
            ];

        }

//                dd($userWithComments);


        return $this->render('admin/lead/index.html.twig', [
            'users'              => $usersWithArticles,
            'usersWithNoArticle' => $usersWithNoArticle,
            'usersTopByCountOfArticles' => $usersTopByCountOfArticles,
            'usersTopByCountOfComments' => $usersTopByCountOfComments,
            'articlesTopByCountOfComments' => $articlesTopByCountOfComments,
            'articleWithComments' => $articleWithComments,
            'userWithComments' => $userWithComments
        ]);
    }
}
