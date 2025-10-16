<?php

namespace App\Form;

use App\Entity\Figure;
use App\Entity\VideoFigure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VideoFigureFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('embedUrl', TextareaType::class, [
                'label' => 'false',
                'attr' =>
                    [
                        'class' => 'w-100',
                    ]
            ])
//            ->add('figure', EntityType::class, [
//                'class' => Figure::class,
//                'choice_label' => 'id',
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VideoFigure::class,
        ]);
    }
}
