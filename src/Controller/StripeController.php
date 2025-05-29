<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\AddressRepository;
use App\Service\CartService;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    #[Route('/createCheckoutSession/{orderId}/{idBilling}/{idShipping}', name: 'stripe_checkout')]
    public function checkout(Order $orderId, CartService $cartService, UrlGeneratorInterface $urlGenerator, $idBilling, $idShipping): Response {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $cart = $cartService->getCart();
        $lineItems = [];

        foreach ($cart as $item) {
            $product = $item['product'];
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                    'unit_amount' => $product->getPrice() * 100,
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $urlGenerator->generate('app_order_validate', ['orderId' => $orderId, 'idBilling'=> $idBilling, 'idShipping' =>$idShipping], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $urlGenerator->generate('don_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url, 303);
    }
    #[Route('/order/validate/{idBilling}/{idShipping}', name: 'app_order_validate')]
    public function validate(EntityManagerInterface $manager,AddressRepository $addressRepository, $idBilling, $idShipping, CartService $cartService, EmailService $emailService): Response
    {
        $user = $this->getUser();

        $billing = $addressRepository->find($idBilling);
        $shipping = $addressRepository->find($idShipping);

        $order = new Order();
        $order->setBillingAddress($billing);
        $order->setShippingAddress($shipping);
        $order->setCustomer($this->getUser()->getProfile());
        $order->setTotal($cartService->getTotal());
        $order->setStatus(1);
        $manager->persist($order);
        $manager->flush();


        foreach ($cartService->getCart() as $cartItem) {
            $product = $cartItem["product"];
            $quantity = $cartItem["quantity"];
            if (!$product->isInStock($quantity)) {
                $this->addFlash('error', 'Stock insuffisant pour ' . $product->getName());
                return $this->redirectToRoute('app_shopping');
            }

            $orderItem = new OrderItem();
            $orderItem->setProduct($cartItem["product"]);
            $orderItem->setQuantity($cartItem["quantity"]);
            $product->decreaseStock($quantity);
            $orderItem->setOfOrder($order);
            $manager->persist($orderItem);
            $manager->persist($product);
        }
        $manager->flush();
        $cartService->emptyCart();

        $emailService->sendOrderConfirmation(
            $user->getEmail(),
            $user->getEmail(),
            $order->getTotal(),
        );

        return $this->redirectToRoute('app_my_orders');
    }

    #[Route('/don-success', name: 'don_success')]
    public function success(): Response
    {
        return $this->render('stripe/success.html.twig');
    }

    #[Route('/don-cancel', name: 'don_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/fail.html.twig');
    }
}