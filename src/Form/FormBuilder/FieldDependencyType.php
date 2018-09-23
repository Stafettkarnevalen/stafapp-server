<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\FormBuilder;


use App\Entity\Forms\FormField;
use App\Entity\Forms\FormFieldDependency;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FieldDependencyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('target', EntityType::class, [
                'class' => FormField::class,
                'choices' => [$options['target']],
                'choice_label' => function(FormField $field = null) { return $field ? $field->getTitle() : null; },
                'choice_value' => function(FormField $field = null) { return $field ? $field->getid() : null; },
                'label' => false,
                'label_attr' => ['class' => 'required'],
                'required' => true,
                'attr' => ['class' => 'hidden'],
            ])
            ->add('source', EntityType::class, [
                'class' => FormField::class,
                'choices' => $options['sources'],
                'choice_label' => function(FormField $field = null) { return $field ? $field->getTitle() : null; },
                // 'choice_name' => 'order',
                'choice_value' => function(FormField $field = null) { return $field ? $field->getid() : null; },
                'label' => 'label.source',
                'attr' => [
                    'onchange' => 'selectedDependency(this);',
                    'data-index' => $options['order'],
                ],
                // 'expanded' => true,
                'label_attr' => ['class' => 'required'],
                'required' => true,
            ])
            ->add('operator', ChoiceType::class, [
                //'choices' => (($dep && $dep->getSource()) ? $dep->getSource()->getDependencyOperators() : $options['operators']),
                'choices' => $options['operators'],
                'choice_label' => function($label = null) { return $label ? $label : null; },
                'label' => 'label.operator',
                'label_attr' => ['class' => 'required'],
                'required' => true,
            ])
            ->add('value', TextType::class, [
                'label' => 'label.value',
                'label_attr' => ['class' => 'required'],
                'required' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var FormFieldDependency $dep */
            $dep = $event->getData();
            if ($dep) {
                $form->add('operator', ChoiceType::class, [
                    'choices' => $dep->getSource()->getDependencyOperators(),
                    'choice_label' => function($label = null) { return $label ? $label : null; },
                    'label' => 'label.operator',
                    'label_attr' => ['class' => 'required'],
                    'required' => true,
                ]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Communication\FormFieldDependency',
            'sources' => [],
            'operators' => ['==', '!='],
            'target' => null,
            'translation_domain' => 'form',
            'validation_groups' => false,
            'order' => 0,
            'by_reference' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'fielddependency';
    }
}