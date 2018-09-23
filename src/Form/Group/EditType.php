<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\Group;

use App\Entity\Security\Group;
use App\Entity\Security\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $rid = $builder->getData()->getId();
        $guestOrAdmin = ($rid === 1 || $rid === 2 || $rid === 3);

        $builder
            ->add('name', TextType::class, ['label' => 'field.name'])
            ->add('email', EmailType::class, ['label' => 'field.email'])
            ->add('isGoogleSynced', ChoiceType::class, [
                'label' => 'field.googleSynced',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['action.no' => 0, 'action.yes' => 1],
                'choice_translation_domain' => 'messages',
            ])
            ->add('googleId', TextType::class, [
                'label' => 'field.googleId',
                'attr' => ['readonly' => 'readonly'],
            ])
            ->add('loginRoute', TextType::class, ['label' => 'field.loginRoute'])
            ->add('logoutRoute', TextType::class, ['label' => 'field.logoutRoute'])
            ->add('roles', ChoiceType::class, [
                'choices' => $options['available_roles'],
                'expanded' => true,
                'multiple' => true,
                'choice_label' => function($role) { return $role; },
                'choice_attr' => function () { return ['class' => 'inline']; },
                'choice_attr' => ['class' => 'inline'],
                'label_attr' => ['class' => 'checkbox'],
                'label' => 'field.roles',
                'choice_translation_domain' => 'role',
            ])
            ->add('users', ChoiceType::class, [
                'choices' => $options['available_users'],
                'expanded' => true,
                'multiple' => true,
                'choice_label' => function (User $u) { return "{$u->getFullname()} <{$u->getUsername()}>"; },
                'choice_attr' => ['class' => 'inline'],
                'label_attr' => ['class' => 'checkbox'],
                'label' => 'field.users',
                'choice_translation_domain' => false,
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => 'messages',
                'left_icon' => 'fa-save',
                'right_icon' => 'fa-check',
                'attr' => ['class' => 'btn-success form-submit'],
                'label' => 'action.save',
            ])
            ->add('close', ButtonType::class, [
                'translation_domain' => 'messages',
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                ],
                'label' => 'action.close',
            ])
        ;

        if ($rid) $builder
            ->add('delete', ButtonType::class, [
                'translation_domain' => 'messages',
                'left_icon' => 'fa-remove',
                'right_icon' => 'fa-minus',
                'attr' => [
                    'class' => 'btn-danger',
                    'data-toggle' => 'confirm',
                    'data-reload' => 'true',
                    'data-title' => $options['delete_title'],
                    'value' => $options['delete_path'],
                ],
                'label' => 'action.delete',
                'disabled' => $builder->getData()->getIsSystem(),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
            'translation_domain' => 'group',
            'available_users' => [],
            'available_roles' => [],
            'delete_title' => '',
            'delete_path' => '',
        ]);
    }
}