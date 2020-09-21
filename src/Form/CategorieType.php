<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                "attr"=>[
                    "class"=>"form-control"
                ]
            ])
            ->add('description', null, [
                "attr"=>[
                    "class"=>"form-control tiny"
                ]
            ])
            ->add('image', null, [
                "attr"=>[
                    "class"=>"form-control"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
