<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Key;

use Doctrine\Common\Persistence\ObjectManager;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;

/**
 * Factory which generates the api keys.
 */
class KeyFactory implements KeyFactoryInterface
{
    const MAX_API_KEY_GENERATION_ATTEMPTS = 200;

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
        $this->keyLength = (int) floor($keyLength / 2);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException If no unique key can be created.
     */
    public function getKey()
    {
        $repository = $this->om->getRepository($this->modelName);
        $count = 0;

        do {
            ++$count;

            if ($count > static::MAX_API_KEY_GENERATION_ATTEMPTS) {
                throw new \RuntimeException(sprintf(
                    'Unable to generate a new api key, stopping after %d tries!',
                    static::MAX_API_KEY_GENERATION_ATTEMPTS
                ));
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
    protected function doGenerate()
    {
        return bin2hex(openssl_random_pseudo_bytes($this->keyLength));
    }
}
