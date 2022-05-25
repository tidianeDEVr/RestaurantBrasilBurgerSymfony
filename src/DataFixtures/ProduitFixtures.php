<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Produit;
use App\Entity\Menu;
use ArrayObject;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProduitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $libelles=["burger","boisson", "frite"];
        $categories=[
            ["pain", "viande", "oignon", "salade", "cheddar"], 	
            ["eau","caramel","acide", "sucre"],
            ["pomme de terre", "sel"]
        ];
        $mns = ["menu_burger", "menu_complet", "menu_boisson"];
        $burgers = new ArrayObject();
        $boissons = new ArrayObject();
        $frites = new ArrayObject();

        foreach ($libelles as $libelle) {
            $cat=new Category;
            $cat->setLibelle($libelle)
                  ->setDescription("Description ".$libelle);
            $manager->persist($cat);
        
        
            for ($i=1; $i < 6; $i++) { 
                $produit=new Produit;
                $produit->setLibelle(ucfirst($cat->getLibelle()).$i);
                if($cat->getLibelle()=="burger"){
                    $produit->setPrix(1500);
                    $produit->setComposants($categories[0]); 
                    $burgers->append($produit);  
                }elseif($cat->getLibelle()=="boisson"){
                    $produit->setPrix(500);
                    $produit->setComposants($categories[1]);
                    $boissons->append($produit);   
                }else{
                    $produit->setPrix(800);
                    $produit->setComposants($categories[2]);   
                    $frites->append($produit);
                }
                $produit->setCategory($cat);
                $manager->persist($produit);
            }
        }

        foreach ($mns as $m) {
            for ($i=1; $i < 6; $i++) { 
                $menu=new Menu;
                $menu->setLibelle(str_replace('_', " ", $m).' '.$i);
                $menu->setDescription($faker->sentence(10));
                if($m=="menu_burger"){
                    $menu->addProduit($burgers[rand(0,4)]); 
                }
                elseif($m=="menu_complet"){
                    $menu->addProduit($burgers[rand(0,4)]);
                    $menu->addProduit($frites[rand(0,4)]);
                    $menu->addProduit($boissons[rand(0,4)]);
                }
                else{
                    $menu->addProduit($boissons[rand(0,4)]);
                }
                $menu->setImage($m.$i.".png");
                $manager->persist($menu);
            }
        }

        $manager->flush();
    }
}
