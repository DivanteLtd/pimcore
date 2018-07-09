<?php

declare(strict_types=1);

namespace Pimcore\Model;

use Pimcore\Cache;

/**
 * Class Notification
 * @package Pimcore\Model
 * @method Notification\Dao getDao()
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
    protected $title;

    /**
     * @var string
     */
    protected $message;

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
     * @param int $id
     * @return null|Notification
     */
    public static function getById(int $id): ?Notification
    {
        $cacheKey = sprintf('notification_%d', $id);

        try {
            $notification = Cache\Runtime::get($cacheKey);
            if (!$notification instanceof Notification) {
                throw new \Exception('Notification in registry is null');
            }
        } catch (\Exception $ex) {
            try {
                $notification = new self();
                Cache\Runtime::set($cacheKey, $notification);
                $notification->getDao()->getById($id);
            } catch (\Exception $ex) {
                return null;
            }
        }

        return $notification;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getCreationDate(): ?int
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate(int $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return int|null
     */
    public function getModificationDate(): ?int
    {
        return $this->modificationDate;
    }

    /**
     * @param int $modificationDate
     */
    public function setModificationDate(int $modificationDate): void
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return null|User
     */
    public function getSender(): ?User
    {
        return $this->sender;
    }

    /**
     * @param null|User $sender
     */
    public function setSender(?User $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return null|User
     */
    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    /**
     * @param null|User $recipient
     */
    public function setRecipient(?User $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return null|string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param null|string $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return null|Element\AbstractElement
     */
    public function getLinkedElement(): ?Element\AbstractElement
    {
        return $this->linkedElement;
    }

    /**
     * @param null|Element\AbstractElement $linkedElement
     */
    public function setLinkedElement(?Element\AbstractElement $linkedElement): void
    {
        $this->linkedElement     = $linkedElement;
        $this->linkedElementType = Element\Service::getElementType($linkedElement);
    }

    /**
     * @return null|string
     */
    public function getLinkedElementType(): ?string
    {
        return $this->linkedElementType;
    }

    /**
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read;
    }

    /**
     * @param bool $read
     */
    public function setRead(bool $read): void
    {
        $this->read = $read;
    }

    /**
     * Save notification
     */
    public function save(): void
    {
        $this->getDao()->save();
    }

    /**
     * Delete notification
     */
    public function delete(): void
    {
        $this->getDao()->delete();
    }
}
