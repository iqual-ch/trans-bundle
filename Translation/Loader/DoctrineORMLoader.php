<?php
namespace TransBundle\Translation\Loader;

use Doctrine\ORM\EntityManager as EntityManager;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DoctrineORMLoader implements LoaderInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    
    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * 
     * @param string $resource
     * @param string $locale
     * @param string $domain
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $results = array();
        $translations = $this->entityManager->getRepository('TransBundle:Translation')->getMessages($locale, $domain);
        /* @var $translation Translation */
        foreach ($translations as $translation) {
            $results[$translation->getMessage()->getMessage()] = $translation->getText();
        }
        return new MessageCatalogue($locale, array($domain => $results));
    }

}