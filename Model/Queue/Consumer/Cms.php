<?php

namespace Cloneble\Translator\Model\Queue\Consumer;

use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Cloneble\Translator\Model\TranslatorCms;

class Cms implements ConsumerInterface
{
    private TranslatorCms $translatorCms;
    private ConsumerConfigurationInterface $configuration;

    public function __construct
    (
        ConsumerConfigurationInterface $configuration,
        TranslatorCms $translatorCms,
    ){
        $this->configuration = $configuration;
        $this->translatorCms = $translatorCms;
    }

    public function process($maxNumberOfMessages = null)
    {
        $queue = $this->configuration->getQueue();
        $this->translatorCms->translate($queue);
    }
}

