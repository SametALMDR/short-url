<?php

namespace App\Controller\Admin;

use App\Entity\Url;
use App\Entity\UrlStats;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(User::class);

        $users = $userRepo->findAll();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/edit/{id}', name:'admin_edit_user')]
    public function editUser(Request $request,$id,UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->find($id);

        $form = $this->createForm(UserType::class,$user)
            ->remove('password');

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user->setUpdatedAt(new \DateTime());

            $em->persist($user);
            $em->flush();

            $this->addFlash('success','User successfully updated.');
        }

        return $this->render('admin/user/edit.html.twig',[
            'user_form' => $form->createView(),
            'uid' => $id
        ]);
    }

    #[Route('/admin/user/change-pass/{id}', name:'admin_user_change_password', methods: ['POST'])]
    public function changeUserPass($id,Request $request,UserPasswordEncoderInterface $encoder)
    {
        if(!empty($request->get('password'))){
            $encoded = $encoder->encodePassword(new User(),$request->get('password'));

            $em = $this->getDoctrine()->getManager();
            $userRepo = $em->getRepository(User::class);
            $user = $userRepo->find($id);
            $user->setPassword($encoded);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success','User password changed successfully.');
        }
        return $this->redirectToRoute('admin_edit_user',['id' => $id]);
    }

    #[Route('/admin/user/del/{id}', name:'admin_del_user')]
    public function delUser($id)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(User::class);
        $urlRepo = $em->getRepository(Url::class);
        $urlStatsRepo = $em->getRepository(UrlStats::class);

        $user = $userRepo->find($id);
        $urls = $urlRepo->findBy(['user_id' => $id]);

        foreach ($urls as $url) {
            $stats = $urlStatsRepo->findBy(['url_id' => $url->getId()]);
            foreach ($stats as $stat) {
                $em->remove($stat);
            }
            $em->remove($url);
        }

        $em->remove($user);
        $em->flush();
        $this->addFlash('success','User successfully deleted.');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/user/create', name:'admin_create_user')]
    public function createUser(Request $request,UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class,$user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $encoded = $encoder->encodePassword($user,$form->getData()->getPassword());
            $user->setPassword($encoded);
            $user->setCreatedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success','New user created!');
            return $this->redirectToRoute('admin_users');
        }
        return $this->render('admin/user/create.html.twig',[
            'user_form' => $form->createView()
        ]);
    }

    #[Route('/admin/user/{id}/links', name:'admin_view_user_short_links')]
    public function viewShortenedLinks($id)
    {
        $em = $this->getDoctrine()->getManager();
        $urlRepo = $em->getRepository(Url::class);

        $urls = $urlRepo->findByUid($id);

        return $this->render('admin/user/links.html.twig',[
            'urls' => $urls
        ]);
    }
}
