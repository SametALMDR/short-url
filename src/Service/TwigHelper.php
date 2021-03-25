<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\ThemeSections;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TwigHelper extends AbstractController
{
    public function getVars(){
        $em = $this->getDoctrine()->getManager();
        if(class_exists('App\Entity\ThemeSections')){
            $themeRepo = $em->getRepository(ThemeSections::class);
            $sections = $themeRepo->findAll();

            foreach ($sections as $section) {
                $this->get('twig')->addGlobal($section->getName(), $section->getContent());
            }
        }
    }

    public function getUnreadContacts(){
        $em = $this->getDoctrine()->getManager();
        if(class_exists('App\Entity\Contact')){
            $contactRepo = $em->getRepository(Contact::class);
            return $contactRepo->getUnreadedCount();
        }
        return 0;
    }
}
