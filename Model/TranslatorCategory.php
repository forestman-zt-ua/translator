<?php

namespace Cloneble\Translator\Model;

use Cloneble\Translator\Model\AbstractTranslator;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\App\CacheInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class TranslatorCategory extends AbstractTranslator
{
    private CategoryRepositoryInterface $categoryRepository;
    private CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator;
    private StoreManagerInterface $storeManager;

    public function __construct(
        LoggerInterface $logger,
        UrlPersistInterface $urlPersist,
        UrlFinderInterface $urlFinder,
        CacheInterface $cache,
        Json $json,
        CategoryRepositoryInterface $categoryRepository,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        StoreManagerInterface $storeManager
    ){
        parent::__construct(
            $logger,
            $urlPersist,
            $urlFinder,
            $cache,
            $json);
        $this->categoryRepository = $categoryRepository;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->storeManager = $storeManager;
    }

    protected function updateTranslateEntity($queue)
    {
        while ($message = $queue->dequeue()) {
            try {
                $data = $this->json->unserialize($message->getBody(), true);
                $categoryId = $data[0]['category_id'];
                $this->storeManager->setCurrentStore($this->getSroreForTranslate());
                $category = $this->categoryRepository->get($categoryId, $this->getSroreForTranslate());
                $category->setName($this->translateName($category->getName()));
                $category->setUrlKey($this->translateUrlKey($category->getUrlKey()));
                $this->categoryRepository->save($category);
                $this->cache->clean('catalog_category_' . $category->getId());
                $this->updateUrlRewrite($category);
            } catch (\Exception $e) {
                $this->logger->error('Error updating category ID ' . $categoryId . ': ' . $e->getMessage());
            }
        }
    }

    private function updateUrlRewrite($category)
    {
        $urls = $this->categoryUrlRewriteGenerator->generate($category);
        try {
            $this->urlPersist->replace($urls);
        } catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
            $this->logger->error('URL already exists for category ID ' . $category->getId() . ': ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Error updating URL rewrite for category ID ' . $category->getId() . ': ' . $e->getMessage());
        }
    }

}
