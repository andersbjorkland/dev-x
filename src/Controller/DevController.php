<?php 

namespace App\Controller;

use App\Service\ArticleParser;
use Exception;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;
use React\Http\Message\Response;

use function React\Async\await;

class DevController extends TwigController
{
    public function __invoke()
    {   
        $devApiKey = $_ENV['DEV_SECRET'];
        $collectionId = $_ENV['DEV_COLLECTION_ID'];
        $browser = new Browser();

        $byMe = 'https://dev.to/api/articles/me';
        $byUser = 'https://dev.to/api/articles?username=andersbjorkland';
        $followers = 'https://dev.to/api/followers/users';
        $byCollection = 'https://dev.to/api/articles?collection_id=' . $collectionId;
        $apiKeyArray = ['api-key' => $devApiKey];

        $promise = $browser->get($byMe, $apiKeyArray);
        $articles = [];
        
        try {
            $response = await($promise);
            assert($response instanceof ResponseInterface);
            $json = json_decode($response->getBody(), true);

            foreach ($json as $article) {
                if ($article['published_at'] >= '2021-11-30' && $article['published_at'] <= '2021-12-25') {
                    $articles[] = ArticleParser::parse($article);
                }
            }

        } catch (Exception $e) {
            return new Response(500, [], 'Something went wrong: ' . $e->getMessage());
        }

        sort($articles);
        $engagement = array_map(function ($article) {
            return [
                'label' => $article['published'],
                'y' => $article['engagement']
            ];
        }, $articles);
        $engagementData = json_encode($engagement);

        $readingTime = array_map(function ($article) {
            return [
                'label' => $article['published'],
                'y' => $article['readingTime']
            ];
        }, $articles);
        $readingTimeData = json_encode($readingTime);

        return $this->render(template: "summary.html.twig", twigParams: [
            "articles" => $articles,
            "engagementData" => $engagementData,
            "readingTimeData" => $readingTimeData,
            "summary" => $this->getMarkdownAnalysis($articles)
        ]);
    }

    function getMarkdownAnalysis($articles): array
    {
        $mostViewed = array_reduce($articles, function ($acc, $article) {
            if ($acc === null) {
                return $article;
            }
            if ($article['views'] > $acc['views']) {
                $acc = $article;
            }
            return $acc;
        });

        $mostReactions = array_reduce($articles, function ($acc, $article) {
            if ($acc === null) {
                return $article;
            }

            if ($article['reactions'] > $acc['reactions']) {
                $acc = $article;
            }
            return $acc;
        });

        $mostEngaged = array_reduce($articles, function ($acc, $article) {
            if ($acc === null) {
                return $article;
            }

            if ($article['engagement'] > $acc['engagement']) {
                $acc = $article;
            }
            return $acc;
        });

        $mostReadingTime = array_reduce($articles, function ($acc, $article) {
            if ($acc === null) {
                return $article;
            }

            if ($article['readingTime'] > $acc['readingTime']) {
                $acc = $article;
            }
            return $acc;
        });

        $totalReadTime = array_reduce($articles, function ($acc, $article) {
            $acc += $article['readingTime'];
            return $acc;
        });

        $totalReactions = array_reduce($articles, function ($acc, $article) {
            $acc += $article['reactions'];
            return $acc;
        });

        $totalViews = array_reduce($articles, function ($acc, $article) {
            $acc += $article['views'];
            return $acc;
        });

        $numberOfArticles = count($articles);

        return [
            "mostViewed" => $mostViewed,
            "mostReactions" => $mostReactions,
            "mostEngaged" => $mostEngaged,
            "mostReadingTime" => $mostReadingTime,
            "totalReadTime" => $totalReadTime,
            "totalReactions" => $totalReactions,
            "totalViews" => $totalViews,
            "numberOfArticles" => $numberOfArticles
        ];
    }
}