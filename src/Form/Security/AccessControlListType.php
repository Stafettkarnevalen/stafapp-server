<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/10/2017
 * Time: 1.16
 */

namespace App\Form\Security;


use App\Entity\Security\SimpleACE;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AccessControlListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var integer $index
         * @var SimpleACE $ace
         */
        foreach ($options['aces'] as $index => $ace) {
            $builder->add($index, AccessControlEntryType::class, [
                'data' => $ace,
                'label' => $ace->getRole(),
                'translation_domain' => $options['ace_translation_domain'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'aces' => [],
            'ace_translation_domain' => 'messages',
        ]);
    }
}