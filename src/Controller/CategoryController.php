<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryForm;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {

        $category = new Category();
        $formCat = $this->createForm(CategoryForm::class, $category);
        $formCat->handleRequest($request);
        if ($formCat->isSubmitted() && $formCat->isValid()) {
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/index.html.twig', [
            'category' => $repository->findAll(),
            'formCat' => $formCat->createView(),
        ]);
    }
}
