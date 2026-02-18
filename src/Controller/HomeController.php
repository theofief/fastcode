<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/home/search', name: 'home_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $query = trim($request->query->get('q'));

        if (!$query) {
            return new JsonResponse([]);
        }

        $repo = $em->getRepository(Product::class);

        $exact = $repo->findOneBy(['code' => $query]);
        if ($exact) {
            return new JsonResponse([$exact]);
        }

        $qb = $repo->createQueryBuilder('p');
        $qb->where('p.productName LIKE :q OR p.code LIKE :q OR p.comment LIKE :q')
           ->setParameter('q', "%$query%")
           ->setMaxResults(20);

        $results = $qb->getQuery()->getResult();

        $data = array_map(fn($p) => [
            'id' => $p->getId(),
            'name' => $p->getProductName(),
            'code' => $p->getCode(),
            'category' => $p->getCategory(),
            'comment' => $p->getComment(),
            'image' => $p->getImage(),
        ], $results);

        return new JsonResponse($data);
    }

    #[Route('/home/random-product', name: 'random_product', methods: ['GET'])]
    public function randomProduct(EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();

        $sql = "
            SELECT id, product_name, code, category, comment, image
            FROM product
            ORDER BY RAND()
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery()->fetchAssociative();

        if (!$result) {
            return new JsonResponse(null);
        }

        return new JsonResponse([
            'id' => $result['id'],
            'name' => $result['product_name'],
            'code' => $result['code'],
            'category' => $result['category'],
            'comment' => $result['comment'],
            'image' => $result['image'],
        ]);
    }
}