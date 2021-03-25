<?php

namespace App\Controller\Admin;

use App\Entity\ThemeSections;
use App\Form\ThemeSectionsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThemeSectionsController extends AbstractController
{
    #[Route('/admin/theme/sections', name: 'admin_theme_sections')]
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $themeRepo = $em->getRepository(ThemeSections::class);

        $sections = $themeRepo->findAll();

        return $this->render('admin/theme_sections/index.html.twig', [
            'sections' => $sections
        ]);
    }

    #[Route('/admin/theme/section/create', name: 'admin_create_section')]
    public function createPage(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $section = new ThemeSections();

        $form = $this->createForm(ThemeSectionsType::class,$section)
            ->add('submit',SubmitType::class,['label'=>'Create Section']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($section);
            $em->flush();
            $this->addFlash('success','Section successfully created.');
            return $this->redirectToRoute('admin_theme_sections');
        }

        return $this->render('admin/theme_sections/create.html.twig',[
            'theme_form' => $form->createView()
        ]);
    }


    #[Route('/admin/theme/section/edit/{id}', name: 'admin_edit_section')]
    public function editPage($id,Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $themeRepo = $em->getRepository(ThemeSections::class);
        $section = $themeRepo->find($id);

        if(!$section){
            $this->addFlash('error','There is no section!');
            return $this->redirectToRoute('admin_theme_sections');
        }

        $form = $this->createForm(ThemeSectionsType::class,$section)
            ->add('submit',SubmitType::class,['label'=>'Update Section']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($section);
            $em->flush();
            $this->addFlash('success','Section successfully updated.');
        }

        return $this->render('admin/theme_sections/edit.html.twig',[
            'theme_form' => $form->createView()
        ]);
    }

    #[Route('/admin/theme/section/del/{id}', name: 'admin_del_section')]
    public function delPage($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $themeRepo = $em->getRepository(ThemeSections::class);
        $section = $themeRepo->find($id);

        if(!$section){
            $this->addFlash('error','There is no section!');
            return $this->redirectToRoute('admin_theme_sections');
        }

        $em->remove($section);
        $em->flush();

        $this->addFlash('success','Section successfully deleted.');
        return $this->redirectToRoute('admin_theme_sections');
    }


}
