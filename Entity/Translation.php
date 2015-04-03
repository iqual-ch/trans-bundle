<?php
namespace TransBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="TranslationRepository")
 * @ORM\Table(
 *     name="trans_message_translations",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="id_locale_idx", 
 *             columns={"message_id", "locale"}
 *         )
 *     }
 * )
 */
class Translation
{
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=32) 
     * @var string 
     */
    protected $locale;
    
    /**
     * @ORM\Column(type="text", nullable=true) 
     * @var string 
     */
    protected $text;
    
    /** 
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="translations")
     */
    protected $message;
    
    public function __construct($locale = null, $text = null)
    {
        $this->locale = $locale;
        $this->text = $text;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getMessage()
    {
        return $this->message;
    }
    
    public function getText()
    {
        return $this->text;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
    
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function __toString()
    {
        return $this->message;
    }

}