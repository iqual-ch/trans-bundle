<?php
namespace TransBundle\Entity;

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
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection;
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

    public function getDomain()
    {
        return $this->domain;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    public function setTranslations($translations)
    {
        $this->translations = $translations;
        return $this;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }
    
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    public function hasTranslation($locale)
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return true;
            }
        }
        return false;
    }
    
    public function addTranslation(Translation $translation)
    {
        $translation->setMessage($this);
        $this->translations->add($translation);
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

}