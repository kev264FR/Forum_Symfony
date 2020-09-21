<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash("error", "Email ou mot de passe invalide");
        }
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername]);
    }

    /**
     * @Route("/logout", name="app_logout")
     * @IsGranted("ROLE_USER")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/profile", name="profile")
     * @IsGranted("ROLE_USER")
     */
    public function profile(){
        return $this->render("home/profile.html.twig");
    }

    /**
     * @Route("/change/password", name="change_password")
     * @IsGranted("ROLE_USER")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder){

        $form = $this->createForm(ChangePasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            
            $this->getDoctrine()
                ->getRepository(User::class)
                ->upgradePassword(
                    $user,
                    $encoder->encodePassword(
                        $user,
                        $form->get("plainPassword")->getData()
                    )
                );


            return $this->redirectToRoute("app_logout");
                
        }

        return $this->render("security/change_password.html.twig",[
            "form"=>$form->createView()
        ]);
    }
}
