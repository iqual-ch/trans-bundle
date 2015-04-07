<?php
namespace TransBundle\Entity;

class SearchHelper
{
    protected static $defaultCriterias = array(
        'query' => null, 
        'domains' => array(), 
        'locales' => array(), 
        'untranslated_only' => false,
        'search_in_translations' => true
    );
    
    protected static $defaultOptions = array(
        'per_page' => 25, 
        'current_page' => 1
    );
    
    /**
     * 
     * @param array $criterias
     * @return array
     */
    public static function prepareCriteria(array $criterias)
    {
        $values = array_replace_recursive(self::$defaultCriterias, $criterias);
        foreach (self::$defaultCriterias as $name => $defaultValue) {
            if (is_array($defaultValue)) {
                if (is_array($values[$name]) && empty(array_filter($values[$name]))) {
                    $values[$name] = null;
                }
            }
            
            if (is_bool($defaultValue)) {
                $values[$name] = (bool) $values[$name];
            }
        }
        
        return $values;
    }
    
    /**
     * 
     * @param array $options
     * @return array
     */
    public static function prepareOptions(array $options)
    {
        return array_replace_recursive(self::$defaultOptions, $options);
    }
    
    /**
     * 
     * @return array
     */
    public static function getDefaultCriterias()
    {
        return self::$defaultCriterias;
    }
    
}