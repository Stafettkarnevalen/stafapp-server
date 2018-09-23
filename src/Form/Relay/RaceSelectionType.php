<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 15/12/2016
 * Time: 8.02
 */

namespace App\Form\Relay;

use App\Entity\Relays\Race;
use App\Entity\Relays\Round;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaceSelectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Race $race */
        $race = $builder->getData();
        $active = ($builder->has('active') ? $builder->get('active')->getData() : $race->getId() !== null);
        $builder
            // ->add('active', HiddenType::class, ['data' => 0])
            ->add('active', CheckboxType::class, [
                'data' => $active,
                'label' => ($active ? 'label.active' : 'label.inactive'),
                'disabled' => $race->getHeats()->count() ? true : false,
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'toggle-active'],
                'label_attr' => [
                    'class' => ('btn btn-' . ($active ? 'success active' : 'default')) .
                        ($race->getHeats()->count() ? ' disabled' : ''),
                ]
            ]);
        // $builder->get('active')->setData(false);

        foreach (Round::ROUNDS as $round => $order) {
            $builder->add('rounds_' . $order, CheckboxType::class, [
                'data' =>  $race->hasRound($order),
                'label' => $round,
                'disabled' => ($race->getId() ? ($race->getHeats($order)->count() > 0) : true),
                'property_path' => 'rounds[$order]',
                //'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'toggle-on-race toggle-round'],
                'label_attr' => [
                    'class' => ('toggle-on-race-label btn btn-' . ($race->hasRound($order) ? 'success active' : 'default')) .
                        ($race->getId() ? ($race->getHeats($order)->count() ? ' disabled' : '') : ' disabled'),
                ]
            ]);
        }
            /*->add('active', CheckboxType::class, [
                'data' => $builder->getData()->getId() ? true : false,
                'mapped' => false,
                'label' => false,
                'attr' => [
                    'data-toggle' => 'toggle',
                    'data-on' => '<u>' . $builder->getData()->getName() . '</u>',
                    'data-off' => '<del>' . $builder->getData()->getName() . '</del>',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'data-width' => '100%',
                ],
                'required' => false,
                'disabled' => $builder->getData()->getHeats()->count() ? true : false,
            ])
            */
        $builder
            ->add('relay', HiddenType::class, ['property_path' => 'relay.id'])
            ->add('serviceType', HiddenType::class, ['property_path' => 'serviceType.id'])
            ->add('price', MoneyType::class, [
                'label' => 'label.price',
                'disabled' => $race->getId() ? false : true,
                'attr' => ['class' => $race->getId() ? 'toggle-on-race' : 'toggle-on-race disabled']
            ])
            ->add('submit', SubmitType::class, ['left_icon' => 'fa-save', 'right_icon' => 'fa-check', 'attr' => ['class' => 'btn-success'], 'label' => 'label.save'])

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Race::class,
            'translation_domain' => 'relay',
        ]);
    }
}