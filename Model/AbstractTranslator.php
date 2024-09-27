<?php

namespace Cloneble\Translator\Model;

use Cloneble\Translator\Api\Data\TranslatorInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Psr\Log\LoggerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;


abstract class AbstractTranslator implements TranslatorInterface
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var UrlPersistInterface
     */
    protected UrlPersistInterface $urlPersist;

    /**
     * @var UrlFinderInterface
     */
    protected UrlFinderInterface $urlFinder;

    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @var Json
     */
    protected Json $json;

    /**
     * @param LoggerInterface $logger
     * @param UrlPersistInterface $urlPersist
     * @param UrlFinderInterface $urlFinder
     * @param CacheInterface $cache
     * @param Json $json
     */
    public function __construct
    (
        LoggerInterface $logger,
        UrlPersistInterface $urlPersist,
        UrlFinderInterface $urlFinder,
        CacheInterface $cache,
        Json $json
    ){
        $this->logger = $logger;
        $this->urlPersist = $urlPersist;
        $this->urlFinder = $urlFinder;
        $this->cache = $cache;
        $this->json = $json;
    }

    /**
     * @param string $productName
     * @return string
     * @throws \Random\RandomException
     */
    public function translateName(string $productName): string
    {
        return $productName . random_int(1, 2000);
    }

    /**
     * @param string $urlKey
     * @return string
     * @throws \Random\RandomException
     */
    public function translateUrlKey(string $urlKey): string
    {
        return $urlKey . random_int(1, 2000);
    }

    /**
     * @param QueueInterface $queue
     * @return void
     */
    public function translate(QueueInterface $queue)
    {
        $this->updateTranslateEntity($queue);
    }

    /**
     * @return int
     */
    protected function getSroreForTranslate()
    {
        return 3;
    }
}
