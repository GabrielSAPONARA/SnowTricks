<?php

namespace App\Form;

use App\Entity\Figure;
use App\Entity\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class FigureForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,
            [
                "attr" =>
                [
                    "class" => "form-control",
                    "placeholder" => "Figure name"
                ]
            ])
            ->add('description', TextareaType::class,
            [
                "attr" =>
                [
                    "class" => "form-control",
                    "placeholder" => "Description"
                ]
            ])
            ->add('creationDate')
            ->add('dateOfLastUpdate')
            ->add('groupes', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'attr' =>
                [
                    "class" => "vertical-checkboxes",
                ],
            ])
            ->add('images', FileType::class, [
                'label' => "Figure's pictures",
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' =>
                [
                    "class" => "d-flex",
                ]
//                'constraints' => [
//                    new Image([
////                        'maxSize' => '5M',
//                        'mimeTypesMessage' => 'Merci d\'uploader une image valide (jpeg/png/webp)',
////                        'minWidth' => 300,
////                        'minHeight' => 300,
//                        // d’autres options disponibles si besoin
//                    ])
//                ],
            ])
            ->add('videoFigures', CollectionType::class, [
                'entry_type' => VideoFigureFormType::class,
                'entry_options' => ['label' => 'URL de la vidéo (YouTube, Dailymotion...)'],
                'allow_add' => true,
                'allow_delete' => true,
//                'prototype' => true,
                'mapped' => false, // on les traite manuellement
                'required' => false,
                'by_reference' => false,
                'label' => false,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Figure::class,
        ]);
    }
}
