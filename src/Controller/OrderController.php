<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\AddressRepository;
use App\Repository\OrderRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/order/billingAddress', name: 'app_order_billingAddress')]
    public function billingAddress(): Response
    {
        return $this->render('order/billingAddress.html.twig');
    }
    #[Route('/order/shippingAddress/{id}', name: 'app_order_shippingAddress')]
    public function shippingAddress(Address $address): Response
    {
        return $this->render('order/shippingAddress.html.twig',[
            'billingAddress' => $address
        ]);
    }
    #[Route('/order/payment/{idBilling}/{idShipping}', name: 'app_order_payment')]
    public function payment(
        CartService $cartService,AddressRepository $addressRepository,
        $idBilling, $idShipping
    ): Response
    {
        $billing = $addressRepository->find($idBilling);
        $shipping = $addressRepository->find($idShipping);


        return $this->render('order/payment.html.twig',[
            'billing' => $billing,
            'shipping' => $shipping,
            'cart'=> $cartService->getCart(),
            'total'=>$cartService->getTotal(),
        ]);
    }
//    #[Route('/order/validate/{idBilling}/{idShipping}', name: 'app_order_validate')]
//    public function validate(EntityManagerInterface $manager,AddressRepository $addressRepository, $idBilling, $idShipping, CartService $cartService): Response
//    {
//
//        $billing = $addressRepository->find($idBilling);
//        $shipping = $addressRepository->find($idShipping);
//
//        $order = new Order();
//        $order->setBillingAddress($billing);
//        $order->setShippingAddress($shipping);
//        $order->setCustomer($this->getUser()->getProfile());
//        $order->setTotal($cartService->getTotal());
//        $order->setStatus(1);
//        $manager->persist($order);
//
//
//        foreach ($cartService->getCart() as $cartItem) {
//            $product = $cartItem["product"];
//            $quantity = $cartItem["quantity"];
//            if (!$product->isInStock($quantity)) {
//                $this->addFlash('error', 'Stock insuffisant pour ' . $product->getName());
//                return $this->redirectToRoute('app_shopping');
//            }
//
//            $orderItem = new OrderItem();
//            $orderItem->setProduct($cartItem["product"]);
//            $orderItem->setQuantity($cartItem["quantity"]);
//            $product->decreaseStock($quantity);
//            $orderItem->setOfOrder($order);
//            $manager->persist($orderItem);
//            $manager->persist($product);
//        }
//        $manager->flush();
//        $cartService->emptyCart();
//        return $this->redirectToRoute('app_my_orders');
//    }
    #[Route('/myOrders', name: 'app_my_orders')]
    public function myOrders(): Response
    {
        return $this->render('order/myOrders.html.twig', [

        ]);
    }

    #[Route('/order/prepare/{idBilling}/{idShipping}', name: 'app_order_prepare')]
    public function prepareOrder(
        EntityManagerInterface $manager,
        AddressRepository $addressRepository,
        CartService $cartService,
                               $idBilling,
                               $idShipping
    ): Response {
        $billing = $addressRepository->find($idBilling);
        $shipping = $addressRepository->find($idShipping);

        $order = new Order();
        $order->setBillingAddress($billing);
        $order->setShippingAddress($shipping);
        $order->setCustomer($this->getUser()->getProfile());
        $order->setTotal($cartService->getTotal());
        $order->setStatus(0);
        $manager->persist($order);
        $manager->flush();

        return $this->redirectToRoute('stripe_checkout', [
            'orderId' => $order->getId(),
            'idBilling' => $idBilling,
            'idShipping' => $idShipping,
        ]);
    }

}
