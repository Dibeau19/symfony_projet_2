<?php

namespace App\Form\Product\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class BookTypeStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', ChoiceType::class, [
            'label' => 'Quel type de livre souhaitez-vous ajouter ?',
            'choices' => [
                'Roman' => 'roman',
                'Bande Dessinée' => 'bd',
                'Manga' => 'manga',
            ],
            'expanded' => true, 
            'multiple' => false,
        ]);
    }
}