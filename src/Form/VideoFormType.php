<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class VideoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', UrlType::class,[
                        'default_protocol' => 'https',
                        // 'help' =>'www.youtube.com/embed...',
                        'label' =>'URL video',
                        'invalid_message' => 'URL is not correct',
                        'attr' => [
                            'class' => 'form-control',
                            'placeholder' => 'www.youtube.com/embed/...',
                            'pattern' => '^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$'
                        ]])           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
            'label' => false //Remove Fieldset legend ( collection index)
        ]);
    }
}
