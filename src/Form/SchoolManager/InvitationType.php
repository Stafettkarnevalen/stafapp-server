<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\SchoolManager;

use App\Entity\Schools\SchoolName;
use App\Entity\Schools\SchoolUnit;
use App\Form\Message\MessageType;
use App\Form\PhoneNumber\PhoneNumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $iid = $builder->getData()->getId();

        $builder
            ->add('username', EmailType::class, [
                'label' => 'field.uname'
            ])
            ->add('schoolUnit', EntityType::class, [
                'class' => SchoolUnit::class,
                'choice_label' => 'name',
                'label' => 'field.sunit',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => 'messages',
                'left_icon' => 'fa-save',
                'right_icon' => 'fa-check',
                'attr' => ['class' => 'btn-success form-submit'],
                'label' => 'action.save',
            ])
            ->add('close', ButtonType::class, [
                'translation_domain' => 'messages',
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                ],
                'label' => 'action.close',
            ])
        ;

        if ($iid) $builder->add('delete', ButtonType::class, [
            'translation_domain' => 'messages',
            'left_icon' => 'fa-trash',
            'right_icon' => 'fa-minus',
            'attr' => [
                'class' => 'btn-danger',
                'data-toggle' => 'confirm',
                'data-reload' => 'true',
                'data-title' => $options['delete_title'],
                'value' => $options['delete_path'],
            ],
            'label' => 'action.delete',
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Security\SchoolManagerPosition',
            'available_units' => [],
            'translation_domain' => 'printicpal',
            'delete_title' => '',
            'delete_path' => '',
        ]);
    }
}