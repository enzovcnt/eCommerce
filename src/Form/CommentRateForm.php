<?php

// src/Form/CommentRateFormType.php
namespace App\Form;

use App\Entity\CommentRate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentRateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rate', IntegerType::class, [
                'label' => 'Utile ? (1 = Non, 5 = Très utile)',
                'attr' => ['min' => 1, 'max' => 5]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommentRate::class,
        ]);
    }
}
