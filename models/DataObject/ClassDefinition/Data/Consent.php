<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @category   Pimcore
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Model\DataObject\ClassDefinition\Data;

use Pimcore\DataObject\Consent\Service;
use Pimcore\Model;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;

class Consent extends Data implements ResourcePersistenceAwareInterface, QueryResourcePersistenceAwareInterface
{
    use Model\DataObject\Traits\SimpleComparisonTrait;
    use Extension\ColumnType;
    use Extension\QueryColumnType;

    /**
     * Static type of this element
     *
     * @var string
     */
    public $fieldtype = 'consent';

    /**
     * @var bool
     */
    public $defaultValue = 0;

    /**
     * Type for the column to query
     *
     * @var string
     */
    public $queryColumnType = 'tinyint(1)';

    /**
     * Type for the column
     *
     * @var string
     */
    public $columnType = [
        'consent' => 'tinyint(1)',
        'note' => 'int(11)'
    ];

    /**
     * Type for the generated phpdoc
     *
     * @var string
     */
    public $phpdocType = '\\Pimcore\\Model\\DataObject\\Data\\Consent';

    /**
     * Width of field
     *
     * @var string
     */
    public $width;

    /**
     * @see ResourcePersistenceAwareInterface::getDataForResource
     *
     * @param DataObject\Data\Consent $data
     * @param null|DataObject\AbstractObject $object
     * @param mixed $params
     *
     * @return array
     */
    public function getDataForResource($data, $object = null, $params = [])
    {
        if ($data instanceof DataObject\Data\Consent) {
            return [
                $this->getName() . '__consent' => $data->getConsent(),
                $this->getName() . '__note' => $data->getNoteId()
            ];
        }

        return [
            $this->getName() . '__consent' => false,
            $this->getName() . '__note' => null
        ];
    }

    /**
     * @see ResourcePersistenceAwareInterface::getDataFromResource
     *
     * @param array $data
     * @param null|Model\DataObject\AbstractObject $object
     * @param mixed $params
     *
     * @return DataObject\Data\Consent
     */
    public function getDataFromResource($data, $object = null, $params = [])
    {
        if (is_array($data) && $data[$this->getName() . '__consent'] !== null) {
            $consent = new DataObject\Data\Consent($data[$this->getName() . '__consent'], $data[$this->getName() . '__note']);
        } else {
            $consent = new DataObject\Data\Consent();
        }

        if (isset($params['owner'])) {
            $consent->setOwner($params['owner'], $params['fieldname'], $params['language']);
        }

        return $consent;
    }

    /**
     * @see QueryResourcePersistenceAwareInterface::getDataForQueryResource
     *
     * @param DataObject\Data\Consent $data
     * @param null|DataObject\AbstractObject $object
     * @param mixed $params
     *
     * @return bool
     */
    public function getDataForQueryResource($data, $object = null, $params = [])
    {
        if ($data instanceof DataObject\Data\Consent) {
            return $data->getConsent();
        }

        return false;
    }

    /**
     * @see Data::getDataForEditmode
     *
     * @param DataObject\Data\Consent $data
     * @param null|DataObject\AbstractObject $object
     * @param mixed $params
     *
     * @return bool
     */
    public function getDataForEditmode($data, $object = null, $params = [])
    {
        // get data & info from note
        if ($data instanceof DataObject\Data\Consent) {
            return [
                'consent' => $data->getConsent(),
                'noteContent' => $data->getSummaryString(),
                'noteId' => $data->getNoteId()
            ];
        } else {
            return null;
        }
    }

    /**
     * @see Data::getDataFromEditmode
     *
     * @param bool $data
     * @param null|DataObject\AbstractObject $object
     * @param mixed $params
     *
     * @return DataObject\Data\Consent
     */
    public function getDataFromEditmode($data, $object = null, $params = [])
    {
        if ($data === 'false') {
            $data = false;
        }

        /**
         * @var $oldData DataObject\Data\Consent
         */
        $oldData = null;
        $noteId = null;

        $getter = 'get' . ucfirst($this->getName());
        if (method_exists($object, $getter)) {
            $oldData = $object->$getter();
        }

        if (!$oldData || $oldData->getConsent() != $data) {
            $service = \Pimcore::getContainer()->get(Service::class);

            if ($data == true) {
                $note = $service->insertConsentNote($object, $this->getName(), 'Manually by User via Pimcore Backend.');
            } else {
                $note = $service->insertRevokeNote($object, $this->getName());
            }
            $noteId = $note->getId();
        }

        return new DataObject\Data\Consent($data, $noteId);
    }

    /** Converts the data sent from the object merger plugin back to the internal object. Similar to
     * getDiffDataForEditMode() an array of data elements is passed in containing the following attributes:
     *  - "field" => the name of (this) field
     *  - "key" => the key of the data element
     *  - "data" => the data
     *
     * @param $data
     * @param null $object
     * @param mixed $params
     *
     * @return mixed
     */
    public function getDiffDataFromEditmode($data, $object = null, $params = [])
    {
        $data = $data[0]['data'];

        $consent = false;
        if (isset($data['consent'])) {
            $consent = $data['consent'];
        }

        $service = \Pimcore::getContainer()->get(Service::class);

        $originalNote = null;
        if (!empty($data['noteId'])) {
            $originalNote = Model\Element\Note::getById($data['noteId']);
        }

        $noteId = null;
        if (!$originalNote || ($originalNote->getCtype() == 'object' && $originalNote->getCid() != $object->getId())) {
            if ($consent == true) {
                $note = $service->insertConsentNote($object, $this->getName(), $data['noteContent']);
            } else {
                $note = $service->insertRevokeNote($object, $this->getName());
            }

            if (!empty($originalNote)) {
                $note->setTitle($note->getTitle() . ' (objects merged - original consent date: ' . date('Y-m-d H:i:s', $originalNote->getDate()) .')');
                $note->save();

                $noteId = $note->getId();
            }
        } elseif ($originalNote) {
            $noteId = $originalNote->getId();
        }

        return new DataObject\Data\Consent($consent, $noteId);
    }

    /**
     * @param $data
     * @param null $object
     * @param array $params
     *
     * @return bool
     */
    public function getDataForGrid($data, $object = null, $params = [])
    {
        return $this->getDataForEditmode($data, $object, $params);
    }

    /**
     * @param $data
     * @param null $object
     * @param array $params
     *
     * @return DataObject\Data\Consent
     */
    public function getDataFromGridEditor($data, $object = null, $params = [])
    {
        return $this->getDataFromEditmode($data, $object, $params);
    }

    /**
     * @see Data::getVersionPreview
     *
     * @param DataObject\Data\Consent $data
     * @param null|DataObject\AbstractObject $object
     * @param mixed $params
     *
     * @return bool
     */
    public function getVersionPreview($data, $object = null, $params = [])
    {
        return $data ? $data->getConsent() : false;
    }

    /**
     * Checks if data is valid for current data field
     *
     * @param mixed $data
     * @param bool $omitMandatoryCheck
     *
     * @throws \Exception
     */
    public function checkValidity($data, $omitMandatoryCheck = false)
    {
        if ($omitMandatoryCheck || !$this->getMandatory()) {
            return;
        }

        if ($data === null || $data instanceof DataObject\Data\Consent && !$data->getConsent()) {
            throw new Model\Element\ValidationException('Empty mandatory field [ ' . $this->getName() . ' ]');
        }
        /* @todo seems to cause problems with old installations
        if(!is_bool($data) and $data !== 1 and $data !== 0){
        throw new \Exception(get_class($this).": invalid data");
        }*/
    }

    /**
     * converts object data to a simple string value or CSV Export
     *
     * @abstract
     *
     * @param DataObject\AbstractObject $object
     * @param array $params
     *
     * @return string
     */
    public function getForCsvExport($object, $params = [])
    {
        $data = $this->getDataFromObjectParam($object, $params);

        return $data ? strval($data->getConsent()) : '';
    }

    /**
     * fills object field data values from CSV Import String
     *
     * @abstract
     *
     * @param string $importValue
     * @param null|Model\DataObject\AbstractObject $object
     * @param mixed $params
     *
     * @return DataObject\ClassDefinition\Data
     */
    public function getFromCsvImport($importValue, $object = null, $params = [])
    {
        return new DataObject\Data\Consent((bool)$importValue);
    }

    /**
     * @param DataObject\AbstractObject $object
     * @param array $params
     *
     * @return bool
     */
    public function getForWebserviceExport($object, $params = [])
    {
        $data = $this->getDataFromObjectParam($object, $params);

        return $data ? (bool) $data->getConsent() : false;
    }

    /**
     * converts data to be imported via webservices
     *
     * @param mixed $value
     * @param null|Model\DataObject\AbstractObject $object
     * @param mixed $params
     * @param $idMapper
     *
     * @return mixed
     */
    public function getFromWebserviceImport($value, $object = null, $params = [], $idMapper = null)
    {
        return new DataObject\Data\Consent((bool)$value);
    }

    /** True if change is allowed in edit mode.
     * @param string $object
     * @param mixed $params
     *
     * @return bool
     */
    public function isDiffChangeAllowed($object, $params = [])
    {
        return true;
    }

    /**
     * @param DataObject\ClassDefinition\Data $masterDefinition
     */
    public function synchronizeWithMasterDefinition(DataObject\ClassDefinition\Data $masterDefinition)
    {
        $this->defaultValue = $masterDefinition->defaultValue;
    }

    /**
     * returns sql query statement to filter according to this data types value(s)
     *
     * @param  $value
     * @param  $operator
     * @param  $params
     *
     * @return string
     *
     */
    public function getFilterCondition($value, $operator, $params = [])
    {
        $params['name'] = $this->name;

        return $this->getFilterConditionExt(
            $value,
            $operator,
            $params
        );
    }

    /**
     * returns sql query statement to filter according to this data types value(s)
     *
     * @param $value
     * @param $operator
     * @param array $params optional params used to change the behavior
     *
     * @return string
     */
    public function getFilterConditionExt($value, $operator, $params = [])
    {
        $db = \Pimcore\Db::get();
        $name = $params['name'] ? $params['name'] : $this->name;
        $value = $db->quote($value);
        $key = $db->quoteIdentifier($this->name);

        $brickPrefix = $params['brickType'] ? $db->quoteIdentifier($params['brickType']) . '.' : '';

        return 'IFNULL(' . $brickPrefix . $key . ', 0) = ' . $value . ' ';
    }

    /**
     * @param $object
     * @param mixed $params
     *
     * @return string
     */
    public function getDataForSearchIndex($object, $params = [])
    {
        return '';
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return (int) $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @inheritdoc
     */
    public function supportsInheritance()
    {
        return false;
    }
}
