<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Profile;
use App\Form\AddressForm;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AddressController extends AbstractController
{
    #[Route('/profile/{id}/address', name: 'app_address')]
    public function index(Profile $profile, EntityManagerInterface $manager, Request $request): Response
    {
        $address = new Address();
        $formAddress = $this->createForm(AddressForm::class, $address);
        $formAddress->handleRequest($request);
        if ($formAddress->isSubmitted() && $formAddress->isValid()) {
            $address->setOwner($this->getUser()->getProfile());
            $manager->persist($address);
            $manager->flush();
            return $this->redirectToRoute('app_products');
        }

        return $this->render('address/index.html.twig', [
            'profile' => $profile,
            'formAddress' => $formAddress->createView(),
        ]);
    }

}
