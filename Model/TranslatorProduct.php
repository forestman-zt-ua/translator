<?php

namespace Cloneble\Translator\Model;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Cloneble\Translator\Model\AbstractTranslator;
use Magento\Framework\App\CacheInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Framework\Serialize\Serializer\Json;

class TranslatorProduct extends AbstractTranslator
{
    private ProductRepositoryInterface $productRepository;

    private ProductUrlRewriteGenerator $productUrlRewriteGenerator;

    private Action $action;

   public function __construct(
       LoggerInterface $logger,
       UrlPersistInterface $urlPersist,
       UrlFinderInterface $urlFinder,
       CacheInterface $cache,
       ProductRepositoryInterface $productRepository,
       ProductUrlRewriteGenerator $productUrlRewriteGenerator,
       Action $action,
       Json $json
   ){
       parent::__construct(
           $logger,
           $urlPersist,
           $urlFinder,
           $cache,
           $json);
         $this->productRepository = $productRepository;
         $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
         $this->action = $action;
   }

    /**
     * @param QueueInterface $queue
     * @return void
     */
    protected function updateTranslateEntity(QueueInterface $queue): void
    {
        while ($message = $queue->dequeue()) {
            try {
                $productSku = $this->json->unserialize($message->getBody(), true);
                $product = $this->productRepository->get($productSku, true, $this->getSroreForTranslate());
                $this->updateProductAttributes($product);
                $this->updateUrlRewrite($this->productRepository->get($productSku, false, $this->getSroreForTranslate(), true));
                $this->logger->info("Product SKU " . $productSku . " name updated successfully.");
            } catch (Exception $e) {
                $this->logger->error('Error updating product with SKU ' . $productSku . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * @param $product
     * @return void
     */
    private function updateProductAttributes($product): void
    {
        try {
            $updatedAttributes = [
                'name' => $this->translateName($product->getName()),
                'url_key' => $this->translateUrlKey($product->getUrlKey()),
                /*'exclude_clonable_autotranslation' => '1'*/
            ];
            $this->action->updateAttributes([$product->getId()], $updatedAttributes, $product->getStoreId());
        } catch (Exception $e) {
            $this->logger->error('Error updating product attributes: ' . $e->getMessage());
        }
    }

    /**
     * @param $product
     * @throws Exception
     */
    private function updateUrlRewrite($product): void
    {
        $urls = $this->productUrlRewriteGenerator->generate($product);
        try {
            $this->urlPersist->replace($urls);
        } catch (UrlAlreadyExistsException $e) {

        }
    }
}
