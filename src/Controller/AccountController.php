<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    #[Route('/', name: 'account')]
    public function index(): Response
    {
        // Simple page pour afficher les forms (login + signup)
        return $this->render('home/account.html.twig');
    }

    #[Route('/signup', name: 'signup', methods: ['POST'])]
    public function signup(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $firstName = trim($request->request->get('firstname'));
        $lastName = trim($request->request->get('lastname'));
        $email = trim($request->request->get('email'));
        $plainPassword = $request->request->get('password');

        if (!$firstName || !$lastName || !$email || !$plainPassword) {
            $this->addFlash('error', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('account');
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $this->addFlash('error', 'Cet email est déjà utilisé.');
            return $this->redirectToRoute('account');
        }

        $user = new User();
        $user->setEmail($email)
            ->setRoles(['ROLE_USER'])
            ->setPassword($passwordHasher->hashPassword($user, $plainPassword))
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setCreatedAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Compte créé ! Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('account');
    }
}