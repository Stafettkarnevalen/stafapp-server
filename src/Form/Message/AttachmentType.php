<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/09/2017
 * Time: 20.34
 */

namespace App\Form\Message;


use App\Entity\Communication\MessageAttachment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class AttachmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control'],
                'translation_domain' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessageAttachment::class,
            'translation_domain' => 'communication',
        ]);
    }
}