<?php

declare(strict_types=1);

namespace Pimcore\Model\Notification;

use Pimcore\Model\Dao\AbstractDao;
use Pimcore\Model\Element;
use Pimcore\Model\Notification;
use Pimcore\Model\User;

/**
 * Class Dao
 * @package Pimcore\Model\Notification
 */
class Dao extends AbstractDao
{
    const DB_TABLE_NAME = 'notifications';

    /**
     * @param int $id
     * @throws \Exception
     */
    public function getById(int $id): void
    {
        $sql  = sprintf("SELECT * FROM `%s` WHERE 'id' = ?", static::DB_TABLE_NAME);
        $data = $this->db->fetchRow($sql, $id);

        if ($data === false) {
            $message = sprintf("Notification with id %d not found", $id);
            throw new \Exception($message);
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * Save notification
     */
    public function save()
    {
        $model = $this->getModel();

        $model->setModificationDate(time());
        if ($model->getId() === null) {
            $model->setCreationDate($model->getModificationDate());
        }

        $this->db->insertOrUpdate(static::DB_TABLE_NAME, $this->getData($model));

        if ($model->getId() === null) {
            $model->setId((int) $this->db->lastInsertId());
        }
    }

    /**
     * Delete notification
     */
    public function delete(): void
    {
        $this->db->delete(static::DB_TABLE_NAME, [
            'id' => $this->getModel()->getId(),
        ]);
    }

    /**
     * @param array $data
     */
    protected function assignVariablesToModel(array $data): void
    {
        $model = $this->getModel();

        $sender = null;
        if (is_int($data['sender'])) {
            $user = User::getById($data['sender']);
            if ($user instanceof User) {
                $sender = $user;
            }
        }

        $recipient = null;
        if (is_int($data['recipient'])) {
            $user = User::getById($data['recipient']);
            if ($user instanceof User) {
                $recipient = $user;
            }
        }

        $linkedElement = null;
        if (is_int($data['linkedElement'])) {
            $linkedElement = Element\Service::getElementById($data['linkedElementType'], $data['linkedElement']);
        }

        $model->setId($data['id']);
        $model->setCreationDate($data['creationDate']);
        $model->setModificationDate($data['modificationDate']);
        $model->setSender($sender);
        $model->setRecipient($recipient);
        $model->setTitle($data['title']);
        $model->setMessage($data['message']);
        $model->setLinkedElement($linkedElement);
        $model->setRead($data['read'] === 1 ? true : false);
    }

    /**
     * @param Notification $model
     * @return array
     */
    protected function getData(Notification $model): array
    {
        return [
            'id'                => $model->getId(),
            'creationDate'      => $model->getCreationDate(),
            'modificationDate'  => $model->getModificationDate(),
            'sender'            => $model->getSender() ? $model->getSender()->getId() : null,
            'recipient'         => $model->getRecipient() ? $model->getRecipient()->getId() : null,
            'title'             => $model->getTitle(),
            'message'           => $model->getMessage(),
            'linkedElement'     => $model->getLinkedElement() ? $model->getLinkedElement()->getId() : null,
            'linkedElementType' => $model->getLinkedElementType(),
            'read'              => $model->isRead() ? 1 : 0,
        ];
    }

    /**
     * @return Notification
     */
    protected function getModel(): Notification
    {
        return $this->model;
    }
}
