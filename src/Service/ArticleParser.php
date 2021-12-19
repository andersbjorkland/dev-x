<?php 

namespace App\Service;

class ArticleParser
{
    public static function parse(array $article):array
    {
        $engagement = ($article['positive_reactions_count'] + $article['comments_count'])/$article['page_views_count'];
        $engagement = round($engagement, 2) * 100;

        $parsedArticle = [
            'id' => $article['id'],
            'views' => $article['page_views_count'],
            'title' => $article['title'],
            'reactions' => $article['positive_reactions_count'] ?? 0,
            'readingTime' => $article['reading_time_minutes'] ?? 0,
            'published' => (new \DateTime($article['published_at']))->format('d M'),
            'publishedAt' => $article['published_at'],
            'tags' => $article['tag_list'],
            'url' => $article['canonical_url'],
            'engagement' => $engagement,
        ];

        return $parsedArticle;
    }

    public static function getMarkdownAnalysis(array $articles):string
    {
        $md = '';
        foreach ($articles as $article) {
            $md .= '# ' . $article['title'] . PHP_EOL;
            $md .= '* ' . $article['url'] . PHP_EOL;
            $md .= '* ' . $article['published'] . PHP_EOL;
            $md .= '* ' . $article['readingTime'] . ' min read' . PHP_EOL;
            $md .= '* ' . $article['reactions'] . ' reactions' . PHP_EOL;
            $md .= '* ' . $article['engagement'] . '% engagement' . PHP_EOL;
            $md .= '* ' . $article['tags'] . PHP_EOL;
            $md .= PHP_EOL;
        }

        return $md;
    }
}