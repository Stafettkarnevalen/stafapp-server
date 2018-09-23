<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\FormBuilder;


use App\Entity\Forms\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class EditType extends AbstractType
{
    const CONTEXT_SET = [
        Form::CONTEXT_USER, Form::CONTEXT_SCHOOL, Form::CONTEXT_SCHOOL_UNIT, Form::CONTEXT_MANAGERS, Form::CONTEXT_RACE
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $id = $builder->getData()->getId();
        $builder
            ->add('title', TextType::class, ['label' => 'label.title'])
            ->add('text', TextareaType::class, [
                'label' => 'label.text',
            ])
            ->add('context', ChoiceType::class, [
                'label' => 'label.context',
                'choices' => array_combine(self::CONTEXT_SET,self::CONTEXT_SET),
            ])
            ->add('from', DateTimeType::class, [
                'label' => 'label.from',
            ])
            ->add('until', DateTimeType::class, [
                'label' => 'label.until',
                'required' => false,
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'label.status',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['label.inactive' => 0, 'label.active' => 1],
            ])
            ->add('isMandatory', ChoiceType::class, [
                'label' => 'label.mandatory',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['label.mandatory_no' => 0, 'label.mandatory_yes' => 1],
            ])
            ->add('submit', SubmitType::class, [
                'left_icon' => 'fa-save',
                'right_icon' => 'fa-check',
                'attr' => ['class' => 'btn-success form-submit'],
                'label' => 'label.save',
            ])
            ->add('close', ButtonType::class, [
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                ],
                'label' => 'label.close',
            ])
        ;
        if ($id) $builder->add('delete', ButtonType::class, [
            'left_icon' => 'fa-trash',
            'right_icon' => 'fa-minus',
            'attr' => [
                'class' => 'btn-danger',
                'data-toggle' => 'confirm',
                'data-reload' => 'true',
                'data-title' => $options['delete_title'],
                'value' => $options['delete_path'],
            ],
            'label' => 'label.delete',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Communication\Form',
            'translation_domain' => 'form',
            'validation_groups' => 'Form',
            'delete_title' => '',
            'delete_path' => '',
        ]);
    }
}