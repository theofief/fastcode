<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findBy([], ['id' => 'DESC']);
        $products = $em->getRepository(Product::class)->findBy([], ['id' => 'DESC']);

        return $this->render('home/admin.html.twig', [
            'users' => $users,
            'products' => $products
        ]);
    }

    // ============================
    // USER MANAGEMENT
    // ============================

    #[Route('/update/{id}', name: 'admin_user_update', methods: ['POST'])]
    public function updateUser(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if ($email = $request->request->get('email')) {
            $user->setEmail(trim($email));
        }

        if ($firstName = $request->request->get('first_name')) {
            $user->setFirstName(trim($firstName));
        }

        if ($lastName = $request->request->get('last_name')) {
            $user->setLastName(trim($lastName));
        }

        if ($role = $request->request->get('role')) {
            $user->setRoles([$role]);
        }

        if ($password = $request->request->get('password')) {
            $user->setPassword(
                $hasher->hashPassword($user, $password)
            );
        }

        $em->flush();

        $this->addFlash('success', 'Utilisateur mis à jour ✅');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé 🗑️');
        return $this->redirectToRoute('admin_dashboard');
    }

    // ============================
    // PRODUCT MANAGEMENT
    // ============================

    #[Route('/product/create', name: 'admin_product_create', methods: ['POST'])]
    public function createProduct(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $name = trim($request->request->get('product_name'));
        $code = trim($request->request->get('code'));
        $category = trim($request->request->get('category'));
        $comment = trim($request->request->get('comment'));

        if (!$name || !$code || !$category) {
            $this->addFlash('error', 'Champs obligatoires manquants');
            return $this->redirectToRoute('admin_dashboard');
        }

        // Code produit unique
        if ($em->getRepository(Product::class)->findOneBy(['code' => $code])) {
            $this->addFlash('error', 'Ce code produit existe déjà ❌');
            return $this->redirectToRoute('admin_dashboard');
        }

        $product = new Product();
        $product->setProductName($name)
                ->setCode($code)
                ->setCategory($category)
                ->setComment($comment);

        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $safeName = $slugger->slug($name);
            $newFilename = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('product_images_directory'),
                    $newFilename
                );
                $product->setImage($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur upload image');
            }
        }

        $em->persist($product);
        $em->flush();

        $this->addFlash('success', 'Produit ajouté avec succès 🚀');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/product/update/{id}', name: 'admin_product_update', methods: ['POST'])]
    public function updateProduct(
        Request $request,
        Product $product,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $name = trim($request->request->get('product_name'));
        $code = trim($request->request->get('code'));
        $category = trim($request->request->get('category'));
        $comment = trim($request->request->get('comment'));

        if ($name) $product->setProductName($name);

        if ($code) {
            $existing = $em->getRepository(Product::class)->findOneBy(['code' => $code]);
            if ($existing && $existing->getId() !== $product->getId()) {
                $this->addFlash('error', 'Ce code produit existe déjà ❌');
                return $this->redirectToRoute('admin_dashboard');
            }
            $product->setCode($code);
        }

        if ($category) $product->setCategory($category);
        if ($comment) $product->setComment($comment);

        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $safeName = $slugger->slug($name ?: $product->getProductName());
            $newFilename = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('product_images_directory'),
                    $newFilename
                );

                if ($product->getImage()) {
                    $oldImage = $this->getParameter('product_images_directory') . '/' . $product->getImage();
                    if (file_exists($oldImage)) unlink($oldImage);
                }

                $product->setImage($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur upload image');
            }
        }

        $em->flush();
        $this->addFlash('success', 'Produit mis à jour ✅');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/product/delete/{id}', name: 'admin_product_delete', methods: ['POST'])]
    public function deleteProduct(Product $product, EntityManagerInterface $em): Response
    {
        if ($product->getImage()) {
            $imagePath = $this->getParameter('product_images_directory') . '/' . $product->getImage();
            if (file_exists($imagePath)) unlink($imagePath);
        }

        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé 🗑️');
        return $this->redirectToRoute('admin_dashboard');
    }
}