<?php

namespace TransBundle\Translation;

use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface;
use TransBundle\Entity\Message;

class LoggingTranslator implements TranslatorInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param Translator      $translator
     * @param LoggerInterface $logger
     */
    public function __construct($translator, LoggerInterface $logger, EntityManager $em)
    {
        if (!($translator instanceof TranslatorInterface && $translator instanceof TranslatorBagInterface)) {
            throw new InvalidArgumentException(sprintf('The Translator "%s" must implements TranslatorInterface and TranslatorBagInterface.', get_class($translator)));
        }

        $this->translator = $translator;
        $this->logger = $logger;
        $this->entityManager = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $trans = $this->translator->trans($id, $parameters, $domain, $locale);
        $this->log($id, $domain, $locale);

        return $trans;
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        $trans = $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
        $this->log($id, $domain, $locale);

        return $trans;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getLocale()
    {
        return $this->translator->getLocale();
    }

    /**
     * Passes through all unknown calls onto the translator object.
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->translator, $method), $args);
    }

    /**
     * Logs for missing translations.
     *
     * @param string      $id
     * @param string|null $domain
     * @param string|null $locale
     */
    private function log($id, $domain, $locale)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if (null === $domain) {
            $domain = 'messages';
        }

        $id = (string) $id;
        $catalogue = $this->translator->getCatalogue($locale);
        if ($catalogue->defines($id, $domain)) {
            return;
        }

        if ($catalogue->has($id, $domain)) {
            $this->logger->debug('Translation use fallback catalogue.', array('id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()));
        } else {
            $this->logger->warning('Translation not found.', array('id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()));
            $this->addTranslation($id, $domain);
        }
    }
    
    /**
     * Adds untranslated message to database.
     * 
     * @param string $id Message ID
     * @param string $domain
     * @return void
     */
    private function addTranslation($id, $domain)
    {
        if (!$this->isValidString($id)) {
            return;
        }
        
        $message = $this->entityManager->getRepository('TransBundle:Message')->findOneBy(array(
            'message' => $id,
            'domain' => $domain
        ));
        if ($message) {
            return;
        }
        $entity = new Message;
        $entity->setDomain($domain);
        $entity->setMessage($id);
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }
    
    /**
     * Checks if string contains a word.
     * 
     * @param string $message
     * @return bool
     */
    private function isValidString($message)
    {
        return preg_match('/[a-zA-Z]/', $message);
    }
}
