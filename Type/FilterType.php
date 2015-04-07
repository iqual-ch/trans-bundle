<?php
namespace TransBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterType extends AbstractType
{
    protected $domains = array();
    protected $locales = array();
    
    public function __construct($domains, $locales)
    {
        $this->domains = array_combine($domains, $domains);
        $this->locales = array_combine($locales, $locales);
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('query', 'text', array(
            'label' => false,
            'required' => false,
            'translation_domain' => 'TransBundle',
            'attr' => array(
                'placeholder' => 'input.placeholder.type_to_search',
                'class' => 'input-sm'
            ),
        ));
        
        $builder->add('domains', 'choice', array(
            'label' => false,
            'required' => false,
            'choices' => $this->domains,
            'multiple' => false,
            'expanded' => false,
            'placeholder' => 'select.option.all_domains',
            'translation_domain' => 'TransBundle',
            'attr' => array(
                'class' => 'form-control input-sm',
                'data-sonata-select2' => 'false'
            )
        ));
        
        $builder->add('search_in_translations', 'checkbox', array(
            'label' => 'label.search_in_translations',
            'required' => false,
            'data' => true
        ));
        
        $builder->add('untranslated_only', 'checkbox', array(
            'label' => 'label.untranslated_only',
            'required' => false,
        ));
        
        $builder->add('locales', 'choice', array(
            'label' => 'label.within_locales',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->locales
        ));
    }
    
    public function getName()
    {
        return '';
    }
}