<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 25/11/2016
 * Time: 22.50
 */

namespace App\Form\PhoneNumber;

use App\Util\CSVReader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AreaCodeType extends AbstractType
{
    /**
     * Stores the available area code choices.
     *
     * @var array
     */
    private static $areaCodes;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => self::getAreaCodes(),
            'compound' => false,
            // 'force_error' => true,
            'error_bubbling' => true,
            'choice_translation_domain' => false,
            'attr' => ['class' => 'text-center']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'areacode';
    }

    /**
     * Returns the area code choices.
     *
     * @return array The area code choices
     */
    private static function getAreaCodes()
    {
        if (empty(self::$areaCodes)) {
            self::$areaCodes = CSVReader::countryCodes();
        }
        // $csv = new CSVReader("", ['firstRowIsKeys' => 1]);
        return self::$areaCodes;
    }


}