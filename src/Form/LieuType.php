<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ajout de lieu
 *
 * Class LieuType
 * @package App\Form
 */
class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codePostal', null, ['label' => 'Code postal',
                'required' => true,
                'data' => '44'])
            ->add('ville', EntityType::class, [
                'label' => 'Ville',
                'required' => true,
                'class' => Ville::class,
                'choice_label' => 'nom',
                //permet de définir comment sont chargées les données depuis la bdd
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('v')
                        ->where('v.codePostal LIKE :cp')
                        ->setParameter('cp', '44%')
                        ->orderBy('v.nom', 'ASC');
                },
            ])
            ->add('rue', null, ['label' => 'Adresse',
                'required' => true])
            ->add('nom', null, ['label' => 'Nom du lieu',
                'required' => true])
            ->add('latitude', null, ['label' => 'latitude'])
            ->add('longitude', null, ['label' => 'longitude'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
