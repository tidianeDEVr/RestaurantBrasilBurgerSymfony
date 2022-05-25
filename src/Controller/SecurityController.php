<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Client;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login",methods={"GET","POST"})
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();
            if($roles[0]=='ROLE_GESTIONNAIRE'){
                return $this->redirectToRoute('dashboard');
            }else{
                return $this->redirectToRoute('home');
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/register", name="app_register",methods={"GET","POST"})
     */
    public function register(AuthenticationUtils $authenticationUtils,
                        EntityManagerInterface $em,
                        UserPasswordHasherInterface $encoder): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }
        if (count($_POST)>3) {
            if($_POST['nom']!='' && $_POST['prenom']!=''
            && $_POST['email']!='' && $_POST['telephone']!=''
            && $_POST['password'] == $_POST['confirm_password']
            ){
                $data=new Client;
                $data->setNomComplet($_POST['prenom'].' '.$_POST['nom']);
                $data->setMatricule(uniqid('client'))
                    ->setDomicile($_POST['address'])    
                    ->setTelephone($_POST['telephone'])
                    ->setAvatar("avatar".rand(1, 5).".png")
                    ->setLogin($_POST['email']);
                $plainPassword = $_POST['password'];
                $passwordEncode= $encoder->hashPassword($data,$plainPassword);
                $data->setPassword($passwordEncode);
                $em->persist($data);  
                $em->flush();

                // Apres inscription
                $_POST = array();
                unset($data);
                
                return $this->redirectToRoute('app_login');

            }else{
                echo('
                <script>
                    alert("Revoyez votre saisie ! ");
                </script>
                ');
                $_POST = array();
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/register.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/my-account", name="my_account",methods={"GET","POST"})
     */
    public function myaccount(AuthenticationUtils $authenticationUtils): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/myaccount.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
