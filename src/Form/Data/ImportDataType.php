<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 02/09/2017
 * Time: 18.58
 */

namespace App\Form\Data;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ImportDataType
 * @package App\Form\Data
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class ImportDataType extends AbstractType
{
    /**
     * @const DELIM_TAB Use the tabulator as a delimiter
     */
    const DELIM_TAB       = 0;

    /**
     * @const DELIM_COMMA Use a comma as a delimiter
     */
    const DELIM_COMMA     = 1;

    /**
     * @const DELIM_SEMICOLON Use a semi colon as a delimiter
     */
    const DELIM_SEMICOLON = 2;

    /**
     * @const DELIM_LABELS The delimiter labels, untranslated
     */
    const DELIM_LABELS = ['import.tab', 'import.comma', 'import.semicolon'];

    /**
     * @const DELIM_VALUES The delimiter values
     */
    const DELIM_VALUES = ["\t", ",", ";"];

    /**
     * @const MAPPING_LABELS Mapping labels for field types, untranslated
     */
    const MAPPING_LABELS = ['label.skip', 'label.false', 'label.true', 'label.current_timestamp', 'label.current_user', ];

    /** @var TranslatorInterface The translator */
    protected static $translator;

    /**
     * ImportDataType constructor.
     * @param TranslatorInterface|null $translator
     */
    public function __construct(TranslatorInterface $translator = null)
    {
        if ($translator != null)
            self::$translator = $translator;
    }

    public function trans(GetResponseEvent $evt)
    {
        //print_r("HOW");
        // print_r($evt->getRequest()->get('controller'));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['step'] == 1) {
            $builder
                ->add('entity', ChoiceType::class, [
                    'choices' => $options['entities'],
                    'expanded' => false,
                    'multiple' => false,
                    'choice_label' => function ($val) {
                        return $val;
                    },
                    'label' => 'import.entity',
                    'choice_translation_domain' => false,
                ])
                ->add('csv', TextareaType::class, [
                    'label' => 'import.csv',
                    'attr' => ['rows' => 10],
                ])
                ->add('delim', ChoiceType::class, [
                    'choices' => [0, 1, 2],
                    'choice_label' => function ($val) {
                        return self::DELIM_LABELS[$val];
                    },
                    'label' => 'import.delimeter',
                ])
                ->add('submit', SubmitType::class, ['left_icon' => null, 'right_icon' => 'fa-chevron-right', 'attr' => ['class' => 'btn-success'], 'label' => 'label.next'])
            ;
        } else if ($options['step'] == 2) {
            $tr = self::$translator;
            foreach ($options['vars'] as $var => $val) {
                $builder->add($var, ChoiceType::class, [
                    'choices' => [
                        $tr->trans('label.custom', [], 'import') => [-1, 0, 1, 2, 3],
                        $tr->trans('label.csv_fields', [], 'import') => $options['vals']],
                    'choice_label' => function ($val) use ($tr) {

                        return is_numeric($val) ? $tr->trans(self::MAPPING_LABELS[$val + 1], [], 'import') : $val;
                    },
                    'label' => $var,
                    'choice_translation_domain' => false,
                    'translation_domain' => false,
                ]);
                if (array_key_exists($var, $options['offsets'])) {
                    $builder->add($var . '_import_offset', IntegerType::class, [
                        'data' => $options['offsets'][$var],
                        'label' => $var . ' offset',
                        'translation_domain' => false,
                    ]);
                }
            }
            $builder
                ->add('submit', SubmitType::class, ['left_icon' => null, 'right_icon' => 'fa-chevron-right', 'attr' => ['class' => 'btn-success'], 'label' => 'label.next']);

        } else if ($options['step'] == 3) {
            $builder
                ->add('submit', SubmitType::class, ['left_icon' => 'fa-download', 'right_icon' => 'fa-check', 'attr' => ['class' => 'btn-success'], 'label' => 'label.import']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'import',
            'entities' => null,
            'csv' => null,
            'delim' => "\t",
            'step' => 1,
            'vars' => null,
            'vals' => [],
            'entity' => null,
            'offsets' => [],
        ]);
    }
}