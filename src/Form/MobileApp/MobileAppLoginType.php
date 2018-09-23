<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.45
 */
namespace App\Form\MobileApp;

use App\Entity\Clients\MobileAppTicket;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class MobileAppLoginType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $phase = $options['phase'];
        if ($phase == 'ticket'){
            $builder
                ->add('phase', HiddenType::class, [
                    'data' => $phase,
                    'mapped' => false
                ])
                ->add('username', EmailType::class, [
                    'translation_domain' => 'user',
                    'mapped' => false,
                    'label' => 'field.uname'
                ])
                ->add('submit', SubmitType::class, [
                    'translation_domain' => 'messages',
                    'left_icon' => 'fa-sign-out',
                    'right_icon' => 'fa-chevron-right',
                    'attr' => ['class' => 'btn-success'],
                    'label' => 'action.login'
                ])
            ;
        } else {
            $builder
                ->add('phase', HiddenType::class, [
                    'data' => $phase,
                    'mapped' => false
                ])
                ->add('cancel', SubmitType::class, [
                    'translation_domain' => 'messages',
                    'left_icon' => 'fa-undo',
                    'right_icon' => 'fa-close',
                    'attr' => ['class' => 'btn-danger'],
                    'label' => 'action.cancel'
                ])
                ->add('refresh', SubmitType::class, [
                    'translation_domain' => 'messages',
                    'left_icon' => 'fa-refresh',
                    'right_icon' => 'fa-check',
                    'attr' => ['class' => 'btn-success'],
                    'label' => 'action.refresh'
                ])
            ;

        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MobileAppTicket::class,
            'translation_domain' => 'security',
            'phase' => 'ticket',
        ]);
    }
}
