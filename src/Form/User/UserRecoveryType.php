<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use App\Validator\Constraints\IsValidHashForUser;

class UserRecoveryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',EmailType::class, ['label' => 'field.email', 'attr' => ['autofocus' => $options['phase'] == 'user','readonly' => $options['phase'] != 'user']])
            ->add('phase', HiddenType::class, ['data' => $options['phase'], 'mapped' => false])
        ;
        if ($options['phase'] == 'hash') {
            $builder
                ->add('emailhash', PasswordType::class, ['label' => 'label.emailhash', 'attr' => ['autofocus' => $options['phase'] == 'hash'],
                    'mapped' => false, 'constraints' => [new IsValidHashForUser($builder->getData(), 'emailhash', $builder)]
                ])
                ->add('plainPassword', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'first_options'  => ['block_name' => 'first', 'attr' => ['pattern' => '.{4,64}'], 'label' => 'label.new_password1', 'error_bubbling' => false],
                        'second_options' => ['block_name' => 'second', 'attr' => ['pattern' => '.{4,64}'], 'label' => 'label.new_password2', 'error_bubbling' => false],
                        'invalid_message' => 'password.missmatch',
                        'error_bubbling' => true,
                        'error_mapping' => ['.' => 'second'],
                    ]
                )
                ->add('resend', SubmitType::class, ['validation_groups' => false, 'left_icon' => 'fa-envelope', 'label' => 'label.resend_code', 'attr' => ['formnovalidate' => 'formnovalidate']])
                ->add('submit', SubmitType::class, ['left_icon' => 'fa-check', 'right_icon' => 'fa-chevron-right', 'attr' => ['class' => 'btn-success'], 'label' => 'label.chpwd'])
            ;
        } else {
            $builder
                ->add('submit', SubmitType::class, ['left_icon' => 'fa-envelope', 'right_icon' => 'fa-chevron-right', 'attr' => ['class' => 'btn-success'], 'label' => 'action.sendcode'])
                ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['phase']);
        $resolver->setDefaults([
            'fieldname' => 'email',
            'data_class' => 'App\Entity\Security\User',
            'translation_domain' => 'user',
            'phase' => 'hash',
        ]);
    }
}