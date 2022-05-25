<?php

namespace App\Controller;

use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MenuRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier')]
    public function index(MenuRepository $repo, SessionInterface $session): Response
    {
        $menus = $repo->findAll();
        $panier = $session->get('panier', []);
        $panierWithData = [];
        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'product' => $repo->find($id),
                'quantity' => $quantity
            ];
        }

        $total = 0;
        foreach ($panierWithData as $item){
            $total += ($item['product']->getPrix())*($item['quantity']);
        }
        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
            'menus' => $menus,
            'selectedItems' => $panierWithData,
            'total' => $total
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
        return $this->redirectToRoute("catalogue");
    }

    #[Route('panier/remove/{id}', name:'cart_remove')]
    public function remove($id, SessionInterface $session){
        $panier = $session->get('panier', []);
        if(!empty($panier[$id])){
            unset($panier[$id]);
        }
        $session->set('panier', $panier);
        return $this->redirectToRoute("panier");
    }
}
