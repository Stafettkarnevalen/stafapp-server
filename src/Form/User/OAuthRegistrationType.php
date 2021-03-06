<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\User;

use App\Entity\Security\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Form\PhoneNumber\PhoneNumberType;

class OAuthRegistrationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$builder->getData()->getId()) {
            $builder
                ->add('username', EmailType::class, [
                    'label' => 'field.uname',
                    'disabled' => $builder->getData()->getUsername() ? true : false,
                    'attr' =>['autofocus' => $builder->getData()->getUsername() ? false : true]])
                ->add('firstname', TextType::class, [
                    'label' => 'field.firstname',
                    'disabled' => $builder->getData()->getFirstname() ? true : false,
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'field.lastname',
                    'disabled' => $builder->getData()->getLastname() ? true : false,
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'field.phone',
                    'defaultArea' => '+358',
                    'constraints' => new NotBlank(),
                    'disabled' => $builder->getData()->getPhone() ? true : false,
                ])
                ->add($options['service'].'Id', HiddenType::class)
                ->add($options['service'].'AccessToken', HiddenType::class)
                ->add('consented', CheckboxType::class, [
                    'label_attr' => ['class' => 'radio-inline'],
                    'label' => 'yes',
                    'translation_domain' => 'messages',
                ]);
        } else if ($builder->getData()->getId()) {
            $builder
                ->add('username', EmailType::class, [
                    'label' => 'field.uname',
                    'disabled' =>true
                ])
                ->add('firstname', TextType::class, [
                    'label' => 'field.firstname',
                    'disabled' =>true                ])
                ->add('lastname', TextType::class, [
                    'label' => 'field.lastname',
                    'disabled' =>true                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'field.phone',
                    'defaultArea' => '+358',
                    'constraints' => new NotBlank(),
                    'disabled' => $builder->getData()->getPhone() ? true : false,
                ])
                ->add($options['service'].'Id', HiddenType::class)
                ->add($options['service'].'AccessToken', HiddenType::class)
                ->add('plainPassword', PasswordType::class, [
                    'label' => 'field.password1'
                ]);
        }

        $builder
            ->add('submit', SubmitType::class, [
                'left_icon' => 'fa-check',
                'right_icon' => 'fa-chevron-right',
                'attr' => [
                    'class' => 'btn-success'
                ],
                'label' => 'action.register',
                'translation_domain' => 'messages',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'user',
            'googleplusId' => null,
            'googleplusAccessToken' => null,
            'twitterId' => null,
            'twitterAccessToken' => null,
            'facebookId' => null,
            'facebookAccessToken' => null,
            'service' => 'googleplus',
        ]);
    }
}