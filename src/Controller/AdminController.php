<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/order/validate', name: 'app_admin')]
    public function index(OrderRepository $repository): Response
    {
        return $this->render('admin/index.html.twig', [
            'orders'=> $repository->findAll(),
        ]);
    }
}
