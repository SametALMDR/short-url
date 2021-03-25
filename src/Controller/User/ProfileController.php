<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Service\TwigHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends AbstractController
{
    #[Route('/user/profile', name: 'user_profile', methods: ['GET'])]
    public function index(TwigHelper $helper): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser()->getId());

        $helper->getVars();
        return $this->render('user/profile/index.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user/profile', name: 'user_profile_post', methods: ['POST'])]
    public function update(Request $request,UserPasswordEncoderInterface $encoder): Response
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->find($this->getUser()->getId());
        $error = false;

        if($request->get('email') !== $this->getUser()->getEmail()){
            $mails  = $userRepo->findBy(['email' => $request->get('email')]);
            if($mails){
                $this->addFlash('error','E-mail address already exists');
                $error = true;
            }
        }
        echo $request->get('username');

        if($request->get('username') !== $this->getUser()->getCUsername()){
            $unames = $userRepo->findBy(['username' => $request->get('username')]);
            if($unames){
                $this->addFlash('error','Username address already exists');
                $error = true;
            }
        }

        if(!$error){
            $user->setName($request->get('name'));
            $user->setSurname($request->get('surname'));
            $user->setUsername($request->get('username'));
            $user->setEmail($request->get('email'));
            if(!empty($request->get('password'))){
                $encoded = $encoder->encodePassword(new User(),$request->get('password'));
                $user->setPassword($encoded);
            }

            $em->flush();
            $this->addFlash('success','Successfully updated!');
        }

        return $this->redirectToRoute('user_profile');
    }
}
