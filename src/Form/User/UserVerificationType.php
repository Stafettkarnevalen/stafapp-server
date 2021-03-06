<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\User;

use App\Form\PhoneNumber\PhoneNumberType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use App\Validator\Constraints\IsValidHashForUser;

class UserVerificationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $type = $options['fieldname'] == 'username' ? EmailType::class : PhoneNumberType::class;
        $label = $options['fieldname'] == 'username' ? 'label.verify_email' : 'label.verify_phone';
        $builder
            //->setRequired(false)
            ->add($options['fieldname'], $type, ['label' => 'label.' . $options['fieldname']])
            ->add($options['hashname'], PasswordType::class, ['label' => 'label.' . $options['hashname'],
                'attr' =>['autofocus' => true],
                'mapped' => false,
                'constraints' => [new IsValidHashForUser($builder->getData(), $options['hashname'], $builder)]
            ])
            ->add('resend', SubmitType::class, ['left_icon' => 'fa-envelope', 'label' => 'label.resend_code', 'validation_groups' => false, 'attr' => ['formnovalidate' => 'formnovalidate']])
            ->add('submit', SubmitType::class, ['left_icon' => 'fa-check', 'right_icon' => 'fa-chevron-right', 'attr' => ['class' => 'btn-success'], 'label' => $label])

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'fieldname' => 'username',
            'hashname' => 'emailhash',
            'data_class' => 'App\Entity\Security\User',
            'translation_domain' => 'user',
        ]);
    }
}