<?php

namespace Pimcore\Model;

/**
 * Class Notification
 * @method Notification\Dao getDao()
 * @package Pimcore\Model
 */
class Notification extends AbstractModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $creationDate;

    /**
     * @var int
     */
    public $modificationDate;

    /**
     * @var User
     */
    public $sender;

    /**
     * @var User
     */
    public $recipient;

    /**
     * @var int
     */
    public $title;

    /**
     * @var int
     */
    public $message;

    /**
     * @var Element\AbstractElement
     */
    public $linkedElement;

    /**
     * @var string
     */
    public $linkedElementType;

    /**
     * @var bool
     */
    public $read;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return int
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param int $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return User
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param User $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return int
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param int $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param int $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return Element\AbstractElement
     */
    public function getLinkedElement()
    {
        return $this->linkedElement;
    }

    /**
     * @param Element\AbstractElement $linkedElement
     */
    public function setLinkedElement($linkedElement)
    {
        $this->linkedElement = $linkedElement;
    }

    /**
     * @return string
     */
    public function getLinkedElementType()
    {
        return $this->linkedElementType;
    }

    /**
     * @param string $linkedElementType
     */
    public function setLinkedElementType($linkedElementType)
    {
        $this->linkedElementType = $linkedElementType;
    }

    /**
     * @return bool
     */
    public function isRead()
    {
        return $this->read;
    }

    /**
     * @param bool $read
     */
    public function setRead($read)
    {
        $this->read = $read;
    }
}
