<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\CommentRate;
use App\Form\CommentRateForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentRateController extends AbstractController
{
    #[Route('/comment/rate/{id}', name: 'app_comment_rate')]
    public function rate(
        Comment $comment,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $user = $this->getUser();
        if (!$user || !$comment) {
            return $this->redirectToRoute('app_login');
        }

        $profile = $user->getProfile();

//        foreach ($comment->getCommentRates() as $existingRate) {
//            if ($existingRate->getAuthor() === $profile) {
//                $this->addFlash('error', 'Vous avez déjà noté ce commentaire.');
//                return $this->redirectToRoute('app_shopping_product_show', [
//                    'id' => $comment->getProduct()->getId()
//                ]);
//            }
//        }

        $commentRate = new CommentRate;
        $form = $this->createForm(CommentRateForm::class, $commentRate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentRate->setAuthor($profile);
            $commentRate->setComment($comment);
            $manager->persist($commentRate);
            $manager->flush();

            $this->addFlash('success', 'Votre vote a été enregistré.');
            return $this->redirectToRoute('app_shopping_product_show', [
                'id' => $comment->getProduct()->getId()
            ]);
        }

        return $this->render('comment/rate.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
        ]);
    }

}
