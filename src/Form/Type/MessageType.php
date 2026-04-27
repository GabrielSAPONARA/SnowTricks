<?php

namespace App\Form\Type;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'required'    => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'The message cannot be empty. Please write something.',
                    ]),
                    new Length([
                        'min'        => 2,
                        'minMessage' => 'Your message is too short. It must be at least {{ limit }} characters.',
                    ]),
                ],
                'attr'        => [
                    'rows'        => 3,
                    'placeholder' => 'Write your comment here...',
                    'id'          => 'message_content'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}