<?php

namespace App\Controller\User;

use App\Entity\Url;
use App\Service\TwigHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/user/home', name: 'user_home')]
    public function index(Request $request,TwigHelper $helper): Response
    {
        $em      = $this->getDoctrine()->getManager();
        $urlRepo = $em->getRepository(Url::class);

        $urls      = $urlRepo->findBy(['user_id' => $this->getUser()->getId()]);
        $web_root  = $request->server->get('HTTPS') ? 'https://' : 'http://';
        $web_root .= $request->server->get('HTTP_HOST');

        $helper->getVars();
        return $this->render('user/home/index.html.twig', [
            'urls'      => $urls,
            'web_root'  => $web_root
        ]);
    }
}
