<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\User;

use App\Form\Label\LabelType;
use App\Form\PhoneNumber\PhoneNumberType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class TicketLoginType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $type = null;
        switch ($options['type']) {
            case 'username':
                $type = EmailType::class;
                break;
            case 'phone':
                $type = PhoneNumberType::class;
                break;
        }

        if ($options['type'] != 'usb') {
            $builder
                ->add($options['type'], $type, [
                    'label' => 'login.' . $options['type'],
                    'mapped' => false, 'data' => $options[$options['type']],
                    'attr' => ['autofocus' => ($options['phase'] == 'ticket'), 'readonly' => ($options['phase'] != 'ticket')]
                ]);
        } else if ($options['type'] == 'usb' && $options['school_number'] && $options['school_password']){
            $builder
                ->add('secret', HiddenType::class, [
                    'data' => $options['usb'],
                    'mapped' => false
                ])
                ->add('label', LabelType::class, [
                    'data' => 'field.usb_key_invalid',
                    'attr' => [
                        'class' => 'alert-danger'
                    ],
                ])
                ->add('school_number', TextType::class, [
                    'data' => $options['school_number'],
                    'label' => 'field.school_number',
                    'mapped' => false,
                    'attr' => [
                        'readonly' => 'readonly'
                    ]
                ])
                ->add('school_password', TextType::class, [
                    'data' => $options['school_password'],
                    'label' => 'field.school_password',
                    'mapped' => false,
                    'attr' => [
                        'readonly' => 'readonly'
                    ]
                ])
                ->add('message', TextareaType::class, [
                    'label' => 'field.message',
                    'mapped' => false,
                    'attr' => [
                        'rows' => 8
                    ]
                ])
            ;

        } else {
            $builder
                ->add('label', LabelType::class, [
                    'data' => 'field.usb_key_tutorial',
                    'attr' => [
                        'class' => 'alert-info'
                    ],
                ]);
        }
        $builder
            ->add('phase', HiddenType::class, ['data' => $options['phase'], 'mapped' => false]);
        if ($options['phase'] == 'login') {
            $builder
                ->add('password', PasswordType::class,
                    ['label' => 'login.code', 'attr' => ['autofocus' => true], 'mapped' => false])
                ->add('resend', SubmitType::class, ['left_icon' => 'fa-envelope',
                    'label' => 'login.resend_code', 'validation_groups' => false,
                    'attr' =>['formnovalidate' => 'formnovalidate']])
                ->add('submit', SubmitType::class, ['left_icon' => 'fa-check',
                    'right_icon' => 'fa-chevron-right', 'attr' => ['class' => 'btn-success'], 'label' => 'login.submit']);
        } else {
            if ($options['type'] != 'usb') {
                $builder
                    ->add('submit', SubmitType::class, ['left_icon' => 'fa-check',
                        'right_icon' => 'fa-chevron-right', 'attr' => ['class' => 'btn-success'], 'label' => 'login.send_code']);
            } else {
                $builder
                    ->add('submit', SubmitType::class, [
                        'left_icon' => 'fa-sign-out',
                        'right_icon' => 'fa-chevron-right',
                        'attr' => ['class' => 'btn-success'],
                        'label' => 'login.send_support']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Security\UserTicket',
            'translation_domain' => 'security',
            'phase' => 'ticket',
            'type' => 'email',
            'username' => null,
            'phone' => null,
            'usb' => null,
            'school_number' => null,
            'school_password' => null,
            'tries' => 3,
        ]);
    }
}
