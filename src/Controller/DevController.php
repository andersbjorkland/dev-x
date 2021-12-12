<?php 

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;
use React\Http\Message\Response;

class DevController
{
    public function __invoke()
    {
        $browser = new Browser();

        return $browser
            ->get('https://dev.to/api/articles?username=andersbjorkland&per_page=1')
            ->then(
                onFulfilled: function (ResponseInterface $response) {
                    $json = json_decode($response->getBody(), true);
                    $latestArticle = $json[0];
                    return $this->parseJsonArticleToResponse($latestArticle);
                },
                onRejected: function () {
                    return new Response(body: "<p>Well, that didn't work. But HEY: Merry Christmas!</p>");
                }
            );

    }

    protected function parseJsonArticleToResponse($article): Response 
    {
        $title = $article['title'];
        $description = $article['description'];
        $url = $article['url'];
        
        $body = <<<BODY
        <h1>$title</h1>
        <p>$description</p>
        <a href="$url">$url</a>
        BODY;

        return new Response(body: $body);
    }
}