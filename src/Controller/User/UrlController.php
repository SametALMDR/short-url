<?php

namespace App\Controller\User;

use App\Entity\Url;
use App\Entity\UrlStats;
use App\Service\TwigHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{
    #[Route('/user/url/{urlid}', name: 'user_url')]
    public function index(int $urlid,TwigHelper $helper): Response
    {
        $em = $this->getDoctrine()->getManager();
        $urlRepo = $em->getRepository(Url::class);
        $urlStatsRepo = $em->getRepository(UrlStats::class);

        $url = $urlRepo->findOneBy(['id' => $urlid]);
        $stats = $urlStatsRepo->findBy(['url_id' => $urlid]);

        $topBrowsers = $urlStatsRepo->topColsByUrlId('browser',$urlid);
        $topDevices = $urlStatsRepo->topColsByUrlId('device',$urlid);
        $topOs = $urlStatsRepo->topColsByUrlId('os',$urlid,5);
        $topCountries = $urlStatsRepo->topColsByUrlId('country',$urlid);
        $topResolutions = $urlStatsRepo->topColsByUrlId('resolution',$urlid);
        $topCities = $urlStatsRepo->topColsByUrlId('city',$urlid);
        $topDeviceBrands = $urlStatsRepo->topColsByUrlId('device_brand',$urlid);


        $helper->getVars();
        return $this->render('user/url/index.html.twig', [
            'stats' => $stats,
            'url' => $url,
            'top_browsers' => $topBrowsers,
            'top_devices' => $topDevices,
            'top_os' => $topOs,
            'top_countries' => $topCountries,
            'top_resolutions' => $topResolutions,
            'top_cities' => $topCities,
            'top_device_brands' => $topDeviceBrands
        ]);
    }
}
