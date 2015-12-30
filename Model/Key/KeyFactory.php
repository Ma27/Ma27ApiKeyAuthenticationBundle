<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Key;

use Doctrine\Common\Persistence\ObjectManager;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;

/**
 * Factory which generates the api keys.
 */
class KeyFactory implements KeyFactoryInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $modelName;

    /**
     * @var ClassMetadata
     */
    private $metadata;

    /**
     * @var int
     */
    private $keyLength;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     * @param string        $modelName
     * @param ClassMetadata $metadata
     * @param int           $keyLength
     */
    public function __construct(ObjectManager $om, $modelName, ClassMetadata $metadata, $keyLength = 200)
    {
        $this->om = $om;
        $this->modelName = (string) $modelName;
        $this->metadata = $metadata;
        $this->keyLength = (int) $keyLength;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        $repository = $this->om->getRepository($this->modelName);
        $max = 200;
        $count = 0;

        do {
            ++$count;

            if ($count > $max) {
                throw new \RuntimeException('Unable to generate a new api key, stopping after 200 tries!');
            }

            $key = $this->doGenerate();
        } while (null !== $repository->findOneBy(array($this->metadata->getPropertyName(ClassMetadata::API_KEY_PROPERTY) => $key)));

        return $key;
    }

    /**
     * Getter for the object manager.
     *
     * @return ObjectManager
     */
    protected function getOm()
    {
        return $this->om;
    }

    /**
     * Getter for the model name.
     *
     * @return string
     */
    protected function getModelName()
    {
        return $this->modelName;
    }

    /**
     * Getter for the api key property.
     *
     * @return ClassMetadata
     */
    protected function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Getter for the key length.
     *
     * @return int
     */
    protected function getKeyLength()
    {
        return $this->keyLength;
    }

    /**
     * Generates the bare key.
     *
     * @return string
     */
    private function doGenerate()
    {
        return bin2hex(openssl_random_pseudo_bytes($this->keyLength));
    }
}
