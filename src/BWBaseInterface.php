<?php

namespace BW\BaseBundle;

use Sonata\SeoBundle\Seo\SeoPageInterface;

interface BWBaseInterface extends SeoPageInterface
{
    /**
     * @param string $pagePart
     * @return array
     */
    public function getSDKs(string $pagePart = null);

    /**
     * @param array $sdks
     *
     * @return SeoPageInterface
     */
    public function setSDKs(array $sdks);

	/**
     * @return array
     */
    public function getLinks();

    /**
     * @param array $links
     *
     * @return SeoPageInterface
     */
    public function setLinks(array $links);

    /**
     * [getUrl description]
     * @param  string $url - path to convert to absolute URL for tags
     * @return string absolute url suitable for tags
     */
    public function getUrl(string $url);
}