<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\School;

use App\Entity\Schools\SchoolUnit;
use App\Entity\Security\SchoolManager;
use App\Entity\Security\SchoolManagerPosition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditUnitManagerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $smpid = $builder->getData()->getId();

        $builder
            ->add('from', DateTimeType::class, [
                'label' => 'field.from',
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'js-datepicker'
                ],
                'format' => 'dd.MM.yyyy',
            ])
            ->add('until', DateTimeType::class, [
                'label' => 'field.until',
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'js-datepicker',
                ],
                'format' => 'dd.MM.yyyy',
                'required' => false,
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'field.status',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choice_translation_domain' => 'messages',
                'choices' => ['label.inactive' => 0, 'label.active' => 1],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'field.type',
                'choice_translation_domain' => 'school',
                'choices' => [
                    'label.assigned' => 'ASSIGNED',
                    'label.invitation' => 'INVITATION',
                    'label.request' => 'REQUEST'
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'field.status',
                'choice_translation_domain' => 'school',
                'choices' => [
                    'label.accepted' => 'ACCEPTED',
                    'label.denied' => 'DENIED',
                    'label.pending' => 'PENDING',
                ],
            ])
            ->add('schoolUnit', EntityType::class, [
                'class' => SchoolUnit::class,
                'choice_label' => 'name',
                'label' => 'field.sunit',
                'required' => true,
                'choices' => $options['schoolUnit'] ? [$options['schoolUnit']] : $options['schoolUnits']
            ])
            ->add('username', EmailType::class, [
                'label' => 'field.uname',
                'required' => false,
            ])
            ->add('manager', EntityType::class, [
                'class' => SchoolManager::class,
                'choice_label' => 'fullname',
                'label' => 'field.manager',
                'required' => false,
                'choices' => $options['schoolManager'] ? [$options['schoolManager']] : $options['schoolManagers']
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
        if ($smpid) $builder->add('delete', ButtonType::class, [
            'translation_domain' => 'messages',
            'left_icon' => 'fa-trash',
            'right_icon' => 'fa-minus',
            'attr' => [
                'class' => 'btn-danger',
                'data-toggle' => 'confirm',
                'data-reload' => 'true',
                'data-title' => $options['delete_title'],
                'value' => $options['delete_path'],
            ],
            'label' => 'action.delete',
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SchoolManagerPosition::class,
            'translation_domain' => 'school',
            'delete_title' => '',
            'delete_path' => '',
            'schoolUnit' => null,
            'schoolUnits' => null,
            'username' => null,
            'schoolManager' => null,
            'schoolManagers' => null,
        ]);
    }
}