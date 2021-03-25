<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/admin/contacts', name: 'admin_contact')]
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $contactRepo = $em->getRepository(Contact::class);

        $contacts = $contactRepo->findAll();

        return $this->render('admin/contact/index.html.twig', [
            'contacts' => $contacts
        ]);
    }

    #[Route('/admin/contact/edit/{id}',name:'admin_edit_contact')]
    public function editContact(Request $request,int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $contactRepo = $em->getRepository(Contact::class);
        $contact = $contactRepo->find($id);

        if(!$contact){
            $this->addFlash('error','There is no contact message');
            return $this->redirectToRoute('admin_contact');
        }

        $first_is_read = $contact->getIsRead();
        $first_is_answered = $contact->getIsAnswered();

        $form = $this->createForm(ContactType::class,$contact)
            ->add('name',TextType::class,['disabled' => true])
            ->add('surname',TextType::class,['disabled' => true])
            ->add('email',EmailType::class,['disabled' => true])
            ->add('message',TextareaType::class,['disabled' => true])
            ->add('save',SubmitType::class,['label' => 'Update Contact']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $new_data = $form->getData();
            if(!$first_is_read && $new_data->getIsRead()){
                $contact->setReadedAt(new \DateTime());
            }
            if(!$first_is_answered && $new_data->getIsAnswered()){
                $contact->setAnsweredAt(new \DateTime());
            }
            $em->flush();
            $this->addFlash('success','Contact information updated.');
        }

        return $this->render('admin/contact/edit.html.twig',[
           'contact_form' => $form->createView()
        ]);
    }

    #[Route('/admin/contact/del/{id}',name:'admin_del_contact')]
    public function delContact(Request $request,int $id)
    {
        $em     = $this->getDoctrine()->getManager();
        $contact = $em->getRepository(Contact::class)->find($id);

        if(!$contact){
            $this->addFlash('error','There is no contact.');
            return $this->redirectToRoute('admin_contact');
        }

        $em->remove($contact);
        $em->flush();

        $this->addFlash('success','Contact message successfully deleted.');
        return $this->redirectToRoute('admin_contact');
    }

    #[Route('/admin/contact/filter/{type}/{val}',name:'admin_filter_contact')]
    public function filterContacts($type,$val) : Response
    {
        $em = $this->getDoctrine()->getManager();
        $contactRepo = $em->getRepository(Contact::class);

        if($type === 'read'){
            $contacts = $contactRepo->findByRead($val);
        }else{
            $contacts = $contactRepo->findByAnswered($val);
        }

        return $this->render('admin/contact/index.html.twig', [
            'contacts' => $contacts
        ]);
    }
}
