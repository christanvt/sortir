<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, ['label' => 'Titre de la sortie'])
            ->add('infosSortie', null, ['label' => "Plus d'infos"])
            ->add('dateHeureDebut', null, [
                'label' => 'Débute le...',
                'html5' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'datetimepicker'],
                'format' => 'dd/MM/yyyy HH:mm'
            ])
            ->add('duree', IntegerType::class, ['label' => 'Durée, en heures'])
            ->add('dateLimiteInscription', null, [
                'label' => "Date limite d'inscription",
                'html5' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'datetimepicker'],
                'format' => 'dd/MM/yyyy HH:mm'
            ])
            ->add('nbInscriptionsMax', IntegerType::class, ['label' => 'Nombre max de participants'])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu',
                'class' => Lieu::class,
                'choice_label' => 'name',
            ])
            /*
            ->add('publierMaintenant', CheckboxType::class, [
                'label' => 'Rendre visible tout de suite',
                'mapped' => false,
                'data' => true,
                'required' => false,
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
