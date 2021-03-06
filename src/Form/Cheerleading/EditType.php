<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\Cheerleading;


use App\Entity\Services\ServiceCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EditType
 * @package App\Form\Cheerleading
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class EditType extends AbstractType
{
    /**
     * @const CLASS_OF_SET The class of range
     */
    const CLASS_OF_RANGE = [1,2,3,4,5,6,7,8,9,10,11,12];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $rid = $builder->getData()->getId();
        $nonDel = $builder->getData()->getCompetitions()->count();
        $builder
            ->add('name', TextType::class, ['label' => 'label.name'])
            ->add('abbreviation', TextType::class, [
                'label' => 'label.abbreviation',
                'required' => false,
            ])
            ->add('serviceCategory', EntityType::class, [
                'class' => ServiceCategory::class,
                'expanded' => false,
                'multiple' => false,
                'choice_label' => 'title',
                'label' => 'label.service_category',
                'choice_translation_domain' => false,
            ])
            ->add('minClassOf', ChoiceType::class, [
                'label' => 'label.min_class_of',
                'choices' => array_combine(self::CLASS_OF_RANGE,self::CLASS_OF_RANGE),
            ])
            ->add('maxClassOf', ChoiceType::class, [
                'label' => 'label.max_class_of',
                'choices' => array_combine(self::CLASS_OF_RANGE,self::CLASS_OF_RANGE),
            ])
            ->add('maxSize', IntegerType::class, ['label' => 'label.maxSize'])
            ->add('from', DateTimeType::class, [
                'label' => 'label.from',
                'years' => range(1960, date("Y") + 5),
            ])
            ->add('until', DateTimeType::class, [
                'label' => 'label.until',
                'years' => range(1960, date("Y") + 5),
                'required' => false,
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'label.status',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['label.inactive' => 0, 'label.active' => 1],
            ])
            ->add('schoolTypes', ChoiceType::class, [
                'choices' => $options['available_school_types'],
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'name',
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'checkbox'],
                'label' => 'label.school_types',
                'choice_translation_domain' => false,
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

        if ($rid) $builder->add('delete', ButtonType::class, [
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
            'disabled' => $nonDel,
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Cheerleading\CheerleadingEvent',
            'translation_domain' => 'cheerleading',
            'available_school_types' => [],
            'delete_title' => '',
            'delete_path' => '',
        ]);
    }
}