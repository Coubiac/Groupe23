<?php

namespace AppBundle\Form\Type;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ObservationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateObservation', DateType::class, array(
                'widget' => 'single_text',
                'html5' => true,
                'attr' => array('class' => 'datepicker', 'max' => date('Y-m-d')),
                'format' => 'yyyy-MM-dd',
            ))
            ->add('comment', TextType::class, array('required' => false))
            ->add('imageFile', FileType::class, array('required' => false,
                'label' => 'Photo :',
                'attr'=>array(
                    'class'=>'custom-file')
            ))
            ->add('longitude', NumberType::class, array(
                 'invalid_message' => 'Ce champ est invalide.',
                 "invalid_message_parameters" => array(
        "{{ type }}" => "float"
            )))
            ->add('latitude', NumberType::class, array(
                'invalid_message' => 'Ce champ est invalide.',
                "invalid_message_parameters" => array(
                    "{{ type }}" => "float"
                )))
            ->add('taxref', EntityType::class, array(
                'class' => 'AppBundle\Entity\Taxref',
                'placeholder' => 'Choisissez une espèce',
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Observation',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_observation';
    }


}
