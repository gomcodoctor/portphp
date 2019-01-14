<?php

namespace Gomcodoctor\Portphp\ValueConverter;

use Doctrine\Common\Persistence\ObjectManager;
use Port\Doctrine\Exception\UnsupportedDatabaseTypeException;

/**
 * A bulk Doctrine writer
 *
 * See also the {@link http://www.doctrine-project.org/docs/orm/2.1/en/reference/batch-processing.html Doctrine documentation}
 * on batch processing.
 *
 * @author David de Boer <david@ddeboer.nl>
 * @editedBy gomcodoctor <info@freeopd.com>
 */
class ForeignKeyConverter
{
    /**
     * Doctrine object manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Fully qualified model name
     *
     * @var string
     */
    protected $objectName;

    /**
     * Doctrine object repository
     *
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * List of fields used to lookup an object
     *
     * @var array
     */
    protected $lookupFields = [];

    /**
     * Method used for looking up the item
     *
     * @var array
     */
    protected $lookupMethod;


    /** @var  array */
    protected $defaultFields =[];

    /** @var string|null */
    protected $csvFieldName;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param string        $objectName
     * @param string|array  $index         Field or fields to find current entities by
     * @param string        $lookupMethod  Method used for looking up the item
     */
    public function __construct(
        ObjectManager $objectManager,
        $objectName,
        $index = null, $csvFieldName,
        $lookupMethod = 'findOneBy'
    ) {
        $this->csvFieldName = $csvFieldName;
        $this->ensureSupportedObjectManager($objectManager);
        $this->objectManager = $objectManager;
        $this->objectRepository = $objectManager->getRepository($objectName);
        $this->objectMetadata = $objectManager->getClassMetadata($objectName);
        //translate objectName in case a namespace alias is used
        $this->objectName = $this->objectMetadata->getName();
        if ($index) {
            if (is_array($index)) {
                $this->lookupFields = $index;
            } else {
                $this->lookupFields = [$index];
            }
        }

        if (!method_exists($this->objectRepository, $lookupMethod)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Repository %s has no method %s',
                    get_class($this->objectRepository),
                    $lookupMethod
                )
            );
        }
        $this->lookupMethod = [$this->objectRepository, $lookupMethod];
    }

    /**
     * @param array $item
     *
     * @return object
     */
    protected function findItem(array $item)
    {
        $object = null;

        if (!empty($this->lookupFields)) {
            $lookupConditions = array();
            foreach ($this->lookupFields as $fieldName) {
                $lookupConditions[$fieldName] = $item[$fieldName];
            }

            $object = call_user_func($this->lookupMethod, $lookupConditions);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($input)
    {
        if (!is_array($input)) {
            throw new \InvalidArgumentException('Input of a ArrayValueConverterMap must be an array');
        }

        $input[$this->csvFieldName] = $this->findItem($input);

        return $input;
    }

    protected function ensureSupportedObjectManager(ObjectManager $objectManager)
    {
        if (!($objectManager instanceof \Doctrine\ORM\EntityManager
            || $objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager)
        ) {
            throw new UnsupportedDatabaseTypeException($objectManager);
        }
    }
}
