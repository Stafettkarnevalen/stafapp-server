<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 07/09/2017
 * Time: 2.45
 */

namespace App\Form\Relay;


use App\Entity\Relays\Relay;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderChoiceType extends ChoiceType
{
    const CONTEXT_RELAY_GENDER      = 0;
    const CONTEXT_START_GENDER      = 1;
    const CONTEXT_COMPETITOR_GENDER = 2;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch($options['context']) {
            case self::CONTEXT_RELAY_GENDER:
                $options['choices'] = [
                    'FEMALE' => Relay::GENDER_FEMALE,
                    'MALE' => Relay::GENDER_MALE,
                    'ORDERED_MIXED' => Relay::GENDER_ORDERED_MIXED,
                    'UNORDERED_MIXED' => Relay::GENDER_UNORDERED_MIXED,
                ];
                break;

            case self::CONTEXT_START_GENDER:
                $options['choices'] = [
                    'FEMALE' => Relay::START_GENDER_FEMALE,
                    'MALE' => Relay::START_GENDER_MALE,
                    'ANY' => Relay::START_GENDER_ANY,
                ];
                break;

            case self::CONTEXT_COMPETITOR_GENDER:
                $options['choices'] = [
                    'FEMALE' => Relay::GENDER_FEMALE,
                    'MALE' => Relay::GENDER_MALE,
                ];
                break;
        }
        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'context' => self::CONTEXT_RELAY_GENDER,
        ]);
    }
}