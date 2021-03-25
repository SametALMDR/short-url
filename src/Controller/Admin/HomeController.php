<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Entity\Page;
use App\Entity\Url;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/admin/home', name: 'admin_home')]
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(User::class);
        $pageRepo = $em->getRepository(Page::class);
        $urlRepo = $em->getRepository(Url::class);
        $contactRepo = $em->getRepository(Contact::class);

        $usercount      = $userRepo->getTotalCount();
        $pagecount      = $pageRepo->getTotalCount();
        $urlcount       = $urlRepo->getTotalCount();
        $contactcount   = $contactRepo->getTotalCount();

        $lastUsers = $userRepo->findBy([],['created_at' => 'DESC'],5);
        $lastLinks = $urlRepo->findBy([],['created_at' => 'DESC'],5);

        return $this->render('admin/home/index.html.twig', [
            'user_count' => $usercount,
            'page_count' => $pagecount,
            'url_count' => $urlcount,
            'contact_count' => $contactcount,
            'last_users' => $lastUsers,
            'last_urls' => $lastLinks
        ]);
    }
}
