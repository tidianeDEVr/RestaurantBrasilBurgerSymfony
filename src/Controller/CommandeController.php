<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommandeRepository;
use App\Entity\Commande;
use App\Entity\Menu;
use App\Repository\MenuRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

class CommandeController extends AbstractController
{
    #[Route('/commands', name: 'my_commands')]
    public function index(CommandeRepository $repo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $user = $this->getUser();
        $commandes=$repo->findby(['client'=>$user]);

        return $this->render('commande/index.html.twig', [
            'controller_name' => 'CommandeController',
            'commandes' => $commandes,
        ]);
    }


    #[Route('/add-command', name: 'make_command')]
    public function makeCommand(CommandeRepository $repo, MenuRepository $repoM, SessionInterface $session, 
    EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $menus = $session->get('panier', []);
        if($menus!=null){
            $commande = new Commande;
            $commande->setEtat("En cours")
                    ->setClient($this->getUser())
                    ->setCommandedAt(new \DateTimeImmutable());
            foreach ($menus as $key=>$menu) {
                $mn = new Menu;
                $mn = $repoM->find($key);
                for ($i=0; $i < $menu ; $i++) { 
                    $commande->addMenu($mn);
                }
            }
            $em->persist($commande);
            $em->flush();
        }else{
            return $this->redirectToRoute('home');
        }
        // On supprime ce qu'il y a dans le panier
        $session->set('panier', []);
        return $this->redirectToRoute('my_commands');
    }
}
