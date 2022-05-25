<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MenuRepository;

class MenuController extends AbstractController
{
    #[Route('menu/{id}', name: 'menu', methods:['GET'])]
    public function index($id, MenuRepository $repo): Response
    {
        $menu = $repo->find($id);
        return $this->render('menu/index.html.twig', [
            'controller_name' => 'MenuController',
            'menu' => $menu,
        ]);
    }
}
