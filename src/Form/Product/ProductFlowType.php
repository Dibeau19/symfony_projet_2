<?php

namespace App\Form\Product;

use Symfony\Component\Form\Flow\AbstractFlowType;
use Symfony\Component\Form\Flow\FormFlowBuilderInterface;

class ProductFlowType extends AbstractFlowType
{
    public function buildFormFlow(FormFlowBuilderInterface $builder, array $options): void
    {
        $builder
            ->addStep('type', Step\BookTypeStepType::class, [
                'label' => 'Type de livre'
            ])
            ->addStep('details', Step\BookDetailsStepType::class, [
                'label' => 'Informations'
            ])
            ->addStep('logistics', Step\BookLogisticsStepType::class, [
                'label' => 'Logistique'
            ])
            ->addStep('summary', Step\BookSummaryStepType::class, [
                'label' => 'Récapitulatif et Confirmation'
            ]);
    }
}