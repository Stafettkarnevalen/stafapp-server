<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 14/10/2017
 * Time: 2.25
 */

namespace App\Form\Module;


use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextModuleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', CKEditorType::class, ['label' => 'label.module.text_module.text'])
            ->add('submit', SubmitType::class, [
                'left_icon' => 'fa-save',
                'right_icon' => 'fa-check',
                'attr' => ['class' => 'btn-success form-submit'],
                'label' => 'label.save',
            ])
            ->add('delete', ButtonType::class, [
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
            ])
            ->add('close', ButtonType::class, [
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                ],
                'label' => 'label.close',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Modules\TextModule',
            'translation_domain' => 'module',
            'delete_title' => '',
            'delete_path' => '',
        ]);
    }
}