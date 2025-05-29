<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProfileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-lime-500',
                    'placeholder' => 'Entrez votre nom',
                ],
                'label_attr' => [
                    'class' => 'block text-gray-700 mb-1 font-semibold',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-lime-500',
                    'placeholder' => 'Entrez votre prénom',
                ],
                'label_attr' => [
                    'class' => 'block text-gray-700 mb-1 font-semibold',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-lime-500',
                    'placeholder' => 'Ex : 06 12 34 56 78',
                ],
                'label_attr' => [
                    'class' => 'block text-gray-700 mb-1 font-semibold',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
