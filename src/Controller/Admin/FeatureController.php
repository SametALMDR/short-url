<?php

namespace App\Controller\Admin;

use App\Entity\Feature;
use App\Form\FeatureType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeatureController extends AbstractController
{
    #[Route('/admin/features', name: 'admin_features')]
    public function index(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $feature = new Feature();

        $form = $this->createForm(FeatureType::class,$feature)
            ->add('save',SubmitType::class,['label' => 'Add New Feature']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $feature->setCreatedAt(new \DateTime());

            $em->persist($feature);
            $em->flush();

            $this->addFlash('success','New feature created!');
            return $this->redirectToRoute('admin_features');
        }

        $featureRepo = $em->getRepository(Feature::class);
        $features = $featureRepo->findAll();

        return $this->render('admin/feature/index.html.twig', [
            'features' => $features,
            'new_feature_form' => $form->createView()
        ]);
    }

    #[Route('/admin/feature/del/{id}',name:'admin_del_feature')]
    public function delFeature(int $id) : Response {

        $em     = $this->getDoctrine()->getManager();
        $feature = $em->getRepository(Feature::class)->find($id);

        if(!$feature){
            $this->addFlash('error','There is no feature.');
            return $this->redirectToRoute('admin_features');
        }

        $em->remove($feature);
        $em->flush();

        $this->addFlash('success','Feature successfully deleted.');
        return $this->redirectToRoute('admin_features');
    }

    #[Route('/admin/feature/edit/{id}',name:'admin_edit_feature')]
    public function editFeature(Request $request,int $id){

        $em = $this->getDoctrine()->getManager();
        $featureRepo = $em->getRepository(Feature::class);
        $feature = $featureRepo->find($id);

        if(!$feature){
            $this->addFlash('error','There is no feature');
            return $this->redirectToRoute('admin_features');
        }

        $form = $this->createForm(FeatureType::class,$feature)
            ->add('save',SubmitType::class,['label' => 'Update Feature']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $feature->setUpdatedAt(new \DateTime());

            $em->flush();
            $this->addFlash('success','Feature successfully updated.');
        }

        return $this->render('admin/feature/edit.html.twig',[
            'feature_form' => $form->createView()
        ]);
    }

}
