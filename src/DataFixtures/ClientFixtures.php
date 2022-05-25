<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class ClientFixtures extends Fixture
{
    private $encoder;
    public function __construct(UserPasswordHasherInterface $encoder){
        $this->encoder=$encoder;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = \Faker\Factory::create('fr_FR');
        
        for ($i=1; $i < 11; $i++) { 
            $data=new Client;
            $data->setNomComplet($faker->name());
            $data->setMatricule($faker->unixTime())
            ->setDomicile($faker->address())    
            ->setTelephone($faker->phoneNumber())
            ->setAvatar("avatar".rand(1, 5).".png")
            ->setLogin(strtolower("client").$i."@gmail.com");
            $plainPassword="passer@123";
           $passwordEncode= $this->encoder->hashPassword($data,$plainPassword);
           $data->setPassword($passwordEncode);
           $this->addReference("Client".$i, $data);
           $manager->persist($data);  
        }

        $manager->flush();
    }
}
