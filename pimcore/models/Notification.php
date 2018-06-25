<?php

declare(strict_types=1);

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
    protected $id;

    /**
     * @var int
     */
    protected $creationDate;

    /**
     * @var int
     */
    protected $modificationDate;

    /**
     * @var User
     */
    protected $sender;

    /**
     * @var User
     */
    protected $recipient;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var Element\AbstractElement
     */
    protected $linkedElement;

    /**
     * @var string
     */
    protected $linkedElementType;

    /**
     * @var bool
     */
    protected $read = false;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate(int $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return int|null
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param int $modificationDate
     */
    public function setModificationDate(int $modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return User|null
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User|null $sender
     */
    public function setSender(User $sender = null)
    {
        $this->sender = $sender;
    }

    /**
     * @return User|null
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param User|null $recipient
     */
    public function setRecipient(User $recipient = null)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return Element\AbstractElement|null
     */
    public function getLinkedElement()
    {
        return $this->linkedElement;
    }

    /**
     * @param Element\AbstractElement|null $linkedElement
     */
    public function setLinkedElement(Element\AbstractElement $linkedElement = null)
    {
        $this->linkedElement     = $linkedElement;
        $this->linkedElementType = Element\Service::getElementType($linkedElement);
    }

    /**
     * @return string|null
     */
    public function getLinkedElementType()
    {
        return $this->linkedElementType;
    }

    /**
     * @return bool
     */
    public function isRead() : bool
    {
        return $this->read;
    }

    /**
     * @param bool $read
     */
    public function setRead(bool $read)
    {
        $this->read = $read;
    }

    /**
     * Save notification
     */
    public function save()
    {
        $this->getDao()->save();
    }
}
