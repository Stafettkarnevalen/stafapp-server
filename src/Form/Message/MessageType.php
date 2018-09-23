<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 12/06/2017
 * Time: 18.19
 */

namespace App\Form\Message;

use App\Entity\Communication\Message;
use App\Entity\Interfaces\MessageDistributionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MessageType
 *
 * @package App\Form\Message
 */
class MessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['independent']) {
            $builder
                ->add('distribution', ChoiceType::class, [
                    'label' => 'field.to',
                    'choices' => $options['available_recipients'],
                    'choice_value' => 'value',
                    'choice_label' => function ($dist) {
                        /** @var MessageDistributionInterface $dist */
                        return $dist->getLabel();
                    },
                    'choice_translation_domain' => false,
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [Message::TYPE_EMAIL, Message::TYPE_SMS, Message::TYPE_INTERNAL],
                    'expanded' => true,
                    'multiple' => true,
                    'choice_label' => function ($val) {
                        return strtolower($val);
                    },
                    'choice_attr' => function () {
                        return ['class' => 'inline'];
                    },
                    'label_attr' => ['class' => 'checkbox-inline'],
                    'label' => 'field.type'
                ])
                ->add('title', TextType::class, [
                    'label' => 'field.title',
                    'attr' => [
                        'autofocus' => ($options['parent'] == null)
                    ]
                ])
                ->add('text', TextareaType::class, [
                    'label' => 'field.text',
                    'attr' => ['autofocus' => ($options['parent'] != null), 'rows' => 10]
                ])
                ->add('attachments', CollectionType::class, [
                    'entry_type' => AttachmentType::class,
                    'entry_options' => [
                        'translation_domain' => false,
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => false,
                    'translation_domain' => false,
                ])
                ->add('close', ButtonType::class, [
                    'translation_domain' => 'messages',
                    'left_icon' => 'fa-chevron-left',
                    'right_icon' => 'fa-close',
                    'label' => 'action.close',
                    'attr' => [
                        'class' => 'btn-default',
                        'data-dismiss' => 'modal',
                    ],
                ])
                ->add('submit', SubmitType::class, [
                    'translation_domain' => 'messages',
                    'left_icon' => 'fa-sign-out',
                    'right_icon' => 'fa-chevron-right',
                    'attr' => ['class' => 'btn-success form-submit'],
                    'label' => 'action.send']);

        } else {
            $builder
                ->add('type', ChoiceType::class, [
                    'choices' => [Message::TYPE_EMAIL, Message::TYPE_SMS, Message::TYPE_INTERNAL],
                    'expanded' => true,
                    'multiple' => true,
                    'choice_label' => function ($val) {
                        return strtolower($val);
                    },
                    'choice_attr' => function () {
                        return ['class' => 'inline'];
                    },
                    'label_attr' => ['class' => 'checkbox-inline'],
                    'label' => 'field.type',
                ])
                ->add('title', TextType::class, [
                    'label' => 'field.title'
                ])
                ->add('text', TextareaType::class, [
                    'label' => 'field.text',
                    'attr' => ['rows' => 10]
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'translation_domain' => 'communication',
            'parent' => null,
            'available_recipients' => [],
            'independent' => true,
        ]);
    }
}