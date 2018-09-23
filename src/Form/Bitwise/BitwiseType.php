<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/10/2017
 * Time: 11.49
 */

namespace App\Form\Bitwise;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BitwiseType
 * @package App\Form\Bitwise
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class BitwiseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for ($i = 0; $i < $options['bits']; $i++)
            $builder->add('bit_' . $i, CheckboxType::class, [
                'label' => $options['labels'][$i],
                'attr' => ['class' => 'inline'],
                'label_attr' => ['class' => 'checkbox-inline'],
                'required' => false,
            ]);

        $builder->addModelTransformer(
            new BitwiseToArrayTransformer($options['bits'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'bits' => '32',
            'labels' => range(0, 31),
            'compound' => true,
            'data_class' => null,
            'constraints' => null,
            'error_bubbling' => false,
            'attr' => ['autofocus' => false],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bitwise';
    }
}