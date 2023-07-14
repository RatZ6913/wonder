<?php

namespace App\Controller;

use App\Form\QuestionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    public function index(Request $request): Response
    {
        $formQuestion = $this->createForm(QuestionType::class);

        $formQuestion->handleRequest($request);

        if ($formQuestion->isSubmitted() && $formQuestion->isValid())
        {
            dump($formQuestion->getData());
        }

        return $this->render('question/index.html.twig', [
            'controller_name' => 'QuestionController',
            'form' => $formQuestion->createView()
        ]);
    }

    #[Route('/question/{id}', name: 'question_show')]
    public function show(Request $request, string $id): Response
    {
        $question = [
            'title' => 'Je suis une super question',
            'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Cum voluptatum harum quo similique neque quam, adipisci tenetur facere. Odio ducimus tenetur distinctio in accusantium beatae fugit vel blanditiis omnis aspernatur!',
            'rating' => 20,
            'author' => [
                'name' => 'Mathilde Blabla',
                'avatar' => 'https://randomuser.me/api/portraits/women/82.jpg'
            ],
            'nbrOfResponse' => 15
        ];

        return $this->render('question/show.html.twig', [
            'controller_name' => 'QuestionController',
            'question' => $question
        ]);
    }
}
