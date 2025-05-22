<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Product;
use App\Form\CommentForm;
use App\Form\ProductQuantityForm;
use App\Repository\CommentRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



final class ShoppingController extends AbstractController
{
    #[Route('/shopping', name: 'app_shopping')]
    public function index(ProductRepository $productRepository): Response
    {


        return $this->render('shopping/index.html.twig', [
            'products' => $productRepository->findAll(),

        ]);
    }

    #[Route('/shopping/{id}', name: 'app_shopping_product_show')]
    public function show(Product $product, EntityManagerInterface $manager, Request $request, CommentRepository $commentRepository): Response
    {

        if(!$this->getUser() || !$product)
        {
            return $this->redirectToRoute('app_login');
        }
        $averageStars = $commentRepository->getAverageStarsForProduct($product);
        $comment = new Comment();
        $form = $this->createForm(CommentForm::class, $comment);
        $form->handleRequest($request);
        $quantityForm = $this->createForm(ProductQuantityForm::class);
        $quantityForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setProduct($product);
            $comment->setAuthor($this->getUser()->getprofile());
            $comment->setDate(new \DateTime());
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('app_shopping_product_show', ['id' => $product->getId()]);
        }
        return $this->render('shopping/show.html.twig', [
            'product' => $product,
            'formComment' => $form,
            'comment' => $comment,
            'averageStars' => $averageStars,
            'quantityForm' => $quantityForm->createView(),
        ]);
    }
}
