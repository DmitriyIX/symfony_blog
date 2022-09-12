<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractController
{
    #[Route('/admin')]
    public function index(): Response
    {
        die('dashboard');
        //return new Response (
        //    'dashboard'
        //);
    }
}
