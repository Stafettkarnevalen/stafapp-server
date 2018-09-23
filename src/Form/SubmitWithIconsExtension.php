<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 27/11/2016
 * Time: 10.34
 */

namespace App\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmitWithIconsExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['left_icon'])
            $builder->setAttribute('left_icon', $options['left_icon']);
        if ($options['right_icon'])
            $builder->setAttribute('right_icon', $options['right_icon']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['left_icon'] = $options['left_icon'];
        $view->vars['right_icon'] = $options['right_icon'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('left_icon', 'right_icon'));
        $resolver->setDefaults(array(
            'left_icon' => null,
            'right_icon' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('left_icon', 'right_icon'));
        $resolver->setDefaults(array(
            'left_icon' => null,
            'right_icon' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return SubmitType::class; // Extend the submit field type
    }
}