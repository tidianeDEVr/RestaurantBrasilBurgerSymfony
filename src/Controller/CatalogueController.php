<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MenuRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CatalogueController extends AbstractController
{
    #[Route('/catalogue', name: 'catalogue')]
    public function showCatalogue(MenuRepository $repo, CategoryRepository $repoC, SessionInterface $session): Response
    {
        $categories = $repoC->findAll();
        $menus = [];
        $filtres = $session->get('filtres', []);
        if(count($filtres)>0){
            foreach ($filtres as $filtre) {
                $category = $repoC->findOneBy(['libelle'=>$filtre]);
                $menusCategory = $category->getMenus();
                foreach ($menusCategory as $mc) {
                    array_push($menus, $mc);    
                }
            }
        }   
        else{ 
            $menus = $repo->findAll();
        }
        return $this->render('catalogue/index.html.twig', [
            'controller_name' => 'CatalogueController',
            'menus' => $menus,
            'categories' => $categories,
            'filtres' => $filtres,
        ]);
    }

    #[Route('/catalogue/remove-filtre', name: 'remove_filtre')]
    public function removeFiltre(SessionInterface $session){  
        $filtres = $session->get('filtres', []);
        if(!empty($filtres)){
            $session->set('filtres', []);
        }
        return $this->redirectToRoute("catalogue");
    }
    
    #[Route('/catalogue/{filtre}', name: 'catalogue_filtre')]
    public function addFilter($filtre, SessionInterface $session)
    {
        $filtres = $session->get('filtres', []);
        if(in_array($filtre, $filtres)){
            unset($filtres[array_search($filtre, $filtres)]);
        }else{
            array_push($filtres, $filtre);
        }
        $session->set('filtres', $filtres);
        return $this->redirectToRoute("catalogue");
    }
}
