<?php

namespace Cloneble\Translator\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class AfterImportProductsObserver implements ObserverInterface
{
    private LoggerInterface $logger;
    private PublisherInterface $publisher;
    private Json $json;
    private ProductRepositoryInterface $productRepository;

    public const TOPIC_PRODUCT_TRANSLATOR = 'cloneble.translator.product.queue';

    public function __construct(
        LoggerInterface $logger,
        PublisherInterface $publisher,
        Json $json,
        ProductRepositoryInterface $productRepository
    ) {
        $this->logger = $logger;
        $this->publisher = $publisher;
        $this->json = $json;
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer): void
    {
        $bunch = $observer->getEvent()->getBunch();
        $productIds = [];

        foreach ($bunch as $productData) {
            if (isset($productData['sku'])) {
                try {
                    $product = $this->productRepository->get($productData['sku']);
                    if ($product) {
                        $this->publisher->publish(self::TOPIC_PRODUCT_TRANSLATOR, $product->getSku());
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Error fetching product by SKU: ' . $productData['sku'], ['exception' => $e]);
                }
            }
        }

    }
}
