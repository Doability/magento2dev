<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Magento\Product;

use Ess\M2ePro\Model\AbstractModel;

Class Image extends AbstractModel
{
    protected $storeManager;
    protected $mediaConfig;
    protected $filesystem;

    protected $url = null;
    protected $path = null;

    protected $hash = null;
    protected $storeId = 0;

    //########################################

    function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    )
    {
        $this->storeManager = $storeManager;
        $this->mediaConfig = $mediaConfig;
        $this->filesystem = $filesystem;

        parent::__construct($helperFactory, $modelFactory);
    }

    //########################################

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    //----------------------------------------

    /**
     * @return string
     */
    public function getPath()
    {
        if (is_null($this->path)) {
            $this->path = $this->getPathByUrl();
        }

        return $this->path;
    }

    /**
     * @param string|null $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    //----------------------------------------

    /**
     * @return string
     */
    public function getHash()
    {
        if ($this->hash) {
            return $this->hash;
        }

        return $this->hash = $this->generateHash($this->url, $this->getPath());
    }

    /**
     * @return $this
     */
    public function resetHash()
    {
        $this->hash = null;
        return $this;
    }

    private function generateHash($url, $path)
    {
        if ($this->isSelfHosted()) {
            return md5_file($path);
        }

        return md5($url);
    }

    //----------------------------------------

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    //########################################

    public function isSelfHosted()
    {
        return $this->getPath() && is_file($this->getPath());
    }

    //########################################

    public function getPathByUrl()
    {
        $imageUrl = str_replace('%20', ' ', $this->url);
        $imageUrl = preg_replace('/^http(s)?:\/\//i', '', $imageUrl);

        $baseMediaUrl = $this->storeManager->getStore($this->storeId)->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA, false
        );

        $baseMediaUrl = preg_replace('/^http(s)?:\/\//i', '', $baseMediaUrl);

        $baseMediaPath = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath();

        $imagePath = str_replace($baseMediaUrl, $baseMediaPath, $imageUrl);
        $imagePath = str_replace('/', DIRECTORY_SEPARATOR, $imagePath);
        $imagePath = str_replace('\\', DIRECTORY_SEPARATOR, $imagePath);

        return $imagePath;
    }

    public function getUrlByPath()
    {
        $baseMediaUrl = $this->storeManager->getStore($this->storeId)->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA, false
        );

        $baseMediaPath = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath();

        $imageLink = str_replace($baseMediaPath, $baseMediaUrl, $this->getPath());
        $imageLink = str_replace(DIRECTORY_SEPARATOR, '/', $imageLink);

        return str_replace(' ', '%20', $imageLink);
    }

    //########################################
}