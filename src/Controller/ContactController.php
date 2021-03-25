<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\TwigHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request,TwigHelper $helper): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class,$contact)
            ->remove('is_read')
            ->remove('is_answered');

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $contact->setIsRead(0);
            $contact->setIsAnswered(0);
            $contact->setCreatedAt(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();

            $form = $this->createForm(ContactType::class,new Contact())
                ->remove('is_read')
                ->remove('is_answered');

            $this->addFlash('success','Contact form successfully sended.');
        }
        $helper->getVars();
        return $this->render('contact/index.html.twig', [
            'contact_form' => $form->createView(),
        ]);
    }
}
