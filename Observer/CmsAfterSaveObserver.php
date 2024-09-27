<?php

namespace Cloneble\Translator\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Observes the `cms_page_save_after` event.
 */
class CmsAfterSaveObserver implements ObserverInterface
{
    private PublisherInterface $publisher;

    public const TOPIC_PRODUCT_TRANSLATOR = 'cloneble.translator.cms.queue';
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
        $cmsPage = $observer->getObject();
        if (!$cmsPage->getExcludeClonableAutotranslation())
        {
            $message [] = ['cms_page_id' => $cmsPage->getId()];
            $this->publisher->publish(self::TOPIC_PRODUCT_TRANSLATOR, $message);
        }
    }
}
