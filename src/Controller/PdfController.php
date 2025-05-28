<?php
// src/Controller/PdfController.php
namespace App\Controller;

use App\Entity\Order;
use App\Service\CartService;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Knp\Snappy\Pdf;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    private Pdf $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    #[Route('/pdf/{id}', name: 'generate_pdf')]
    public function generatePdf(Order $order): Response
    {
        $user = $this->getUser();
        $roles = $user->getRoles();

        if (
            $user->getProfile() !== $order->getCustomer() &&
            !in_array('ROLE_ADMIN', $roles) &&
            !in_array('ROLE_EMPLOYEE', $roles)
        ) {
            return $this->redirectToRoute('app_login');
        }
        $html = $this->renderView('pdf/template.html.twig', [
            'order'=>$order,

        ]);

        $pdfOutput = $this->pdf->getOutputFromHtml($html);

        return new PdfResponse($pdfOutput, "recap.pdf");
    }
}