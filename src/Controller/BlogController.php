<?php

declare(strict_types=1);

namespace App\Controller;

use App\Blog\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    public function __construct(private readonly ArticleRepository $articles)
    {
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        $featured = $this->articles->findFeatured();

        // The featured article already has the hero slot; keep it out of the
        // grid below so it is not rendered twice on the same page.
        return $this->render('blog/index.html.twig', [
            'featured' => $featured,
            'articles' => null !== $featured
                ? $this->articles->findAllExcept($featured->slug)
                : $this->articles->findAll(),
        ]);
    }

    #[Route('/articles/{slug}', name: 'article', methods: ['GET'], requirements: ['slug' => '[a-z0-9-]+'])]
    public function article(string $slug): Response
    {
        $article = $this->articles->findOneBySlug($slug);
        if (null === $article) {
            throw $this->createNotFoundException('No article found for slug "'.$slug.'".');
        }

        return $this->render('blog/article.html.twig', [
            'article' => $article,
            'more' => \array_slice($this->articles->findAllExcept($slug), 0, 3),
        ]);
    }
}
