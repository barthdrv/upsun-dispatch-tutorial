<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Blog\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BlogControllerTest extends WebTestCase
{
    public function testHomepageRendersTheFeaturedArticleOnlyInTheHero(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();

        $featured = $this->repository()->findFeatured();
        self::assertNotNull($featured, 'The content set must contain at least one article.');

        $heroLinks = $crawler->filter('a[href="/articles/'.$featured->slug.'"]');
        self::assertCount(
            1,
            $heroLinks,
            'The featured article must be linked exactly once on the homepage (the hero card).',
        );
    }

    public function testHomepageGridListsEveryArticleButTheFeaturedOne(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();

        $expected = \count($this->repository()->findAll()) - 1;

        $gridLinks = $crawler->filter('#categories a[href^="/articles/"]');
        self::assertCount($expected, $gridLinks);

        self::assertSame(
            $expected.' post'.(1 === $expected ? '' : 's'),
            trim($crawler->filter('#categories span')->first()->text()),
        );
    }

    public function testArticlePageStillShowsUpToThreeMoreArticles(): void
    {
        $client = static::createClient();

        $featured = $this->repository()->findFeatured();
        self::assertNotNull($featured);

        $crawler = $client->request('GET', '/articles/'.$featured->slug);

        self::assertResponseIsSuccessful();
        self::assertCount(
            0,
            $crawler->filter('a[href="/articles/'.$featured->slug.'"]'),
            'An article never links to itself in its "more articles" rail.',
        );
        self::assertCount(3, $crawler->filter('a[href^="/articles/"]'));
    }

    /**
     * The repository takes its directory as a plain string, so the committed
     * content set can be read without going through the container.
     */
    private function repository(): ArticleRepository
    {
        return new ArticleRepository(\dirname(__DIR__, 2).'/content/articles');
    }
}
