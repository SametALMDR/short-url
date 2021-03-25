<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Url;
use App\Entity\UrlStats;
use App\Service\TwigHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UrlController extends AbstractController
{

    #[Route('/url/create', name: 'url_create')]
    public function create(Request $request, ValidatorInterface $validator): Response
    {

        $url = $request->get('url');
        $shortUrl = null;

        # url validation
        $constraints = new Assert\Collection([
            'url' => [ new Assert\Url() ]
        ]);

        $violations = $validator->validate([
            'url'=>$url
        ], $constraints);

        $accessor = PropertyAccess::createPropertyAccessor();
        $errorMessages = [];

        foreach($violations as $v){
            $accessor->setValue($errorMessages, $v->getPropertyPath(), $v->getMessage() );
        }

        if (count($errorMessages)===0){
            # generate 5 digit hash
            $alpha_numeric = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $url_hash = substr( str_shuffle($alpha_numeric),0,5);

            $em = $this->getDoctrine()->getManager();
            $expireDate = new \DateTime();
            $expireDate->modify('+30 day');

            if($this->getUser()){
                $uid = $this->getUser()->getId();
            }else{
                $uid = 0;
            }

            $url_item = new Url();
            $url_item->setUrl($url)
                ->setUrlHash( $url_hash )
                ->setCreatedAt( (new \DateTime()) )
                ->setUserId($uid)
                ->setClickCount(0)
                ->setIsPublic(true)
                ->setExpiredAt($expireDate)
                ->setIsActive(true);

            $em->persist($url_item);
            $em->flush();

            $shortUrl = 'http://pa.th/'.$url_hash;
        }

        return new JsonResponse([
            'success'       => count($errorMessages) === 0 ?? false,
            'response'      => $shortUrl,
            'error'         => count($errorMessages) > 0 ?? false,
            'errorMessage'  => count($errorMessages) > 0 ? $errorMessages : null
        ],200);

    }

    #[Route('/{urlHash}', name: 'redirector',methods: ['GET'])]
    public function redirector($urlHash, Request $request,TwigHelper $helper): Response
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * Is a Page ?
         */
        $pageRepo   = $em->getRepository(Page::class);
        $page       = $pageRepo->findOneBy(['url'=> $urlHash]);

        if($page){
            if($page->getIsHidden() == 0 ){
                $helper->getVars();
                return $this->render('page/index.html.twig', [
                    'page' => $page,
                ]);
            }else {
                return $this->redirectToRoute('home');
            }
        }

        /**
         * Is a shortened link ?
         */
        $urlRepository = $em->getRepository(Url::class);
        $url_item = $urlRepository->findOneBy([
            'is_active'=>true,
            'url_hash'=> $urlHash,
        ]);

        if ($url_item){
            if($url_item->getExpiredAt()->format('Y-m-d H:i:s') > date('Y-m-d H:i:s')){
                return $this->render('home/agent.html.twig',[
                    'hash' => $urlHash,
                    'redirectTo' => $url_item->getUrl()
                ]);
            }
            return $this->redirectToRoute('home');
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/agentpost',methods:['POST'])]
    public function postUserAgent(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * Is a shortened link ?
         */
        $urlRepository = $em->getRepository(Url::class);
        $url_item = $urlRepository->findOneBy([
            'is_active'=>true,
            'url_hash'=> $request->get('hash')
        ]);

        if ($url_item){
            $url = $url_item->getUrl();
            $urlId = $url_item->getId();

            $this->saveStats($urlId, $request);
        }

        return $this->redirect('home');
    }

    public function saveStats($urlId, Request $request){

        $em = $this->getDoctrine()->getManager();

        $IP          = json_decode(file_get_contents('https://api.ipify.org?format=json'))->ip;
        $query       = @unserialize(file_get_contents('http://ip-api.com/php/'.$IP));
        $city        = 'istanbul';
        $country     = 'turkey';

        if($query && $query['status'] == 'success') {
            $city        = strtolower($query['regionName']);
            $country     = strtolower($query['country']);
        }

        $urlRepo = $em->getRepository(Url::class);
        $url = $urlRepo->findOneBy(['url_hash'=>$request->get('hash')]);
        $url->setClickCount($url->getClickCount()+1);


        $url_stats = new UrlStats();
        $url_stats->setUrlId($urlId)
            ->setBrowser(strtolower($request->get('browser')))
            ->setIpAddress($IP)
            ->setOs(strtolower($request->get('os')))
            ->setDeviceBrand(strtolower($request->get('device_brand')))
            ->setIpAddress($IP)
            ->setDevice(strtolower($request->get('device')))
            ->setResolution(strtolower($request->get('resolution')))
            ->setLocale($request->getLocale())
            ->setCity($city)
            ->setCountry($country)
            ->setCreatedAt( ( new \DateTime() ));

        $em->persist($url_stats);
        $em->flush();
    }

}
