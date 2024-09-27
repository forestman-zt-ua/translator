<?php

namespace Cloneble\Translator\Model\Queue\Consumer;

use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Cloneble\Translator\Model\TranslatorProduct;

class Product implements ConsumerInterface
{
    private TranslatorProduct $translatorProduct;
    private ConsumerConfigurationInterface $configuration;

    public function __construct
    (
        ConsumerConfigurationInterface $configuration,
        TranslatorProduct $translatorProduct,
    ){
        $this->configuration = $configuration;
        $this->translatorProduct = $translatorProduct;
    }

    /**
     * @param null $maxNumberOfMessages
     */
    public function process($maxNumberOfMessages = null): void
    {
        $queue = $this->configuration->getQueue();
        $this->translatorProduct->translate($queue);
    }
}
