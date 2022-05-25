<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\MenuRepository;
use Doctrine\ORM\Mapping\Id;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(SessionInterface $session, MenuRepository $menuRepository): Response
    {
        $panier = $session->get('panier', []);
        $panierWithData = [];
        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'product' => $menuRepository->find($id),
                'quantity' => $quantity
            ];
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'selectedItems' => $panierWithData
        ]);
    }

    #[Route('/panier/add/{id}', name: 'cart_add')]
    public function addToCart($id, SessionInterface $session)
    {
        $panier = $session->get('panier', []);
        if(!empty($panier[$id])){
            $panier[$id]++;
        }else{
            $panier[$id]=1;
        }
        $session->set('panier', $panier);
    }
}
