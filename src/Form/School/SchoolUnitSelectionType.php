<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 15/12/2016
 * Time: 8.02
 */

namespace App\Form\School;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Form\SessionEntities;
use App\Entity\Schools\SchoolUnit;

class SchoolUnitSelectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['schoolUnits']) > 1) {
            if (!in_array($builder->getData()->_schoolUnit, $options['schoolUnits'])){
                $builder->getData()->__set('_schoolUnit', null);
            }
            $builder->add('_schoolUnit', EntityType::class, [
                'class' => SchoolUnit::class,
                'attr' => ['onchange' => 'this.form.submit();'],
                'label' => 'nav.selected_school_unit',
                'placeholder' => 'nav.select_school_unit',
                'choices' => $options['schoolUnits'],
                'choice_translation_domain' => false,
                'choice_label' => 'type.name',
            ]);
        } else {
            $builder->getData()->__set('_schoolUnit', $options['schoolUnits'][0]);
            $builder->add('_schoolUnit', EntityType::class, [
                'class' => 'App:SchoolUnit',
                'attr' => ['readonly' => 'readonly', 'disabled' => 'disabled'],
                'label' => 'nav.selected_school_unit',
                'choices' => $options['schoolUnits'],
                'choice_translation_domain' => false,
                'choice_label' => 'name',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('schoolUnits', 'session'));
        $resolver->setDefaults(array(
            'data_class' => SessionEntities::class,
            'translation_domain' => 'nav',
            'shoolUnits' => [],
            'session' => null,
        ));
    }
}