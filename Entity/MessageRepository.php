<?php

namespace TransBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MessageRepository extends EntityRepository
{

    /**
     * 
     * @param string $q
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function search($q = null, $limit = 25, $page = 0)
    {
        $qb = $this->createQueryBuilder('m');
        if ($q) {
            $qb->where($qb->expr()->like('m.message', $qb->expr()->literal('%' . $q . '%')));
            $qb->leftJoin('m.translations', 't');
            $qb->orWhere($qb->expr()->like('t.text', $qb->expr()->literal('%' . $q . '%')));
        }
        
        $qb->orderBy('m.message', 'ASC');
        $qb->setFirstResult($page * $limit);
        $qb->setMaxResults($limit);
        
        $paginator = new Paginator($qb->getQuery());
        return $paginator;
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

}
