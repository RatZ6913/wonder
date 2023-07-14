<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $questions = [
            [
            'id' => '1',
            'title' => 'Je suis une super question',
            'content' => '1 Lorem ipsum dolor sit amet consectetur adipisicing elit. Cum voluptatum harum quo similique neque quam, adipisci tenetur facere. Odio ducimus tenetur distinctio in accusantium beatae fugit vel blanditiis omnis aspernatur!',
            'rating' => 20,
            'author' => [
                'name' => 'Mathilde Blabla',
                'avatar' => 'https://randomuser.me/api/portraits/women/82.jpg'
            ],
            'nbrOfResponse' => 15
        ],
        [
            'id' => '2',
            'title' => 'Je suis une super question',
            'content' => '2 Lorem ipsum dolor sit amet consectetur adipisicing elit. Cum voluptatum harum quo similique neque quam, adipisci tenetur facere. Odio ducimus tenetur distinctio in accusantium beatae fugit vel blanditiis omnis aspernatur!',
            'rating' => 0,
            'author' => [
                'name' => 'Camila Toto',
                'avatar' => 'https://randomuser.me/api/portraits/women/32.jpg'
            ],
            'nbrOfResponse' => 15
        ],
        [
            'id' => '3',
            'title' => 'Je suis une super question',
            'content' => '3 Lorem ipsum dolor sit amet consectetur adipisicing elit. Cum voluptatum harum quo similique neque quam, adipisci tenetur facere. Odio ducimus tenetur distinctio in accusantium beatae fugit vel blanditiis omnis aspernatur!',
            'rating' => -15,
            'author' => [
                'name' => 'Solene Denie',
                'avatar' => 'https://randomuser.me/api/portraits/women/57.jpg'
            ],
            'nbrOfResponse' => 15
            ]
        ];

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'questions' => $questions
        ]);
    }
}
