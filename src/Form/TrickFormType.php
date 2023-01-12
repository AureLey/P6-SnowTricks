<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Trick;
use App\Form\ImageFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TrickFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,['label' => null])            
            ->add('content')                        
            ->add('groupTrick', EntityType::class, [
                                'class' => Group::class,
                                'choice_label'=>'name',])            
            ->add('videos', CollectionType::class, [
                                'label' => false,
                                'entry_type'    => VideoFormType::class,                             
                                'allow_add'     => true,
                                'allow_delete' => true,
                                'by_reference'  => false,])
            ->add('images', CollectionType::class, [
                                'label' => false,
                                'entry_type'    => ImageFormType::class,                                
                                'allow_add'     => true,
                                'allow_delete' => true,
                                'by_reference'  => false,                                
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class
            
        ]);
    }
}
