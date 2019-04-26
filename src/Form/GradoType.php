<?php

namespace App\Form;

use App\Entity\Grado;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class GradoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
        ;
        
        $builder->add('asignaturas', CollectionType::class, array(
            'entry_type' => AsignaturaType::class,
            'entry_options' => array('label' => false),
            'allow_add' => true,
            'by_reference' => false,
        ));
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Grado::class,
        ]);
    }
}
