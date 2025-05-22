<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cartService): Response
    {

        return $this->render('cart/index.html.twig', [
            'cart' => $cartService->getCart(),
            'total' => $cartService->getTotal(),
        ]);
    }
    #[Route('/cart/add/{id}/{quantity}', name: 'app_cart_add')]
    public function add(Product $product, int $quantity, CartService $cartService): Response
    {
        if(!$product)
        {
            return $this->redirectToRoute('app_shopping');
        }
        $cartService->addToCart($product, $quantity);
        return $this->redirectToRoute('app_cart');

    }

    #[Route('/cart/remove/{id}/{quantity}', name: 'app_cart_remove_one_product')]
    public function removeOneProduct(Product $product, int $quantity, CartService $cartService): Response{
        if(!$product){
            return $this->redirectToRoute('app_shopping');
        }
        $cartService->removeOneUnitFromCart($product, $quantity);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear/product/{id}', name: 'app_cart_clear_product')]
    public function clearOneProduct(Product $product, CartService $cartService): Response{
        if(!$product){
            return $this->redirectToRoute('app_shopping');
        }
        $cartService->removeProductFromCart($product);
        return $this->redirectToRoute('app_cart');
    }
}
