<?php

namespace App\Controller;

use App\Form\ProductQuantityForm;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShoppingController extends AbstractController
{
    #[Route('/shopping', name: 'app_shopping')]
    public function index(ProductRepository $productRepository): Response
    {
        $quantityForm = $this->createForm(ProductQuantityForm::class);

        return $this->render('shopping/index.html.twig', [
            'products' => $productRepository->findAll(),
            'quantityForm'=> $quantityForm,
        ]);
    }
}
