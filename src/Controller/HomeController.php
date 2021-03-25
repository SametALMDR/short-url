<?php

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\Slider;
use App\Entity\ThemeSections;
use App\Service\TwigHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    #[Route('/home')]
    public function index(TwigHelper $helper): Response
    {
        $em = $this->getDoctrine()->getManager();
        $sliderRepo = $em->getRepository(Slider::class);
        $sliders = $sliderRepo->findAll();

        $featureRepo = $em->getRepository(Feature::class);
        $features = $featureRepo->findAll();

        $helper->getVars();

        return $this->render('home/index.html.twig', [
            'sliders' => $sliders,
            'features' => $features
        ]);
    }
}
