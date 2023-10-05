<?php

namespace App\Controller;

use App\Entity\Suivi;
use App\Form\SuiviType;
use App\Repository\SuiviRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, UserRepository $userRepository, EntityManagerInterface $em, SuiviRepository $suiviRepository): Response
    {
        $suivi = new Suivi;
        $form = $this->createForm(SuiviType::class, $suivi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $req = $userRepository->findOneBy(['token' => $form['user']['token']->getData()]);
            if ($req) {
                $verifLastPoint = $suiviRepository->findOneBy(['user' => $req], ['createdAt' => 'DESC']);
                if (($form['isStart']->getData() && $form['isEnd']->getData()) == 0) {
                    $form['isStart']->addError(new FormError('Veuillez selectionner au moins une des deux cases'));
                } else {
                    // if ($verifLastPoint->isIsStart() == $form['isStart']->getData()) {
                    //     # code...
                    // }
                    $suivi->setCreatedAt(new \DateTimeImmutable);
                    $suivi->setUser($req);

                    $em->persist($suivi);
                    $em->flush();

                    $this->addFlash('success', '');
                }
            } else {
                $form['user']['token']->addError(new FormError('Ce code n\'existe pas'));
            }
        }

        return $this->render('home/index.html.twig', [
            'formSuivi' => $form
        ]);
    }
}
