<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Key;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Factory which generates the api keys
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
     * @var string
     */
    private $apiKeyProperty;

    /**
     * @var int
     */
    private $keyLength;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     * @param string $modelName
     * @param string $apiKeyProperty
     * @param int $keyLength
     */
    public function __construct(ObjectManager $om, $modelName, $apiKeyProperty, $keyLength = 200)
    {
        $this->om = $om;
        $this->modelName = (string) $modelName;
        $this->apiKeyProperty = (string) $apiKeyProperty;
        $this->keyLength = (int) $keyLength;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        $key = $this->doGenerate();
        $repository = $this->om->getRepository($this->modelName);
        $max = 200;
        $count = 0;

        while (null !== $repository->findOneBy(array($this->apiKeyProperty => $key))) {
            ++$count;

            if ($count > $max) {
                throw new \RuntimeException('Unable to generate a new api key, stopping after 200 tries!');
            }

            $key = $this->doGenerate();
        }

        return $key;
    }

    /**
     * Generates the bare key
     *
     * @return string
     */
    private function doGenerate()
    {
        return bin2hex(openssl_random_pseudo_bytes($this->keyLength));
    }
}
