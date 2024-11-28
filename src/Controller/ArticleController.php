<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function home(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }


    // #[Route('/article/create', name: 'article_create')]
    // public function create(Request $request, ManagerRegistry $doctrine): Response
    // {
    //     $article = new Article();
    //     $form = $this->createForm(ArticleType::class, $article, [
    //         'submit_label' => 'Créer l\'article'
    //     ]);

    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager = $doctrine->getManager();
    //         $entityManager->persist($article);
    //         $entityManager->flush();

    //         $this->addFlash('success', 'L\'article a été crée avec succès.');

    //         // return $this->redirectToRoute('article_list');
    //     }

    //     return $this->render('article/create.html.twig', [
    //         'form' => $form->createView(),
    //     ]);
    // }

    #[Route('/article/create', name: 'article_create')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article, [
            'submit_label' => 'Créer l\'article'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Une erreur est survenue lors de l\'upload de l\'image.');
                }

                $article->setImagePath('uploads/images/' . $newFilename);
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'L\'article a été créé avec succès.');

            return $this->redirectToRoute('article_list');
        }

        return $this->render('article/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/article/list', name: 'article_list')]
    public function list(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
        ]);
    }
    #[Route('/article/update/{id}', name: 'article_update')]
    public function update(int $id, Request $request, ManagerRegistry $doctrine, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->find($id);
    
        if (!$article) {
            throw $this->createNotFoundException('Aucun article trouvé avec l\'ID ' . $id);
        }
    
        $form = $this->createForm(ArticleType::class, $article, [
            'submit_label' => 'Mettre à jour l\'article'
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
    
            $this->addFlash('success', 'L\'article a été modifié avec succès.');

            // return $this->redirectToRoute('article_list');
        }
    
        return $this->render('article/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/article/delete/{id}', name: 'article_delete')]
    public function delete(int $id, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $article = $entityManager->getRepository(Article::class)->find($id);
    
        if (!$article) {
            $message = 'Article numéro ' . $id . ' inexistant';
        } else {
            $entityManager->remove($article);
            $entityManager->flush();
            $message = 'Article numéro ' . $id . ' supprimé';
        }
    
        return $this->render('article/delete.html.twig', [
            'message' => $message,
        ]);
    }
}
