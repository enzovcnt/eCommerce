<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employee')]
final class EmployeeController extends AbstractController
{
    #[Route('/orders', name: 'app_employee')]
    public function index(OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYEE');

        return $this->render('employee/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

    #[Route('/order/{id}', name: 'app_employee_show')]
    public function show(Order $order): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYEE');

        return $this->render('employee/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/order/status/{id}/{status}', name: 'app_employee_status')]
    public function changeStatus(Order $order, int $status, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYEE');

        if (!in_array($status, [1, 2, 3])) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('app_employee_show', ['id' => $order->getId()]);
        }

        $order->setStatus($status);
        $entityManager->flush();

        $this->addFlash('success', 'Statut mis à jour.');
        return $this->redirectToRoute('app_employee_show', ['id' => $order->getId()]);
    }
}
