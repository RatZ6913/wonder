<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(QuestionRepository $questionRepository): Response
    {
        // $questionRepo = $this->getDoctrine()->getRepository(Question::class);
        $questions = $questionRepository->findAll();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'questions' => $questions
        ]);
    }
}

// https://randomuser.me/api/portraits/women/32.jpg
// https://randomuser.me/api/portraits/women/57.jpg