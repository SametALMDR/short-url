<?php

namespace App\Controller\Admin;

use App\Entity\Url;
use App\Entity\UrlStats;
use App\Entity\User;
use App\Form\NewUrlType;
use App\Form\TryType;
use App\Form\UrlType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

class UrlController extends AbstractController
{
    #[Route('/admin/urls', name: 'admin_urls')]
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $urlRepo = $em->getRepository(Url::class);
        $urlStatsRepo = $em->getRepository(UrlStats::class);
        $userRepo = $em->getRepository(User::class);
        $urls = $urlRepo->findAll();

        $topBrowsers = $urlStatsRepo->topCols('browser');
        $topLocales = $urlStatsRepo->topCols('locale');
        $topResolutions = $urlStatsRepo->topCols('resolution');

        $topUsers = $urlRepo->topUsers();
        $topUsersInfo = [];
        foreach ($topUsers as $topUser) {
            if($topUser['user_id'] !== 0){
                $topUsersInfo[] = $userRepo->find($topUser['user_id']);
            }
        }

        $topClicked = $urlRepo->topClicked();
        $topDomains = $urlRepo->topDomains();

        $topRealDomains = [];
        foreach ($topDomains as $key => $topDomain) {
            $pure = str_replace('www.','',parse_url($topDomain['url'],PHP_URL_HOST));
            if(!array_key_exists($pure,$topRealDomains)){
                $topRealDomains[$pure] = 1;
            }else{
                $topRealDomains[$pure] += 1;
            }
        }
        arsort( $topRealDomains);

        return $this->render('admin/url/index.html.twig', [
            'urls' => $urls,
            'top_browsers' => $topBrowsers,
            'top_locales' => $topLocales,
            'top_resolutions' => $topResolutions,
            'top_users' => $topUsersInfo,
            'top_clicked' => $topClicked,
            'top_domains' => $topRealDomains
        ]);
    }


    #[Route('/admin/url/create', name: 'admin_url_create')]
    public function createUrl(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $urlRepo = $em->getRepository(Url::class);
        $url = new Url();

        $form = $this->createForm(NewUrlType::class,$url)
            ->remove('user_id')
            ->add('save',SubmitType::class,['label' => 'Create Url']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $url->setUserId(0);
            $url->setCreatedAt(new \DateTime());

            $em->persist($url);
            $em->flush();
            $this->addFlash('success','Shortened url successfully created.');
            return $this->redirectToRoute('admin_urls');
        }

        return $this->render('admin/url/create.html.twig',[
            'url_form' => $form->createView()
        ]);
    }

    #[Route('/admin/url/edit/{id}', name: 'admin_url_edit')]
    public function editUrl(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $urlRepo = $em->getRepository(Url::class);
        $url =  $urlRepo->find($id);

        $form = $this->createForm(NewUrlType::class,$url)
            ->remove('user_id')
            ->add('save',SubmitType::class,['label' => 'Update Url']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $url->setUpdatedAt(new \DateTime());

            $em->persist($url);
            $em->flush();
            $this->addFlash('success','Url information successfully updated.');
        }

        return $this->render('admin/url/edit.html.twig',[
            'url_form' => $form->createView()
        ]);
    }

    #[Route('/admin/url/del/{id}', name: 'admin_url_del')]
    public function delUrl($id)
    {
        $em = $this->getDoctrine()->getManager();
        $urlRepo = $em->getRepository(Url::class);
        $url =  $urlRepo->find($id);

        if(!$url){
            $this->addFlash('error','There is no record');
            return $this->redirectToRoute('admin_urls');
        }

        $em->remove($url);
        $em->flush();
        $this->addFlash('success','Shortened url successfully deleted.');

        return $this->redirectToRoute('admin_urls');
    }

    #[Route('/admin/url/view/stat/{id}', name: 'admin_url_stat_view')]
    public function viewUrl($id)
    {
        $em = $this->getDoctrine()->getManager();
        $urlStatsRepo = $em->getRepository(UrlStats::class);
        $stats =  $urlStatsRepo->findBy(['url_id' => $id]);

        return $this->render('admin/url/view.html.twig',[
            'stats' => $stats
        ]);
    }
}
