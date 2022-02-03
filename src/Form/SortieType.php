<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, ['label' => 'Nom de la sortie :'])
            ->add('infosSortie', null, ['label' => "Description et infos :"])
            ->add('dateHeureDebut', null, [
                'label' => 'Date et heure de la sortie :',
                'html5' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'datetimepicker'],
                'format' => 'dd/MM/yyyy HH:mm'
            ])
            ->add('duree', IntegerType::class, ['label' => 'Durée, en minutes'])
            ->add('dateLimiteInscription', null, [
                'label' => "Date limite d'inscription :",
                'html5' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'datetimepicker'],
                'format' => 'dd/MM/yyyy HH:mm'
            ])
            ->add('nbInscriptionsMax', IntegerType::class, ['label' => 'Nombre de places :'])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu :',
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ])
            ->add('save', SubmitType::class, ['label'=> 'Enregistrer'])
            ->add('saveAndPublish', SubmitType::class, ['label'=> 'Publier la sortie'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
