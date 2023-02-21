<?php

declare(strict_types=1);

/*
 * This file is part of Snowtricks
 *
 * (c)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\Group;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TrickFormType extends AbstractType
{
    /**
     * buildForm.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                    'label' => 'Trick name',
                    'label_attr' => ['class' => 'text-white'], ])
            ->add('content', TextareaType::class, [
                'label' => 'Trick Description',
            ])
            ->add('groupTrick', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'label' => 'Group of trick',
            ])
            ->add('videos', CollectionType::class, [
                'label' => false,
                'entry_type' => VideoFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('images', CollectionType::class, [
                'label' => false,
                'entry_type' => ImageFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])

            ->add('featuredImage', FileType::class, [
                'label' => 'Featured Image',
                'mapped' => false, // unmapped fields can't define their validation using annotations
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
