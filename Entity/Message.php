<?php
namespace TransBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="MessageRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *     name="trans_messages",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="message_domain_idx", 
 *             columns={"hash", "domain"}
 *         )
 *     }
 * )
 */
class Message
{
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=1024) 
     * @var string 
     */
    protected $message;
    
    /** 
     * @ORM\Column(type="string", length=200)
     */
    protected $domain;
 
    /** 
     * @var Translation[]
     * @ORM\OneToMany(targetEntity="Translation", mappedBy="message", cascade={"all"})
     * @ORM\JoinColumn(name="translation_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $translations;
    
    /**
     * @var string
     * @ORM\Column(type="string", length=32) 
     */
    protected $hash;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true) 
     */
    protected $filename;
    
    /**
     *
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true) 
     */
    protected $createdAt;
    
    /**
     * 
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection;
        $this->createdAt = new DateTime;
    }
    
    /**
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * 
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * 
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * 
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * 
     * @param int $id
     * @return Message
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * @param string $message
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * 
     * @param string $domain
     * @return Message
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * 
     * @param array $translations
     * @return Message
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
        return $this;
    }

    /**
     * 
     * @param string $filename
     * @return Message
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }
    
    /**
     * 
     * @param string $hash
     * @return Message
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * 
     * @param string $locale
     * @return boolean
     */
    public function hasTranslation($locale)
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param Translation $translation
     * @return Translation
     */
    public function addTranslation(Translation $translation)
    {
        $translation->setMessage($this);
        $this->translations->add($translation);
        return $this;
    }
    
    /**
     * 
     * @param string $locale
     * @return Translation
     */
    public function getTranslation($locale)
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }
    }
    
    /**
     * 
     * @param string $locale
     * @return string
     */
    public function getTranslationText($locale)
    {
        $translation = $this->getTranslation($locale);
        if (!$translation instanceof Translation) {
            return '';
        }
        return $translation->getText();
    }
    
    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->hash = md5($this->message);
    }
    
    /**
     * 
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * 
     * @param DateTime $createdAt
     * @return Message
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

}