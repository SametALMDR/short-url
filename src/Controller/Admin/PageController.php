<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Form\PageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/admin/pages', name: 'admin_pages')]
    public function index(): Response
    {
       $em = $this->getDoctrine()->getManager();
       $pageRepo = $em->getRepository(Page::class);

       $pages = $pageRepo->findAll();

        return $this->render('admin/page/index.html.twig', [
            'controller_name' => 'PageController',
            'pages' => $pages
        ]);
    }

    #[Route('/admin/page/create',name:'admin_create_page')]
    public function createPage(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $page = new Page();

        $form = $this->createForm(PageType::class,$page)
            ->add('save',SubmitType::class,['label' => 'Add a new page']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $page->setCreatedAt(new \DateTime());
            $em->persist($page);
            $em->flush();

            $this->addFlash('success','New page created!');
            return $this->redirectToRoute('admin_pages');
        }

        return $this->render('admin/page/create.html.twig',[
            'new_page_form' => $form->createView()
        ]);
    }

    #[Route('/admin/page/del/{id}',name:'admin_del_page')]
    public function delPage(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $pageRepo = $em->getRepository(Page::class);
        $page = $pageRepo->find($id);

        if(!$page){
            $this->addFlash('error','There is no page!');
            return $this->redirectToRoute('admin_pages');
        }

        $em->remove($page);
        $em->flush();

        $this->addFlash('success','Page successfully deleted.');
        return $this->redirectToRoute('admin_pages');
    }

    #[Route('/admin/page/edit/{id}',name:'admin_edit_page')]
    public function editPage(Request $request,int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $pageRepo = $em->getRepository(Page::class);
        $page = $pageRepo->find($id);

        if(!$page){
            $this->addFlash('error','There is no page');
            return $this->redirectToRoute('admin_pages');
        }

        $form = $this->createForm(PageType::class,$page)
            ->add('save',SubmitType::class,['label' => 'Update Page']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $page->setUpdatedAt(new \DateTime());

            $em->flush();
            $this->addFlash('success','Page successfully edited.');
        }
        
        return $this->render('admin/page/edit.html.twig',[
            'page_form' => $form->createView()
        ]);
    }
}
