<?php

namespace Cloneble\Translator\Model\Queue\Consumer;

use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Cloneble\Translator\Model\TranslatorCategory;

class Category implements ConsumerInterface
{
    private TranslatorCategory $translatorCategory;
    private ConsumerConfigurationInterface $configuration;

    public function __construct
    (
        ConsumerConfigurationInterface $configuration,
        TranslatorCategory $translatorCategory,
    ){
        $this->configuration = $configuration;
        $this->translatorCategory = $translatorCategory;
    }

    public function process($maxNumberOfMessages = null)
    {
        $queue = $this->configuration->getQueue();
        $this->translatorCategory->translate($queue);
    }
}
