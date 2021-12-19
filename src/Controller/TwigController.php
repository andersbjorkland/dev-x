<?php 

namespace App\Controller;

use React\Http\Message\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigController
{
    protected Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $twig = new Environment($loader, [
            'cache' => __DIR__ . '/../../var/cache',
            'debug' => true
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $this->twig = $twig;
    }

    public function render(int $status = 200, array $middleWare = [], string $template = "", array $twigParams = []): Response
    {
        return new Response($status, $middleWare, $this->twig->render($template, $twigParams));
    }
    
}