<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\TwigHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
class UserSecurityController extends AbstractController
{
    /**
     * @Route("/login", priority = 1, name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils,TwigHelper $helper): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('redirect_to_matched_panel');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $helper->getVars();
        return $this->render('user/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", priority = 1, name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/signup", priority = 1, name="app_signup",methods={"GET"})
     */
    public function GetSignUp(SessionInterface $session,TwigHelper $helper): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('redirect_to_matched_panel');
        }
        $helper->getVars();
        return $this->render('user/signup.html.twig');
    }

    /**
     * @Route("/signup", priority = 1, name="app_post_signup",methods={"POST"})
     */
    public function PostSignUp(
        UserPasswordEncoderInterface $encoder ,
        Request $request,
        ValidatorInterface $validator,
    ): Response {

        $constraints = new Assert\Collection([
            'name'      => [ new Assert\Length(['min'=> 2]), new Assert\Length(['max'=> 32]) ],
            'surname'   => [ new Assert\Length(['min'=> 2]), new Assert\Length(['max'=> 64]) ],
            'username'  => [ new Assert\Length(['min'=> 2]), new Assert\Length(['max'=> 64]) ],
            'email'     => [ new Assert\Email(), new Assert\Length(['max'=> 180])],
            'password'  => [ new Assert\Length(['min'=> 6]) ]
        ]);

        $violations = $validator->validate([
            'name'      => $request->get('name'),
            'surname'   => $request->get('surname'),
            'username'  => $request->get('username'),
            'email'     => $request->get('email'),
            'password'  => $request->get('password')
        ], $constraints);

        $accessor       = PropertyAccess::createPropertyAccessor();
        $errorMessages  = [];

        foreach($violations as $v){
            $accessor->setValue($errorMessages, $v->getPropertyPath(), $v->getMessage() );
        }

        if (count($errorMessages) === 0){

            $em         = $this->getDoctrine()->getManager();
            $UserRepo   = $em->getRepository(User::class);

            $isAvailableUname = $UserRepo->findOneBy([
                'username' => $request->get('username')
            ]);

            $isAvailableEmail = $UserRepo->findOneBy([
                'email' => $request->get('email')
            ]);

            if($isAvailableUname){
                $errorMessages[] = "This username is already exist!";
            }

            if($isAvailableEmail){
                $errorMessages[] = "This email address is already exist!";
            }

            if(!$isAvailableUname && !$isAvailableEmail){

                $user = new User();

                $encoded = $encoder->encodePassword($user, $request->get('password'));

                $user->setName($request->get('name'))
                    ->setSurname($request->get('surname'))
                    ->setUsername($request->get('username'))
                    ->setEmail($request->get('email'))
                    ->setPassword($encoded)
                    ->setRoles(['ROLE_USER'])
                    ->setIsActive(1)
                    ->setCreatedAt(new \DateTime());

                $em->persist($user);
                $em->flush();

                // Manually authenticate user in controller
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));

                return $this->redirectToRoute('redirect_to_matched_panel');
            }
        }

        if(count($errorMessages) > 0){
            foreach ($errorMessages as $errorMessage) {
                $this->addFlash('error',$errorMessage);
            }
        }

        return $this->redirectToRoute('app_signup');
    }

    /**
     * @Route("/panelredirect", priority = 1 , name="redirect_to_matched_panel")
     */
    public function redirectToPanel(){
        /* guaranteed that we have a user in session */
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();
            if(in_array('ROLE_ADMIN',$roles)){
                return $this->redirectToRoute('admin_home');
            }else{
                return $this->redirectToRoute('user_home');
            }
        }
    }

}
