<?php
namespace TransBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class TranslationRepository extends EntityRepository
{
    public function getMessages($locale, $domain)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->join('t.message', 'm');
        $qb->where('m.domain = :domain');
        $qb->andWhere('t.locale = :locale');
        $qb->setParameters(array(
            'locale' => $locale,
            'domain' => $domain
        ));
        return $qb->getQuery()->setHydrationMode(Query::HYDRATE_ARRAY)->getResult();
    }
    
    public function updateTranslation($messageId, $locale, $text)
    {
        // ultra native query for performance reasons
        $connection = $this->_em->getConnection();
        $stmt = $connection->prepare('REPLACE INTO ' . $this->_class->getTableName() . ' (message_id, locale, text) VALUES (:message, :locale, :text)');
        $stmt->execute(array(
            'message' => $messageId,
            'locale' => $locale,
            'text' => $text
        ));
    }
}