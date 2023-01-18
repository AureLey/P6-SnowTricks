<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('file', FileType::class, [
            // 'mapped' => false,
            // 'data_class' => null,          
            'required' => false,
            'constraints' => [                
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                                'image/jpeg',
                                'image/png',],
                        'mimeTypesMessage' => 'Please upload a valid image',])
                    ,]])            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'label' => false //Remove Fieldset legend ( collection index)
        ]);
    }
}