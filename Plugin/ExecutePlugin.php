<?php

namespace Cloneble\Translator\Plugin;

use Cloneble\Translator\Observer\ProductAfterSaveObserver;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Framework\MessageQueue\PublisherInterface;

class ExecutePlugin
{
    /**
     * @var Attribute
     */
    protected Attribute $attributeHelper;

    /**
     * @var PublisherInterface
     */
    protected PublisherInterface $publisher;

    public function __construct
    (
        Attribute $attributeHelper,
        PublisherInterface $publisher,
    ){
        $this->attributeHelper = $attributeHelper;
        $this->publisher = $publisher;
    }

    /**
     * @param Save $subject
     * @param Redirect $result
     * @return Redirect
     */
    public function afterExecute(Save $subject, Redirect $result): Redirect
    {
        $productIds = $this->attributeHelper->getProductIds();
        foreach ($productIds as $productId) {
            $this->publisher->publish(ProductAfterSaveObserver::TOPIC_PRODUCT_TRANSLATOR, [['product_id' => $productId]]);
        }
        return $result;
    }
}
