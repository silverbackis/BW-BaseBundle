<?php

namespace BW\BaseBundle;

use Sonata\SeoBundle\Seo\SeoPage;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Asset\PackageInterface;

class BasePage extends SeoPage implements BWBaseInterface
{
    /**
     * @var SeoPage
     */
    protected $SonataSeoPage;

    /**
     * @var  string
     */
    protected $baseURL = null;

    /**
     * @var PackageInterface
     */
    protected $defaultPackage;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * Array of metas that can be auto generated
     */
    const AUTOMETAS = [
        'name' => [
            'twitter:title',
            'twitter:description'
        ],
        'property' => [
            'og:title',
            'og:description',
            'og:url'
        ]
    ];

    private $autometas = [];

    public function __construct(SeoPage $SonataSeoPage, RequestStack $requestStack, Packages $packages){
        $this->SonataSeoPage = $SonataSeoPage;
        /**
         * @var PackageInterface
         */
        $this->defaultPackage = $packages->getPackage();
        $this->requestStack = $requestStack;
    }
    /**
     * @var array
     */
    protected $links,
        $sdks;

    /**
     * {@inheritdoc}
     */
    public function setMetas(array $metadatas)
    {
        foreach( $metadatas as $mainAttr => &$valueArr )
        {
            foreach( $valueArr as $valueKey => &$valueAttrs )
            {
                if( isset($valueAttrs['{{ master_key }}']) && $valueAttrs['{{ master_key }}'] === '{{ key }}' )
                {
                    unset($valueAttrs['{{ master_key }}']);
                    $valueAttrs[$mainAttr] = $valueKey;
                }
                // e.g. property/name
                $type = $mainAttr;
                // e.g. og:image
                $name = $valueAttrs[$mainAttr];
                $content = $valueAttrs['content'];
                $extras = isset($valueAttrs['extras']) ? $valueAttrs['extras'] : [];
                $this->addMeta($type, $name, $content, $extras);
            }
        }
    }

    public function getMetas()
    {
        $this->processAutoContent();
        return parent::getMetas();
    }

    public function processAutoContent()
    {
        if(null === $this->metas)
        {
            return;
        }
        foreach( $this->metas as $type=>&$metaArr )
        {
            //ie($type.var_dump($metaArr));
            foreach($metaArr as &$metaInfo)
            {
                $name = $metaInfo[0];
                $content = $metaInfo[1];

                if( isset(self::AUTOMETAS[$type]) && in_array($name, self::AUTOMETAS[$type]) && ($content === 'auto' || in_array($type, $this->autometas)) )
                {
                    $this->autometas[] = $type;

                    $chk_title = ':title';
                    $chk_description = ':description';
                    $chk_url = ':url';
                    if( substr($name, strlen($chk_title)*-1) === $chk_title )
                    {
                        $content = $this->SonataSeoPage->getTitle();
                    }
                    elseif( substr($name, strlen($chk_description)*-1) === $chk_description )
                    {
                        $content = $this->getDescription();
                    }
                    elseif( substr($name, strlen($chk_url)*-1) === $chk_url )
                    {
                        $request = $this->requestStack->getCurrentRequest();
                        $content = $request ? $request->getUri() : 'http://localhost/';
                    }
                    $metaInfo = [$name, $content, []];
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addMeta($type, $name, $content, array $extras = array())
    {
        if (!isset($this->metas[$type])) {
            $this->metas[$type] = array();
        }
        if( isset(self::AUTOMETAS[$type]) && in_array($name, self::AUTOMETAS[$type]) && ($content === 'auto' || in_array($type, $this->autometas)) )
        {
            $this->autometas[] = $type;
            $chk_title = ':title';
            $chk_description = ':description';
            $chk_url = ':url';
            if( substr($name, strlen($chk_title)*-1) === $chk_title )
            {
                $content = $this->SonataSeoPage->getTitle();
            }
            elseif( substr($name, strlen($chk_description)*-1) === $chk_description )
            {
                $content = $this->getDescription();
            }
            elseif( substr($name, strlen($chk_url)*-1) === $chk_url )
            {
                $request = $this->requestStack->getCurrentRequest();
                $content = $request ? $request->getUri() : 'http://localhost/';
            }
        }

        $this->metas[$type][] = array($name, $content, $extras);
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return bool
     */
    public function hasMeta($type, $name)
    {
        $isset = false;
        foreach( $this->metas[$type] as $metaArr )
        {
            if( $metaArr[0] === $name )
            {
                $isset = true;
                break;
            }
        }
        return $isset;
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return bool
     */
    public function getMeta($type, $name)
    {
        foreach( $this->metas[$type] as $metaArr )
        {
            if( $metaArr[0] === $name )
            {
                return $metaArr;
            }
        }
        return false;
    }

    /**
     * @param string $type
     * @param string $name
     */
    public function removeMeta($type, $name)
    {
        foreach( $this->metas[$type] as $key=>$metaArr )
        {
            if( $metaArr[0] === $name )
            {
                unset($this->metas[$type][$key]);
            }
        }
    }

    public function replaceMeta($type, $name, $content, array $extras = array())
    {
        if($this->hasMeta($type, $name))
        {
            $this->removeMeta($type, $name);
        }
        $this->addMeta($type, $name, $content, $extras);
    }

    /**
     * @param string $type
     * @param integer $key
     */
    public function removeMetaByKey($type, $key)
    {
        unset($this->metas[$type][$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function setSDKs(array $sdks)
    {
        $this->sdks = $sdks;
    }

    /**
     * {@inheritdoc}
     */
    public function getSDKs(string $bodyPart=null)
    {
        return $this->sdks ? array_filter($this->sdks,function($arr) use ($bodyPart){
            $matchPart = $arr['head'] ? 'head' : 'body';
            return $arr['enabled'] && (null === $bodyPart || $bodyPart === $matchPart);
        }) : [];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasSDK($name)
    {
        return $this->sdks[$name]['enabled'];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function getSDK($name)
    {
        return $this->sdks[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function enableSDK($name)
    {
        if (!isset($this->sdks[$name])) {
            return;
        }

        $this->sdks[$name]['enabled'] = true;
    }

    /**
     * @param string $name
     */
    public function disableSDK($name)
    {
        $this->sdks[$name]['enabled'] = false;
    }

    /**
     * {@inheritdoc}
     */
    public function setLinks(array $linkdatas)
    {
        $this->links = array();

        foreach ($linkdatas as $type => $links) {
            if (!is_array($links)) {
                throw new \RuntimeException('$links must be an array');
            }
            foreach ($links as $name => $link) {
                $this->addLink($type, $name, $link);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return bool
     */
    public function hasLink($type, $name)
    {
        return isset($this->links[$type][$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function addLink($type, $name, $content)
    {
        if (!isset($this->links[$type])) {
            $this->links[$type] = array();
        }

        $this->links[$type][$name] = $content;
    }

    /**
     * @param string $type
     * @param string $name
     */
    public function removeLink($type, $name)
    {
        unset($this->links[$type][$name]);
    }

    /**
     * [getUrl description]
     * @param  string $url path to convert to absolute URL for tags
     * @return string      absolute url suitable for tags
     */
    public function getUrl(string $url)
    {
        // Check if we have a sheme already in the path - meaning it's a URL
        if( substr($url, 0, 2)!=='//' &&  parse_url($url, PHP_URL_SCHEME) === null)
        {
            // Add base URL path if not
            $this->setBaseUrl();
            $url = $this->baseURL.$url;
        }
        return $url;
    }

    /**
     * Sets $this->baseURL - either the specified base_url for assets or the current http scheme, host and port
     */
    private function setBaseUrl()
    {
        // Only set the base URL if not set already
        if(null === $this->baseURL)
        {
            // Check if base_url is already set for assets in the config - e.g. a CDN
            $this->baseURL = $this->defaultPackage->getUrl('');

            // Check if path is missing scheme and host
            if( $this->baseURL === '/' )
            {
                // These assets need to be referred to with absolute path
                // Get the current request
                $request = $this->requestStack->getCurrentRequest();

                // Set the baseURL to the current request's Scheme and Host
                $this->baseURL = $request->getSchemeAndHttpHost();
            }
            // Always finish with a slash
            $this->baseURL = rtrim($this->baseURL,"/")."/";
        }
    }

    private function getDescription()
    {
        if($this->getMeta('name', 'description'))
        {
            return $this->getMeta('name', 'description')[1];
        }
        elseif($this->SonataSeoPage->hasMeta('name', 'description'))
        {
            return $this->SonataSeoPage->metas['name']['description'][0];
        }
        else{
            return '';
        }
    }
}