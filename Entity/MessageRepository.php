<?php

namespace TransBundle\Entity;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Generator;

class MessageRepository extends EntityRepository
{

    public function search(array $criterias = array(), array $options = array())
    {
        $criterias = SearchHelper::prepareCriteria($criterias);
        $options   = SearchHelper::prepareOptions($options);
        
        $qb = $this->createQueryBuilder('m');
        $qb->leftJoin('m.translations', 't');
        
        if ($criterias['query']) {
            if (substr($criterias['query'], 0, 1) == '%' || substr($criterias['query'], -1, 1) == '%') {
                $qb->where($qb->expr()->like('m.message', $qb->expr()->literal($criterias['query'])));

                if ($criterias['search_in_translations']) {
                    $qb->orWhere($qb->expr()->like('t.text', $qb->expr()->literal($criterias['query'])));
                }
            } else {
                $qb->where('m.message = :query');
                if ($criterias['search_in_translations']) {
                    $qb->orWhere('t.text = :query');
                }
                $qb->setParameter('query', $criterias['query']);
            }
        }
        
        if (count($criterias['domains'])) { 
            $qb->andWhere($qb->expr()->in('m.domain', (array) $criterias['domains']));
        }
        
        // look for untranslated messages within these locales
        if (count($criterias['locales']) > 0) {
            $qb->andWhere($qb->expr()->in('t.locale', $criterias['locales']));
            if ($criterias['untranslated_only']) {
                $qb->andWhere($qb->expr()->eq('t.text', $qb->expr()->literal('')));
            }
        }
        
        
        $qb->orderBy('m.message', 'ASC');
        
        $qb->setMaxResults($options['per_page']);
        $qb->setFirstResult($options['per_page'] * $options['current_page']);
        
//        die($qb);
        
        $paginator = new Paginator($qb->getQuery());
        return $paginator;
    }
    
    /**
     * 
     * @return Generator
     */
    public function getAvailableDomainsGenerator()
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select('m.domain');
        $qb->distinct('m.domain');
                
        $result = $qb->getQuery()->getResult(Query::HYDRATE_SCALAR);
        foreach ($result as $domain) {
            yield $domain['domain'];
        }
    }
    
    /**
     * 
     * @return array
     */
    public function getDomains()
    {
        $domains = array();
        foreach ($this->getAvailableDomainsGenerator() as $domain) {
            $domains[] = $domain;
        }
        return $domains;
    }
    
    /**
     * 
     * @return int
     */
    public function getTotal()
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select('COUNT(m.id) AS total');
        return $qb->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * 
     * @return Statement
     */
    public function clearGarbage()
    {
        $sql = sprintf('DELETE FROM %s WHERE message NOT RLIKE "[a-zA-Z]"', $this->_class->getTableName());
        return $this->_em->getConnection()->executeQuery($sql);
    }
}
