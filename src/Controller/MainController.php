<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', host: 'www.bucket.local')]
class MainController extends AbstractController
{
    #[Route('/about-us', name: 'aboutUs')]
    #[Template]
    public function aboutUs()
    {
        return [
            'text' => 'Lorem ipsum...'
        ];
    }

    #[Route('/legal-stuff', name: 'legalStuff')]
    #[Template]
    public function legalStuff()
    {
        return [
            'text' => 'Mentions légales...'
        ];
    }
}
