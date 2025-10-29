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

class FigureForm2 extends AbstractType
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
            ->add('group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
                'attr' =>
                [
                    "class" => "vertical-checkboxes",
                ],
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
