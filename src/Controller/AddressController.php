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
    #[Route('/profile/address', name: 'app_address')]
    public function index(EntityManagerInterface $manager, Request $request): Response
    {
        $profile = $this->getUser()->getProfile();
        if (!$profile) {
            $this->redirectToRoute('app_login');
        }

        $address = new Address();
        $formAddress = $this->createForm(AddressForm::class, $address);
        $formAddress->handleRequest($request);
        if ($formAddress->isSubmitted() && $formAddress->isValid()) {
            $address->setOwner($profile);
            $manager->persist($address);
            $manager->flush();

            return $this->redirectToRoute('app_shopping');
        }

        return $this->render('address/index.html.twig', [
            'profile' => $profile,
            'formAddress' => $formAddress->createView(),
        ]);
    }


}
