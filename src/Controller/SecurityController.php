<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ResetPasswordRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SecurityController extends AbstractController
{
    #[Route('/signup', name: 'signup')]
    public function signup(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer): Response
    {
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $picture = $userForm->get('pictureFile')->getData();
            $folder = $this->getParameter('profile.folder');
            $ext = $picture->guessExtension() ?? 'bin';
            $fileName = bin2hex(random_bytes(10)) . '.' . $ext;
            $picture->move($folder, $fileName);
            $user->setPicture($this->getParameter('profile.folder.public_path') . '/' . $fileName);
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Bienvenue sur Wonder !');
            $email = new TemplatedEmail();
            $email->to($user->getEmail())
                    ->subject('Bienvenue sur Wonder')
                    ->htmlTemplate('@email_templates/welcome.html.twig')
                    ->context([
                'username' => $user->getFirstname()
            ]);
            $mailer->send($email);
            return $this->redirectToRoute('login');
        }

        return $this->render('security/signup.html.twig', [
            'controller_name' => 'SecurityController',
            'form' => $userForm->createView()
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'controller_name' => 'SecurityController',
            'error' => $error,
            'username' => $username
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout()
    {
    }

    #[Route('reset-password/{token}', name: 'reset-password')]
    public function resetPassword(RateLimiterFactory $passwordRecoveryLimiter, UserPasswordHasherInterface $userPasswordHasher, Request $request, EntityManagerInterface $em, string $token, ResetPasswordRepository $resetPasswordRepository)
    {
        $limiter = $passwordRecoveryLimiter->create($request->getClientIp());
        if(false === $limiter->consume(1)->isAccepted()) {
           $this->addFlash('error', 'Vous devez attendre 1 heure pour refaire une tentative.');
           return $this->redirectToRoute('login');
        }

        $resetPassword = $resetPasswordRepository->findOneBy(['token' => sha1($token)]);
        if(!$resetPassword || $resetPassword->getExpiredAt() < new \DateTime('now')) {
            if($resetPassword) {
                $em->remove($resetPassword);
                $em->flush();
            }
            $this->addFlash('error', 'Votre demande est expiré veuillez refaire une demande.');
            return $this->redirectToRoute('login');
        }
        $passwordForm = $this->createFormBuilder()
                            ->add('password', PasswordType::class, [
                                'label' => 'Nouveau mot de passe',
                                'constraints' => [
                                    new Length([
                                        'min' => 6,
                                        'minMessage' => 'Le mot de passe doit faire au moins 6 caractères.'
                                    ]),
                                    new NotBlank([
                                        'message' => 'Veuillez renseigner un mot de passe.'
                                    ])
                                ]
                            ])
                            ->getForm();
        $passwordForm->handleRequest($request);
        if($passwordForm->isSubmitted() && $passwordForm->isSubmitted()) {
            $password = $passwordForm->get('password')->getData();
            $user = $resetPassword->getUser();
            $hash = $userPasswordHasher->hashPassword($user, $password);
            $user->setPassword($hash);
            $em->remove($resetPassword);
            $em->flush();
            $this->addFlash('success', 'Votre mot de passe a été modifié');
            return $this->redirectToRoute('login');
        }

        return $this->render('security/reset_password_form.html.twig', [
            'form' => $passwordForm->createView()
        ]);
    }

    #[Route('/reset-password-request', name: 'reset-password-request')]
    public function resetPasswordRequest(RateLimiterFactory $passwordRecoveryLimiter, MailerInterface $mailer, Request $request, UserRepository $userRepository, ResetPasswordRepository $resetPasswordRepository, EntityManagerInterface $em)
    {
        $limiter = $passwordRecoveryLimiter->create($request->getClientIp());
         if(false === $limiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'Vous devez attendre 1 heure pour refaire une tentative.');
            return $this->redirectToRoute('login');
         }

        $emailForm = $this->createFormBuilder()->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez renseigner votre email'
                ])
            ]
        ])->getForm();

        $emailForm->handleRequest($request);
        if($emailForm->isSubmitted() && $emailForm->isValid()) {
            $emailValue = $emailForm->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $emailValue]);
            if($user) {
                $oldResetPassword = $resetPasswordRepository->findOneBy(['user' => $user]);
                if($oldResetPassword) {
                    $em->remove($oldResetPassword);
                    $em->flush();
                }
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user);
                $resetPassword->setExpiredAt(new \DateTimeImmutable('+2 hours'));
                $token = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(30))), 0, 20);
                $resetPassword->setToken(sha1($token));
                $em->persist($resetPassword);
                $em->flush();
                $email = new TemplatedEmail();
                $email->to($emailValue)
                      ->subject('Demande de réinitialisation de mot de passe')
                      ->htmlTemplate('@email_templates/reset_password_request.html.twig')
                      ->context([
                        'token' => $token
                      ]);
                $mailer->send($email);
            }
            $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'form' => $emailForm->createView()
        ]);
    }
}

