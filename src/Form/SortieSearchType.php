<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\SchoolSite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de recherche et tri de sorties
 *
 * Ce formulaire n'est pas associée à une entité ! Voir la méthode configureOptions(), elle est vide.
 * Il faut récupérer les données à la mano dans le contrôleur
 *
 * Class SortieSearchType
 * @package App\Form
 */
class SortieSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //ce formulaire est en get
            ->setMethod('get')
            ->add('campus', EntityType::class, [
                'label' => 'Site',
                'class' => Campus::class,
                'choice_label' => 'nom',
                'required' => false,
            ])
            ->add('keyword', SearchType::class, [
                'label' => 'Mots-clefs',
                'required' => false,
            ])
            ->add('start_at_min_date', DateType::class, [
                'label' => 'Entre le',
                'required' => true,
                'html5' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'datepicker'],
                'format' => 'dd/MM/yyyy'
            ])
            ->add('start_at_max_date', DateType::class, [
                'label' => 'Et le',
                'required' => true,
                'html5' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'datepicker'],
                'format' => 'dd/MM/yyyy'
            ])
            ->add('is_organizer', CheckboxType::class, [
                'label' => "Sorties dont je suis l'organisateur",
                'required' => false,
            ])
            ->add('subscribed_to', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit',
                'required' => false,
            ])
            ->add('not_subscribed_to', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'GO'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //pas besoin de protection csrf ici
            'csrf_protection' => false
        ]);
    }
}
