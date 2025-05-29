<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Knp\Snappy\Pdf;




class EmailService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private Pdf $pdf;



    public function __construct(MailerInterface $mailer, Environment $twig, Pdf $pdf)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->pdf = $pdf;
    }

    public function sendOrderConfirmation(string $to, string $emailUser, float $total,): void
    {

        $htmlContent = $this->twig->render('emails/orderConfirmation.html.twig', [
            'emailUser' => $emailUser
        ]);


        $pdfContent = $this->pdf->getOutputFromHtml(
            $this->twig->render('pdf/facture.html.twig', [
                'emailUser' => $emailUser,
                'total' => $total,
            ])
        );

        $email = (new Email())
            ->from('mail@enzovincent.org')
            ->to($to)
            ->subject('Confirmation de votre commande')
            ->html($htmlContent)
            ->attach($pdfContent, 'facture.pdf', 'application/pdf');

        $this->mailer->send($email);
    }


}