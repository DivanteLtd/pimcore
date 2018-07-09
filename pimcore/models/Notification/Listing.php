<?php

declare(strict_types=1);

namespace Pimcore\Model\Notification;

use Pimcore\Model\Listing\AbstractListing;
use Pimcore\Model;

/**
 * Class Listing
 * @package Pimcore\Model\Notification
 * @method Listing\Dao getDao()
 */
class Listing extends AbstractListing
{
    /**
     * @var array
     */
    protected $notifications;

    /**
     * @param string $key
     * @return bool
     */
    public function isValidOrderKey($key)
    {
        return true;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->getDao()->count();
    }

    /**
     * @return Model\Notification[]
     */
    public function load(): array
    {
        return $this->getDao()->load();
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return Model\Notification[]
     */
    public function getItems(int $offset, int $limit): array
    {
        $this->setOffset($offset);
        $this->setLimit($limit);

        return $this->getDao()->load();
    }

    /**
     * @return Model\Notification[]
     */
    public function getNotifications(): array
    {
        return $this->notifications;
    }

    /**
     * @param Model\Notification[] $notifications
     */
    public function setNotifications(array $notifications): void
    {
        $this->notifications = $notifications;
    }
}
