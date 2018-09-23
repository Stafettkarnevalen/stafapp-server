<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 11/09/2017
 * Time: 9.05
 */

namespace App\Form\FormBuilder;

use App\Entity\Forms\FormSubmissionAnswer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientAnswerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var FormSubmissionAnswer $answer */
        $answer = $builder->getData();
        $field = $answer->getFormField();
        $builder->add('answer', $field->getFormType(), $field->getFormOptions($answer));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Communication\FormSubmissionAnswer',
            'translation_domain' => 'form',
        ]);
    }
}