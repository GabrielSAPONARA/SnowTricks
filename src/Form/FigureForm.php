<?php

namespace App\Form;

use App\Entity\Figure;
use App\Entity\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class FigureForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('creationDate')
            ->add('dateOfLastUpdate')
            ->add('groupes', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('images', FileType::class, [
                'label' => 'Images de la figure',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
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

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Figure::class,
        ]);
    }
}
