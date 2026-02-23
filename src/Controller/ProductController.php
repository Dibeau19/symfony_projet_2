<?php

namespace App\Controller;

use App\Dto\ProductDto;
use App\Entity\Product;
use App\Form\Product\ProductFlowType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ProductExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAllOrderedByPriceDesc(),
        ]);
    }

    #[Route('/{id}/export', name: 'app_product_export', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function export(Product $product, ProductExportService $exportService): Response
    {
        return $exportService->exportCsv([$product]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        
        $dto = $session->get('product_dto', new ProductDto());
        
        $step = $request->query->getInt('step', 1);

        $formClass = match ($step) {
            1 => \App\Form\Product\Step\BookTypeStepType::class,
            2 => \App\Form\Product\Step\BookDetailsStepType::class,
            3 => \App\Form\Product\Step\BookLogisticsStepType::class,
            4 => \App\Form\Product\Step\BookSummaryStepType::class,
            default => \App\Form\Product\Step\BookTypeStepType::class,
        };

        $form = $this->createForm($formClass, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('product_dto', $dto);

            if ($request->request->has('previous')) {
                return $this->redirectToRoute('app_product_new', ['step' => max(1, $step - 1)]);
            }

            if ($step === 4) {
                $product = new Product();
                $product->setType($dto->type);
                $product->setName($dto->name);
                $product->setDescription($dto->description);
                $product->setPrice($dto->price);
                $product->setWeight($dto->weight);
                $product->setStock($dto->stock);

                $entityManager->persist($product);
                $entityManager->flush();

                $session->remove('product_dto');

                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_product_new', ['step' => $step + 1]);
        }

        $stepLabels = [
            1 => 'Type de livre',
            2 => 'Informations',
            3 => 'Logistique',
            4 => 'Récapitulatif et Confirmation'
        ];

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'current_step' => $step,
            'total_steps' => 4,
            'step_label' => $stepLabels[$step],
            'dto' => $dto 
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}