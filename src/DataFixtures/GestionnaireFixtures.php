<?php

namespace App\DataFixtures;

use App\Entity\Gestionnaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GestionnaireFixtures extends Fixture
{
    private $encoder;
    public function __construct(UserPasswordHasherInterface $encoder){
        $this->encoder=$encoder;
    }

    public function load(ObjectManager $manager): void
    {
        
        $faker = \Faker\Factory::create('fr_FR');

        for ($i=1; $i < 11; $i++) { 
            $data=new Gestionnaire;
            $data->setNomComplet($faker->name());
            $data->setNci($faker->unixTime()) 
            ->setLogin(strtolower("gestionnaire").$i."@gmail.com")
            ->setTelephone($faker->phoneNumber())
            ->setAvatar("avatar".rand(1, 5).".png");
            $plainPassword="passer@123";
           $passwordEncode= $this->encoder->hashPassword($data,$plainPassword);
           $data->setPassword($passwordEncode);
           $this->addReference("Gestionnaire".$i, $data);
           $manager->persist($data);  
        }

        $manager->flush();
    }
}
