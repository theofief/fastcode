<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('home/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/update', name: 'profile_update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifie quel champ est présent dans le POST
        if ($request->request->has('email')) {
            $user->setEmail($request->request->get('email'));
        }

        if ($request->request->has('first_name')) {
            $user->setFirstName($request->request->get('first_name'));
        }

        if ($request->request->has('last_name')) {
            $user->setLastName($request->request->get('last_name'));
        }

        if ($request->request->has('password') && $request->request->get('password') !== '') {
            $hashedPassword = $passwordHasher->hashPassword($user, $request->request->get('password'));
            $user->setPassword($hashedPassword);
        }

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Vos informations ont été mises à jour !');

        return $this->redirectToRoute('profile');
    }

    #[Route('/profile/delete', name: 'profile_delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $em->remove($user);
        $em->flush();

        // Déconnecte l'utilisateur après suppression
        return $this->redirect('/logout');
    }
}