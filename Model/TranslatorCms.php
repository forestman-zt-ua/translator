<?php

namespace Cloneble\Translator\Model;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Cloneble\Translator\Model\AbstractTranslator;
use Magento\Framework\App\CacheInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;


class TranslatorCms extends AbstractTranslator
{
    private PageRepositoryInterface $pageRepository;
    private CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator;

    public function __construct(
        LoggerInterface $logger
        , UrlPersistInterface $urlPersist,
        UrlFinderInterface $urlFinder,
        CacheInterface $cache,
        Json $json,
        PageRepositoryInterface $pageRepository,
        CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator
    ){
        parent::__construct(
            $logger,
            $urlPersist,
            $urlFinder,
            $cache,
            $json);
        $this->pageRepository = $pageRepository;
        $this->cmsPageUrlRewriteGenerator = $cmsPageUrlRewriteGenerator;
    }

    protected function updateTranslateEntity($queue)
    {
        while ($message = $queue->dequeue()) {
            try {
                $data = $this->json->unserialize($message->getBody(), true);
                $pageId = $data[0]['cms_page_id'];
                $page = $this->pageRepository->getById($pageId);
                $page->setTitle($this->translateName($page->getTitle()));
                $page->setIdentifier($this->translateUrlKey($page->getIdentifier()));
                $this->pageRepository->save($page);
                $this->cache->clean('cms_page_' . $page->getId());
                $this->updateUrlRewrite($page);
            } catch (\Exception $e) {
                $this->logger->error('Error updating CMS page ID ' . $pageId . ': ' . $e->getMessage());
            }
        }
    }

    private function updateUrlRewrite($page)
    {
        $urls = $this->cmsPageUrlRewriteGenerator->generate($page);
        try {
            $this->urlPersist->replace($urls);
        } catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
            $this->logger->error('URL already exists for page ID ' . $page->getId() . ': ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Error updating URL rewrite for page ID ' . $page->getId() . ': ' . $e->getMessage());
        }
    }

}
