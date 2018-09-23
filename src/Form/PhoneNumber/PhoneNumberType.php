<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 26/11/2016
 * Time: 16.12
 */

namespace App\Form\PhoneNumber;


use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PhoneNumberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaultArea = $options['defaultArea'];
        unset($options['defaultArea']);
        $builder
            ->add('areaCode', AreaCodeType::class, ['data' => $defaultArea, 'error_bubbling' => true])
            ->add('number', IntegerType::class, [
                'constraints' => $options['constraints'],
                'attr' => [
                    'pattern' => '[1-9]{1}[0-9]{4,7}',
                    'autofocus' => (array_key_exists('attr', $options) && array_key_exists('autofocus', $options['attr']) ? $options['attr']['autofocus'] : false),
                    'type' => 'number'
                ],
                'error_bubbling' => true,
            ]);
        $builder->addModelTransformer(
            new PhoneNumberToArrayTransformer()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'defaultArea' => '+358',
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
        return 'phonenumber';
    }

}