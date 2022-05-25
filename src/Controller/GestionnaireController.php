<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Produit;
use App\Entity\Menu;
use App\Entity\Commande;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\CommandeRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use App\Repository\MenuRepository;
use App\Repository\ProduitRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GestionnaireController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(CommandeRepository $repo, ClientRepository $repoClient, MenuRepository $repoM): Response
    {
        $this->security();
        ///////////////////////////
        $commandes = $repo->findAll();
        $clients = $repoClient->findAll();
        $menus = $repoM->findAll();
        ///////////////////////////
        $revenu=0;
        $nombreClient=0;
        $commandeValide=0;
        $commandeInvalide=0;
        $commandeEnCours=0;
        $menu = $menus[0];
        ///////////////////////////
        foreach ($commandes as $commande) {
            if($commande->getEtat()=='Prete'){
                $commandeValide++;
                $revenu += $commande->getPrix();
            }elseif($commande->getEtat()=='Annuler'){
                $commandeInvalide++;
            }else{
                $commandeEnCours++;
            }
        }
        foreach ($clients as $client) {
            $nombreClient++;
        }
        foreach ($menus as $mn){
            if(count($menu->getCommandes())<count($mn->getCommandes())){
                $menu = $mn;
            }
        }
        return $this->render('gestionnaire/index.html.twig', [
            'controller_name' => 'GestionnaireController',
            'commandes' => $commandes,
            'nombreClients' => $nombreClient,
            'revenu' => $revenu,
            'menuPlusVendu' => $menu,
            'commandeValide' => $commandeValide,
            'commandeInvalide' => $commandeInvalide,
            'commandeEnCours'=> $commandeEnCours,
        ]);
    }

    // ******************FONCTIONS DE RENDUES******************
    public function security(){
        if($this->getUser()){
            if($this->getUser()->getRoles()[0]!='ROLE_GESTIONNAIRE'){
                return $this->redirectToRoute('home');
            }
        }else{
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/dashboard/category', name: 'dashboard_category')]
    #[Route('/dashboard/category/{id}', name: 'category_edit')]
    public function catalogueCategories(Category $category = null,
                                        CategoryRepository $repoC,
                                        Request $request,
                                        EntityManagerInterface $manager
                                        ): Response
    {
        $this->security();
        $categories = $repoC->findAll();
        
        if(!$category){
            $category = new Category();
        }

        $form = $this->createFormBuilder($category)
                    ->add('libelle', TextType::class, [
                        'attr' => [
                            'placeholder' => 'Le nom de la categorie',
                            'class' => 'form-control'
                        ]
                    ])
                    ->add('description', TextareaType::class, [
                        'attr' => [
                            'placeholder' => 'La description de la categorie',
                            'class' => 'form-control'
                        ]
                    ])
                    ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('dashboard_category');
        }

        return $this->render('gestionnaire/category.html.twig', [
            'controller_name' => 'GestionnaireController',
            'categories' => $categories,
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/category/rm/{id}', name: 'category_remove')]
    public function removeCategory(Category $category,
                                    EntityManagerInterface $manager): Response
    {
        if($category){
            $manager->remove($category);
            $manager->flush();
            return $this->redirectToRoute('dashboard_category');
        }
        return $this->render('gestionnaire/category.html.twig', [
            'controller_name' => 'GestionnaireController',
        ]);
    }

    #[Route('/dashboard/command', name: 'dashboard_command')]
    #[Route('/dashboard/command/vl/{id}', name: 'valid_command')]
    public function catalogueCommands(Commande $commande = null,
                                      EntityManagerInterface $em,
                                      CommandeRepository $repo
                                      ): Response
    {
        $this->security();
        $commandes = $repo->findAll();
        if($commande){
            $commande->setEtat('Prete');
            $em->persist($commande);
            $em->flush();
            return $this->redirectToRoute('dashboard_command');
        }
        return $this->render('gestionnaire/command.html.twig', [
            'controller_name' => 'GestionnaireController',
            'commandes' => $commandes,
        ]);
    }

    #[Route('/dashboard/command/rm/{id}', name: 'remov_command')]
    public function removeCommand(Commande $commande = null,
                                      EntityManagerInterface $em,
                                      CommandeRepository $repo
                                      ): Response
    {
        $this->security();
        $commandes = $repo->findAll();
        if($commande){
            $commande->setEtat('Annulee');
            $em->persist($commande);
            $em->flush();
            return $this->redirectToRoute('dashboard_command');
        }
        return $this->render('gestionnaire/command.html.twig', [
            'controller_name' => 'GestionnaireController',
            'commandes' => $commandes,
        ]);
    }

    #[Route('/dashboard/menu', name: 'dashboard_menu')]
    #[Route('/dashboard/menu/{id}', name: 'menu_edit')]
    public function catalogueMenus(Menu $menu = null,
                                   Request $request,
                                   MenuRepository $repo,
                                   ProduitRepository $repoP): Response
    {
        $this->security();

        $menus = $repo->findAll();
        $produits = $repoP->findAll();
        if(!$menu){
            $menu = new Menu;
        }
        // dd($request->get('produits'));
        $form = $this->createFormBuilder($menu)
                    ->add('libelle', TextType::class, [
                        'attr' => [
                            'placeholder' => 'Le nom du menu',
                            'class' => 'form-control'
                        ]
                    ])
                    ->add('image', FileType::class, [
                        'attr' => [
                            'placeholder' => 'Le prix du produit',
                            'class' => 'form-control'
                        ]
                    ])
                    ->add('description', TextareaType::class, [
                        'attr' => [
                            'placeholder' => 'La description du menu',
                            'class' => 'form-control'
                        ]
                    ])
                    ->getForm();

        return $this->render('gestionnaire/menu.html.twig', [
            'controller_name' => 'GestionnaireController',
            'produits' => $produits,
            'menus' => $menus,
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/product', name: 'dashboard_product')]
    #[Route('/dashboard/product/{id}', name: 'product_edit')]
    public function catalogueProduits(Produit $produit = null,
                                      Request $request,
                                      ProduitRepository $repo,
                                      EntityManagerInterface $manager
                                      ): Response
    {
        $this->security();
        $produits = $repo->findAll();
        if(!$produit){
            $produit = new Produit;
        }
        
        $form = $this->createFormBuilder($produit)
                    ->add('libelle', TextType::class, [
                        'attr' => [
                            'placeholder' => 'Le nom du produit',
                            'class' => 'form-control'
                        ]
                    ])
                    ->add('prix', NumberType::class, [
                        'attr' => [
                            'placeholder' => 'Le prix du produit',
                            'class' => 'form-control'
                        ]
                    ])
                    ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $composants = explode(',', $request->get('composants'));
            $produit->setComposants($composants);
            $produit->setAddedAt(new DateTimeImmutable());
            $manager->persist($produit);
            $manager->flush();
            return $this->redirectToRoute('dashboard_product');
        }            
        return $this->render('gestionnaire/product.html.twig', [
            'controller_name' => 'GestionnaireController',
            'produits' => $produits,
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/delivery', name: 'dashboard_delivery')]
    public function catalogueDeliveries(): Response
    {
        $this->security();
        return $this->render('gestionnaire/delivery.html.twig', [
            'controller_name' => 'GestionnaireController',
        ]);
    }

    // ******************FIN FONCTIONS DE RENDUES******************
}
