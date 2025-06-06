<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Product;
use App\Form\CommentForm;
use App\Form\ImageForm;
use App\Form\ProductForm;
use App\Repository\CommentRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin')]
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
            return $this->redirectToRoute('app_product_addimage', ['id' => $product->getId()]);
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


    #[Route('/product/addimage/{id}', name: 'app_product_addimage')]
    public function addImage(Product $product, Request $request, EntityManagerInterface $manager) : Response
    {
        if(!$this->getUser() || !$product)
        {
            return $this->redirectToRoute('app_login');
        }
        if($this->getUser() && !$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }
        $image = new Image();
        $formImage = $this->createForm(ImageForm::class, $image);
        $formImage->handleRequest($request);
        if($formImage->isSubmitted() && $formImage->isValid()){
            $image->setProduct($product);
            $manager->persist($image);
            $manager->flush();
            return $this->redirectToRoute('app_product_addimage', ['id' => $product->getId()]);
        }


        return $this->render('product/image.html.twig', [
            'product' => $product,
            'formImage' => $formImage->createView(),
        ]);
    }

    #[Route('/product/removeImage/{id}', name: 'app_removeImage')]
    public function removeImage(Image $image, EntityManagerInterface $manager) : Response
    {
        if(!$this->getUser() || !$image)
        {
            return $this->redirectToRoute('app_login');
        }
        if($image->getProduct() && !$this->getUser())
        {
            return $this->redirectToRoute('app_product_show', ['id' => $image->getId()]);
        }
        $productId = $image->getProduct()->getId();
        $manager->remove($image);
        $manager->flush();


        return $this->redirectToRoute('app_product_addimage', ['id' => $productId]);
    }
}
