<?php

namespace Cloneble\Translator\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Observes the `catalog_category_save_after` event.
 */
class CategoryAfterSaveObserver implements ObserverInterface
{
    private PublisherInterface $publisher;

    public const TOPIC_PRODUCT_TRANSLATOR = 'cloneble.translator.category.queue';
    public function __construct
    (
        PublisherInterface $publisher,
    ){
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
        $category = $observer->getEvent()->getCategory();
        if (!$category->getExcludeClonableAutotranslation())
        {
            $message [] = ['category_id' => $category->getId()];
            $this->publisher->publish(self::TOPIC_PRODUCT_TRANSLATOR, $message);
        }
    }
}
