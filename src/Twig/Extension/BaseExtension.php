<?php

namespace BW\BaseBundle\Twig\Extension;

use Sonata\SeoBundle\Twig\Extension\SeoExtension;
use BW\BaseBundle\BWBaseInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;

class BaseExtension extends SeoExtension
{
    /**
     * @var SeoPageInterface
     */
    protected $page;

    /**
     * @var BWBaseInterface
     */
    protected $BWBase;

    /**
     * @param BWBaseInterface $page
     * @param string $encoding
     * @param BWBaseInterface $BWBase
    */
  public function __construct(SeoPageInterface $page, string $encoding, BWBaseInterface $BWBase){
        $this->page = $page;
        $this->encoding = $encoding;
        $this->BWBase = $BWBase;
  }

  public function getFunctions()
    {
        $parentFunctions = parent::getFunctions();
        return array_merge($parentFunctions,array(
            new \Twig_SimpleFunction('bwbase_links', array($this, 'getLinks'), [
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('bwbase_link_tags', array($this, 'getLinkTags'), [
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('bwbase_sdks', array($this, 'getSDKs'), [
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('bwbase_sdks_html', array($this, 'getSDKHtml'), [
                'is_safe'           => ['html'],
                'needs_environment' => true
            ]),
            new \Twig_SimpleFunction('bwbase_meta_tags', array($this, 'getMetaTags'), [
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('bwbase_metas', array($this, 'getMetas'), [
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('bwbase_title', array($this, 'getPlainTitle'), [
                'is_safe' => ['html']
            ])
        ));
    }

    public function getName()
    {
        return 'bw.base.twig';
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return $this->BWBase->getLinks();
    }

    /**
     * @return array
     */
    public function getSDKs(string $pagePart = null)
    {
        return $this->BWBase->getSDKs($pagePart);
    }

    /**
     * @return array
     */
    public function getMetas()
    {
        $BWBaseMetas = $this->BWBase->getMetas();
        $sonataSeoMetas = $this->page->getMetas();
        if($sonataSeoMetas) {
            foreach ($sonataSeoMetas as $type => $metas) {
                if (!isset($BWBaseMetas[$type])) {
                    $BWBaseMetas[$type] = [];
                }
                foreach ((array)$metas as $name => $meta) {
                    $BWBaseMetas[$type][] = array($name, $meta[0], $meta[1]);
                }
            }
        }
        return $BWBaseMetas;
    }

    /**
     * @return string (HTML tags)
     */
    public function getLinkTags()
    {
        $html = '';
        if($this->getLinks()) {
            foreach ($this->getLinks() as $type => $links) {
                foreach ((array)$links as $name => $linksSet) {
                    //list($content, $extras) = $link;
                    foreach ($linksSet as $link) {
                        $sprintfArr = array(
                            $this->normalize($type),
                            $this->normalize($name)
                        );
                        $sprintfArgs = str_repeat(" %s=\"%s\"", sizeof($link));
                        foreach ($link as $attr => $val) {
                            $sprintfArr[] = $this->normalize($attr);
                            if ($attr === 'href') {
                                $val = $this->BWBase->getUrl($val);
                            }
                            $sprintfArr[] = $this->normalize($val);
                        }

                        $html .= '<link '.vsprintf('%s="%s" '.$sprintfArgs, $sprintfArr).' />';
                    }

                }
            }
        }
        return $html;
    }

    /**
     * @return string (HTML)
     */
    public function getSDKHtml(\Twig_Environment $environment, string $pageSection)
    {
        $html = "";
        foreach ($this->getSDKs($pageSection) as $sdkName => $sdkInfo) {
            switch($sdkName){
                case "twitter":
                    $html .= $environment->render('@BWBase/Block/_twitter_sdk.html.twig', []);
                break;

                case "facebook":
                    $js_script = $sdkInfo['debug'] ? 'debug.js' : 'sdk.js';
                    $html .= $environment->render('@BWBase/Block/_facebook_sdk.html.twig',[
                        'app_id' =>  $sdkInfo['app_id'],
                        'xfbml' => $sdkInfo['xfbml'],
                        'version' => $sdkInfo['version'],
                        'status' => $sdkInfo['login_status_check'] ? "true" : "false",
                        'language' => $sdkInfo['language'],
                        'js_script' => $js_script
                    ]);
                break;

                case "google_analytics":
                    $html .= $environment->render('@BWBase/Block/_google_analytics_sdk.html.twig', [
                        'id' => $sdkInfo['id'],
                        'domain' => $sdkInfo['domain']
                    ]);
                break;

                case "woopra":
                    $html .= $environment->render('@BWBase/Block/_woopra_sdk.html.twig', [
                        'domain' => $sdkInfo['domain']
                    ]);
                break;
                
                case "facebook_pixel":
                    $html .= $environment->render('@BWBase/Block/_facebook_pixel_sdk.html.twig', [
                        'id' => $sdkInfo['id']
                    ]);
                break;

                default:
                    $html .= '<script>console.warn("'.$sdkName.' has no code configured for the twig extension `BaseExtension` using getSDKs");</script>';
                break;
            }
        }
        return $html;
    }

    /**
     * {@inheriteddoc}
     * @return string (HTML tags)
     */
    public function getMetaTags()
    {
        $assetPostfixes = array(":image", "TileImage", "logo", ":audio", ":secure_url");
        $allMetas = $this->getMetas();
        $html = '';
        foreach ($allMetas as $type => $metas) {
            foreach ((array) $metas as $key => $meta) {
                // Support Sonata SEO which has 2 items in array - only 1 twig function to call then for all meta tags
                if( sizeof($meta) === 2 )
                {
                    $name = $key;
                    list($content) = $meta;
                }
                else
                {
                    list($name, $content) = $meta;
                }

                foreach($assetPostfixes as $assetPostfix){
                    if( substr($name, strlen($assetPostfix)*-1) === $assetPostfix ){
                        $content = $this->BWBase->getUrl($content);
                        break;
                    }
                }

                if (!empty($content)) {
                    $html .= '<meta '.sprintf('%s="%s" content="%s"',
                        $type,
                        $this->normalize($name),
                        $this->normalize($content)
                    ).' />';
                } else {
                    $html .= '<meta '.sprintf('%s="%s"',
                        $type,
                        $this->normalize($name)
                    ).' />';
                }
            }
        }
        return $html;
    }

    /**
     * Function used to normalise strings inserted into HTML tags
     * @param string $string
     *
     * @return mixed
     */
    private function normalize($string)
    {
        return htmlentities(strip_tags($string), ENT_QUOTES, $this->encoding);
    }

    /**
     * @return string
     */
    public function getPlainTitle()
    {
        return sprintf('%s', strip_tags($this->page->getTitle()));
    }
}