<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 14/10/2017
 * Time: 2.25
 */

namespace App\Form\Module;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsModuleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('showTitle', ChoiceType::class, [
                'label' => 'label.module.news_module.show_title',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['label.module.news_module.no' => 0, 'label.module.news_module.yes' => 1],
            ])
            ->add('renderType', ChoiceType::class, [
                'label' => 'label.module.news_module.view',
                'choices' => [
                    'label.module.news_module.view_type_list' => 'list',
                    'label.module.news_module.view_type_carousel' => 'carousel'
                ],
            ])
            ->add('listLimit', IntegerType::class, [
                'label' => 'label.module.news_module.listLimit',
            ])
            ->add('showListPager', ChoiceType::class, [
                'label' => 'label.module.news_module.show_list_pager',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['label.module.news_module.no' => 0, 'label.module.news_module.yes' => 1],
            ])
            ->add('showCarouselIndicators', ChoiceType::class, [
                'label' => 'label.module.news_module.show_carousel_indicators',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['label.module.news_module.no' => 0, 'label.module.news_module.yes' => 1],
            ])
            ->add('showCarouselNavigation', ChoiceType::class, [
                'label' => 'label.module.news_module.show_carousel_navigation',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['label.module.news_module.no' => 0, 'label.module.news_module.yes' => 1],
            ])
            ->add('more', ChoiceType::class, [
                'label' => 'label.module.news_module.show_more',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['label.module.news_module.no' => 0, 'label.module.news_module.yes' => 'label.module.news_module.read_more'],
            ])
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
            'data_class' => 'App\Entity\Modules\NewsModule',
            'translation_domain' => 'module',
            'delete_title' => '',
            'delete_path' => '',
        ]);
    }
}