<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('description', TextareaType::class)
        ->add('prix', NumberType::class)
        ->add('superficie', NumberType::class)
        ->add('type', ChoiceType::class, [
            'choices' => [
                'Appartement' => 'appartement',
                'Maison' => 'maison',
                'Studio' => 'studio',
                'Chambre' => 'chambre',
            ],
        ])
        ->add('disponibilite', CheckboxType::class, [
            'label' => 'Disponible',
            'required' => false,
        ])
        ->add('meuble', CheckboxType::class, [
            'required' => false,
        ])
        ->add('adresse', TextType::class)
        ->add('ville', TextType::class)
        ->add('photos', CollectionType::class, [
            'entry_type' => FileType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'mapped' => false, // Ce champ n'est pas mappé directement à l'entité
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }

}
