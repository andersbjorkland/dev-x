<?php

namespace App\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;
use React\Http\Message\Response;

use function React\Async\await;

class ApiController
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
                    $articles[] = $this->parseArticle($article);
                }
            }

        } catch (Exception $e) {
            return new Response(500, [], 'Something went wrong: ' . $e->getMessage());
        }

        sort($articles);
        return new Response(200, [], $this->renderTrend($articles));
    }

    protected function parseArticle(array $article): array
    {
        $readingTime = $article['reading_time_minutes'];
        $publishedAt = $article['published_at'];
        $published = (new \DateTime($publishedAt))->format('d M');
        $tags = $article['tag_list'];
        $reactions = $article['positive_reactions_count'];
        $id = $article['id'];
        $views = $article['page_views_count'];
        $title = $article['title'];
        $url = $article['canonical_url'];
        $commentsCount = $article['comments_count'];
        $engagement = $reactions + $commentsCount;

        $parsedArticle = [
            'id' => $id,
            'views' => $views,
            'title' => $title,
            'reactions' => $reactions,
            'readingTime' => $readingTime,
            'published' => $published,
            'publishedAt' => $publishedAt,
            'tags' => $tags,
            'url' => $url,
            'engagement' => $engagement,
        ];

        return $parsedArticle;
    }

    protected function renderTrend(array $articles)
    {

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

        $md = $this->getMarkdownAnalysis($articles);
        $html = <<<ARTICLE

<!DOCTYPE HTML>
<html>
<head>
<script>
window.onload = function () {
 
var chart = new CanvasJS.Chart("chartContainer", {
    animationEnabled: true,
    exportEnabled: true,
	theme: "light2",
	title: {
		text: "The C in PHP stands for Christmas"
	},
	axisY: {
		title: "Engagement",
        titleFontColor: "#6D78AD",
	},
    axisY2: {
        title: "Reading Time (minutes)",
    },
	data: [{
		type: "line",
        name: "Engagement",
        showInLegend: true,
		dataPoints: $engagementData
	},
    {
        type: "line",
        name: "Reading Time",
        axisYType: "secondary",
        showInLegend: true,
        dataPoints: $readingTimeData
    }]
});
chart.render();
 
}
</script>
</head>
<body>
<div id="chartContainer" style="height: 370px; width: 100%;"></div>
<h3>Markdown analysis</h3>
<pre>$md</pre>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>
ARTICLE;

        return $html;

    }

    function getMarkdownAnalysis($articles): string
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

        $md = <<<ARTICLE

        ## So long and thanks for all the code!
        This is the final article in my series "The C in PHP stands For Christmas". When I started writing this series I hoped to share all that I learned along the way, and hopefully learn something from you as well. I can happily say that I have grown.

        All in all, I have written $numberOfArticles articles, and I dare not think about how many hours of reading and writing that entail (but WakaTime have counted it for me). But let me share with you a bit of your participation in this series.

        My most popular (or infamous) article is probably [{$mostViewed['title']}]({$mostViewed['url']}). It has been viewed {$mostViewed['views']} times. Exceeding by far the view counts that I'm used to. This is however quite an arbitrary measurement as many views came from outside of DEV, which meant less engagement. Which article garnered the most engagement then?
        Well, it was [{$mostReactions['title']}]({$mostReactions['url']}). It got {$mostEngaged['reactions']}. I like to see what you are interested in, and perhaps it was a witty title, or an engaging subject. Overall, it was pretty fun to write.  

        Now, if you were to read all of my articles, DEV expects you to spend about {$totalReadTime} minutes reading them. Myself am quite a slow reader when it comes to technical subjects so for me it would perhaps be double that. 

        And in closing, I want to thank all of you that have read my articles - be it just in passing. You have viewed my articles $totalViews times, and reacted in some way $totalReactions times. These are not astronomical numbers but they have meant a lot to me. 
        I don't know if I'll manage to do something like this again, but I'm happy that I have been able to do it this time.  
        **Merry Christmas, and a happy New Year!** 

        ARTICLE;

        return $md;
    }

}