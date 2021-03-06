<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 17/12/2016
 * Time: 0.21
 */

namespace App\Form\Invoicing;

use App\Entity\Invoicing\BankAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankAccountEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $baid = $builder->getData()->getId();

        $builder
            ->add('name', TextType::class, [
                'label' => 'field.name'
            ])
            ->add('iban', TextType::class, [
                'label' => 'field.iban',
            ])
            ->add('bic', TextType::class, [
                'label' => 'field.bic',
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'field.status',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choice_translation_domain' => 'messages',
                'choices' => ['label.inactive' => 0, 'label.active' => 1],
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
        if ($baid) $builder->add('delete', ButtonType::class, [
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
        $resolver->setDefaults(array(
            'data_class' => BankAccount::class,
            'translation_domain' => 'invoice',
            'delete_title' => '',
            'delete_path' => '',
        ));
    }
}