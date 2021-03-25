<?php

namespace App\Controller\Admin;

use App\Entity\Slider;
use App\Form\SliderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SliderController extends AbstractController
{
    #[Route('/admin/sliders', name: 'admin_sliders')]
    public function index(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $slider = new Slider();

        $form = $this->createForm(SliderType::class,$slider)
            ->add('save', SubmitType::class, ['label' => 'Create New Slider']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $slider->setCreatedAt(new \DateTime());

            $em->persist($slider);
            $em->flush();

            $this->addFlash('success','New slider created!');
            $form = $this->createForm(SliderType::class,new Slider())
                ->add('save', SubmitType::class, ['label' => 'Create New Slider']);
        }

        $sliderRepo = $em->getRepository(Slider::class);
        $sliders    = $sliderRepo->findAll();

        return $this->render('admin/slider/index.html.twig', [
            'sliders' => $sliders,
            'new_slider_form' => $form->createView()
        ]);
    }


    #[Route('/admin/slider/del/{id}', name: 'admin_del_slider')]
    public function delSlider(Request $request,int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $sliderRepo = $em->getRepository(Slider::class);
        $slider = $sliderRepo->find($id);

        if(!$slider){
            $this->addFlash('error','There is no slider!');
            return $this->redirectToRoute('admin_sliders');
        }

        $em->remove($slider);
        $em->flush();

        $this->addFlash('success','Slider successfully deleted.');
        return $this->redirectToRoute('admin_sliders');
    }

    #[Route('/admin/slider/edit/{id}', name: 'admin_edit_slider')]
    public function getEditSlider(Request $request,int $id): Response
    {

        $em = $this->getDoctrine()->getManager();
        $sliderRepo = $em->getRepository(Slider::class);
        $slider = $sliderRepo->find($id);

        if(!$slider){
            $this->addFlash('error','There is no slider');
            return $this->redirectToRoute('admin_sliders');
        }

        $form = $this->createForm(SliderType::class,$slider)
            ->add('save',SubmitType::class,['label' => 'Update Slider']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $slider->setUpdatedAt(new \DateTime());

            $em->flush();
            $this->addFlash('success','Slider successfully edited.');
        }

        return $this->render('admin/slider/edit.html.twig',[
            'slider_form' => $form->createView()
        ]);
    }

}
