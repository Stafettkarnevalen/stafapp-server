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

class SchoolSelectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['schools']) > 1) {
            if (!in_array($builder->getData()->_school, $options['schools'])){
                $builder->getData()->__set('_school', null);
            }
            $builder->add('_school', EntityType::class, [
                'class' => 'App:Schools\School',
                'attr' => ['onchange' => 'this.form.submit();'],
                'label' => 'nav.selected_school',
                'placeholder' => 'nav.select_school',
                'choices' => $options['schools'],
                'choice_translation_domain' => false,
                'choice_label' => 'name',
            ]);
        } else if (count($options['schools']) == 1) {
            $builder->getData()->__set('_school', $options['schools'][0]);
            $builder->add('_school', EntityType::class, [
                'class' => 'App:Schools\School',
                'attr' => ['readonly' => 'readonly', 'disabled' => 'disabled'],
                'label' => 'nav.selected_school',
                'choices' => $options['schools'],
                'choice_translation_domain' => false,
                'choice_label' => 'name',
            ]);
        } else {
            $builder->getData()->__set('_school', null);
            $builder->add('_school', EntityType::class, [
                'class' => 'App:Schools\School',
                'attr' => ['readonly' => 'readonly', 'disabled' => 'disabled'],
                'label' => 'nav.selected_school',
                'choices' => $options['schools'],
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
        $resolver->setDefined(array('schools', 'add'));
        $resolver->setDefaults(array(
            'data_class' => SessionEntities::class,
            'translation_domain' => 'nav',
            'shools' => [],
        ));
    }
}