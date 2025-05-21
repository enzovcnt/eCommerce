<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Product;
use App\Form\CommentForm;
use App\Form\ProductForm;
use App\Repository\CommentRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', priority: -1)]
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

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setProduct($product);
            $comment->setAuthor($this->getUser()->getprofile());
            $comment->setDate(new \DateTime());
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'formComment' => $form,
            'comment' => $comment,
            'averageStars' => $averageStars,
        ]);
    }
    #[Route('/product/create', name: 'app_product_create')]
    public function new(EntityManagerInterface $manager, Request $request): Response
    {
        $product = new Product();
        $formNew = $this->createForm(ProductForm::class, $product);
        $formNew->handleRequest($request);
        if($formNew->isSubmitted() && $formNew->isValid()){
            $product->setDate(new \DateTime());
            $manager->persist($product);
            $manager->flush();
            return $this->redirectToRoute('app_products');
        }

        return $this->render('product/new.html.twig', [
            'formNew' => $formNew->createView(),
        ]);
    }
    #[Route('/product/{id}/edit', name: 'app_product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $manager): Response{
        $formEdit = $this->createForm(ProductForm::class, $product);
        $formEdit->handleRequest($request);
        if($formEdit->isSubmitted() && $formEdit->isValid()){
            $manager->persist($product);
            $manager->flush();
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }
        return $this->render('product/edit.html.twig', [
            'formEdit' => $formEdit->createView(),
        ]);
    }

    #[Route('/product/{id}/delete', name: 'app_product_delete')]
    public function delete(Product $product, EntityManagerInterface $manager): Response{
        $manager->remove($product);
        $manager->flush();
        return $this->redirectToRoute('app_product_index');
    }
}
