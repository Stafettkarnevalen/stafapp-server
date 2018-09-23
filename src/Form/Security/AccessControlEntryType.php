<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/10/2017
 * Time: 1.16
 */

namespace App\Form\Security;


use App\Entity\Security\SimpleACE;
use App\Form\Bitwise\BitwiseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessControlEntryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mask', BitwiseType::class, [
            'label' => false,
            'bits' => 8,
            'labels' => ['view', 'create', 'edit', 'delete',
                'undelete', 'operator', 'master', 'owner'],

        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SimpleACE::class,
        ]);
    }
}