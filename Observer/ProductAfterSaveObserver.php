<?php

namespace Cloneble\Translator\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Observes the `catalog_product_save_after` event.
 */
class ProductAfterSaveObserver implements ObserverInterface
{
    private PublisherInterface $publisher;

    public const TOPIC_PRODUCT_TRANSLATOR = 'cloneble.translator.product.queue';

    public function __construct
    (
        PublisherInterface $publisher,
    )
    {
        $this->publisher = $publisher;
    }

    /**
     * Observer for catalog_product_save_after.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product->getExcludeClonableAutotranslation()) {
            $this->publisher->publish(self::TOPIC_PRODUCT_TRANSLATOR, $product->getSku());
        }
    }
}
