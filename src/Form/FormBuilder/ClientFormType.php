<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 11/09/2017
 * Time: 9.05
 */

namespace App\Form\FormBuilder;

use App\Entity\Forms\FormSubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var FormSubmission $formSubmission */
        $formSubmission = $builder->getData();
        $answers = $formSubmission->getAnswers();
        // $builder->add('form', HiddenType::class);
        $builder->add('answers', ClientAnswerCollectionType::class, [
            'items' => $answers,
            'label' => false,
            ]);
        $builder->add('submit', SubmitType::class, ['left_icon' => 'fa-save', 'right_icon' => 'fa-check', 'attr' => ['class' => 'btn-success'], 'label' => 'label.submit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Communication\FormSubmission',
            'translation_domain' => 'form',
        ]);
    }
}