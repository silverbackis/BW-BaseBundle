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
     * BaseExtension constructor.
     * @param SeoPageInterface $page
     * @param string $encoding
     * @param BWBaseInterface $BWBase
     */
    public function __construct(SeoPageInterface $page, $encoding, BWBaseInterface $BWBase)
    {
        $this->page = $page;
        $this->encoding = $encoding;
        $this->BWBase = $BWBase;
    }

    public function getFunctions()
    {
        $parentFunctions = parent::getFunctions();
        return array_merge($parentFunctions, array(
            new \Twig_SimpleFunction('bwbase_link_tags', array($this, 'getLinkTags'), [
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('bwbase_sdks_html', array($this, 'getSDKHtml'), [
                'is_safe' => ['html'],
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
            ]),

            new \Twig_SimpleFunction('bwbase_links', array($this->BWBase, 'getLinks')),
            new \Twig_SimpleFunction('bwbase_sdks', array($this->BWBase, 'getSDKs')),
            new \Twig_SimpleFunction('bwbase_prccess_auto_content', array($this->BWBase, 'processAutoContent')),
            new \Twig_SimpleFunction('bwbase_add_meta', array($this->BWBase, 'addMeta')),
            new \Twig_SimpleFunction('bwbase_remove_meta', array($this->BWBase, 'removeMeta')),
            new \Twig_SimpleFunction('bwbase_replace_meta', array($this->BWBase, 'replaceMeta')),
            new \Twig_SimpleFunction('bwbase_remove_meta_by_key', array($this->BWBase, 'removeMetaByKey')),
            new \Twig_SimpleFunction('bwbase_set_sdks', array($this->BWBase, 'setSDKs')),
            new \Twig_SimpleFunction('bwbase_has_sdk', array($this->BWBase, 'hasSDK')),
            new \Twig_SimpleFunction('bwbase_get_sdk', array($this->BWBase, 'getSDK')),
            new \Twig_SimpleFunction('bwbase_enable_sdk', array($this->BWBase, 'enableSDK')),
            new \Twig_SimpleFunction('bwbase_disable_sdk', array($this->BWBase, 'disableSDK')),
            new \Twig_SimpleFunction('bwbase_disable_sdk', array($this->BWBase, 'setLinks')),
            new \Twig_SimpleFunction('bwbase_disable_sdk', array($this->BWBase, 'hasLink')),
            new \Twig_SimpleFunction('bwbase_disable_sdk', array($this->BWBase, 'addLink')),
            new \Twig_SimpleFunction('bwbase_disable_sdk', array($this->BWBase, 'removeLink')),
            new \Twig_SimpleFunction('bwbase_get_url', array($this->BWBase, 'getUrl'))
        ));
    }

    public function getName()
    {
        return 'bw.base.twig';
    }

    /**
     * @return array
     */
    public function getMetas()
    {
        $sonataSeoMetas = $this->page->getMetas();
        $BWBaseMetas = $this->BWBase->getMetas();
        foreach ($sonataSeoMetas as $type => $metas) {
            if (!isset($BWBaseMetas[$type])) {
                $BWBaseMetas[$type] = [];
            }

            foreach ((array)$metas as $name => $meta) {
                $BWBaseMetas[$type][] = array($name, $meta[0], $meta[1]);
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
        foreach ($this->BWBase->getLinks() as $type => $links) {
            foreach ((array)$links as $name => $linksSet) {
                foreach ($linksSet as $link) {
                    $sprintfArr = array(
                        $this->normalize($type),
                        $this->normalize($name)
                    );
                    $sprintfArgs = str_repeat(" %s=\"%s\"", sizeof($link)+1);
                    foreach ($link as $attr => $val) {
                        $sprintfArr[] = $this->normalize($attr);
                        if ($attr === 'href') {
                            $val = $this->BWBase->getUrl($val);
                        }
                        $sprintfArr[] = $this->normalize($val);
                    }
                    $html .= "<link ".vsprintf($sprintfArgs, $sprintfArr)." />\n";
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
        foreach ($this->BWBase->getSDKs($pageSection) as $sdkName => $sdkInfo) {
            switch ($sdkName) {
                case "twitter":
                    $html .= $environment->render('@BWBase/Block/_twitter_sdk.html.twig', []);
                    break;

                case "facebook":
                    $js_script = $sdkInfo['debug'] ? 'debug.js' : 'sdk.js';
                    $html .= $environment->render('@BWBase/Block/_facebook_sdk.html.twig', [
                        'app_id' => $sdkInfo['app_id'],
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
                        'domain' => $sdkInfo['domain'],
                        'debug' => $sdkInfo['debug']
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
                    $html .= '<script>console.warn("' . $sdkName . ' has no code configured for the twig extension `BaseExtension` using getSDKs");</script>';
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
            foreach ((array)$metas as $key => $meta) {
                // Support Sonata SEO which has 2 items in array - only 1 twig function to call then for all meta tags
                if (sizeof($meta) === 2) {
                    $name = $key;
                    list($content) = $meta;
                } else {
                    list($name, $content) = $meta;
                }
                if( null !== $content )
                {
                    foreach ($assetPostfixes as $assetPostfix) {
                        if (substr($name, strlen($assetPostfix) * -1) === $assetPostfix) {
                            $content = $this->BWBase->getUrl($content);
                            break;
                        }
                    }

                    if (!empty($content)) {
                        $html .= "<meta ".sprintf("%s=\"%s\" content=\"%s\"",
                                $type,
                                $this->normalize($name),
                                $this->normalize($content)
                            )." />\n";
                    } else {
                        $html .= "<meta ".sprintf("%s=\"%s\"",
                                $type,
                                $this->normalize($name)
                            )." />\n";
                    }
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
    private function normalize(string $string)
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