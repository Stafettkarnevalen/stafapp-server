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

/**
 * Class ClientAnswerCollectionType
 * @package App\Form\FormBuilder
 */
class ClientAnswerCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var integer $index
         * @var FormSubmissionAnswer $answer
         */
        foreach ($options['items'] as $index => $answer) {
            $builder->add($index, ClientAnswerType::class, ['data' => $answer, 'label' => false]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'form',
            'items' => [],
        ]);
    }
}