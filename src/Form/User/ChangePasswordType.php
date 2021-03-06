<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ChangePasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('current',PasswordType::class, ['label' => 'label.current_password', 'attr' => ['autofocus' => true], 'mapped' => false])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                    'first_options'  => ['block_name' => 'first', 'attr' => ['pattern' => '.{4,64}'], 'label' => 'label.new_password1', 'error_bubbling' => false],
                    'second_options' => ['block_name' => 'second', 'attr' => ['pattern' => '.{4,64}'], 'label' => 'label.new_password2', 'error_bubbling' => false],
                    'invalid_message' => 'password.missmatch',
                    'error_bubbling' => true,
                    'error_mapping' => ['.' => 'second'],
                ]
            )

            ->add('submit', SubmitType::class, ['left_icon' => 'fa-check', 'right_icon' => 'fa-chevron-right', 'attr' => ['class' => 'btn-success'], 'label' => 'label.chpwd'])
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Security\User',
            'translation_domain' => 'user',
        ]);
    }
}