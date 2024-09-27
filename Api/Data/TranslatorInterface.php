<?php

namespace Cloneble\Translator\Api\Data;

use Magento\Framework\MessageQueue\QueueInterface;

interface TranslatorInterface
{
    public function translate(QueueInterface $queue);
}
