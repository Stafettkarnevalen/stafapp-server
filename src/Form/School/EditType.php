<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\School;

use App\Form\Message\MessageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $sid = $builder->getData()->getId();

        $builder
            ->add('password', TextType::class, [
                'label' => 'field.password'
            ])
            ->add('number', TextType::class, [
                'label' => 'field.number',
            ]);

        if ($options['include_name']) $builder
            ->add('name', EditNameType::class, [
                'label' => false,
                'embedded' => true,
                'data' => $builder->getData()->getName(),
                'error_bubbling' => true,
                'is_api' => $options['is_api'],
            ]);

        if (!$options['is_api']) {
            $builder
                ->add('isActive', ChoiceType::class, [
                    'label' => 'field.status',
                    'expanded' => true,
                    'choice_attr' => function () { return ['class' => 'inline']; },
                    'label_attr' => ['class' => 'radio-inline'],
                    'choice_translation_domain' => 'messages',
                    'choices' => ['label.inactive' => 0, 'label.active' => 1],
                ])
                ->add('message', MessageType::class, [
                    'independent' => false,
                    'label' => 'field.message',
                    'translation_domain' => 'school',
                    'required' => false,
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

            if ($sid) $builder->add('delete', ButtonType::class, [
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
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Schools\School',
            'translation_domain' => 'school',
            'delete_title' => '',
            'delete_path' => '',
            'include_name' => true,
            'is_api' => false,
        ]);
    }
}