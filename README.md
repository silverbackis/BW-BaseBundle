# BW Base Bundle
This bundle extends the very popular [Sonata SEO Bundle](https://sonata-project.org/bundles/seo/master/doc/reference/installation.html) and provides additional functionality including:
* Configuring link tags
* Enabling Javascript SDKs without an additional extension and with some additional configuration options.
* Converting appropriate meta and link tags to full URLs and respecting the base_urls option set in the configuration file
* Ability to define multiple meta tags with the same name (for multiple og:image tags)

## Installation
You can install this bundle using composer:
```bash
composer require silverbackis/bw-base-bundle
```

Then enable **both** Sonata SEO and the BW Base Bundle:
```php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    // ...

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Sonata\SeoBundle\SonataSeoBundle(),
            new BW\BaseBundle\BWBaseBundle(),
        );

        // ...
    }
}
```

There is a twig template you extend your templates from which will insert all the default Twig functions in your template from both Sonata SEO and BW Base:
```twig
{% extends '@BWBase/base.html.twig' %}
```


## Configuration
By default 2 parameters are set which can be overridden (or not used if you don't want to):
```
favicon_base: bundles/app/images/favicon
opengraph_base: bundles/app/images/opengraph
```

Here is a sample full configuration for this bundle.
```yaml
bw_base:
  page:
    metas:
      name:
        twitter:site:       '@silverbackis'
        twitter:image:      '%opengraph_base%opengraph_image1.png'
        # auto = same as Sonata SEO title tag
        twitter:title:      'auto'
        # auto = same as meta description (from either Sonata SEO or BW Base)
        twitter:description: 'auto'
      property:
        # auto = same as Sonata SEO title tag
        - { property: "og:title", content: "auto" }
        # auto = same as meta description (from either Sonata SEO or BW Base)
        - { property: "og:description", content: "auto" }
        # auto = get URL from the current request
        - { property: "og:url", content: "auto" }
        - { property: "og:image", content: "%opengraph_base%opengraph_image1.png" }
        - { property: "og:image:width", content: "500" }
        - { property: "og:image:height", content: "500" }
        - { property: "og:image", content: "%opengraph_base%opengraph_image2.png" }
    links:
      rel:
        apple-touch-icon-precomposed:
          - { sizes: 57x57, href: '%favicon_base%apple-touch-icon-57x57.png' }
        icon:
          - { type: image/png, sizes: 196x196, href: '%favicon_base%favicon-196x196.png' }
    js_sdk:
      google_analytics:
        enabled: true
        id: UA-12345678-01
        domain: www.yourdomain.com
      woopra:
        enabled: true
        domain: www.yourdomain.com
      facebook_pixel:
        enabled: true
        id: pixelID
      facebook:
        enabled: true
        app_id: 1234567890
        xfbml: true
        version: 'v2.8'
        language: en_GB
        login_status_check: false
        debug: false
      twitter: true
```

## Twig functions
The Meta tags defined in the BW Base bundle will be merged with those defined for the Sonata SEO bundle when you call the following twig function:
```twig
{{ bwbase_meta_tags() }}
```

There are also some more Twig functions available:
```twig
{# Outputs the title without the title tag for easy block overriding #}
{{ bwbase_title() }}

{# Outputs the link tags #}
{{ bwbase_link_tags() }}

{# You can omit the parameter to output all the sdks. Otherwise you can specify or use 'head' or 'body' for each sdks preferred location #}
{{ bwbase_sdks_html('head') }}

{# You can also just return the raw arrays of the data when testing #}
{{ dump(bwbase_links()) }}
{{ dump(bwbase_sdks()) }}
{{ dump(bwbase_metas()) }}
```

## The BW Base Service
You can access the BW Base service with the alias `bw.base.page`

The following methods are available:
- `setMetas(array $metadatas)`
- `addMeta($type, $name, $content, array $extras = array())`
- `hasMeta($type, $name)`
- `getMeta($type, $name)`
- `removeMeta($type, $name)`
- `removeMetaByKey($type, $key)`
- `setSDKs(array $sdks)`
- `getSDKs($bodyPart=false)`
- `hasSDK($name)`
- `getSDK($name)`
- `enableSDK($name)`
- `disableSDK($name)`
- `setLinks(array $linkdatas)`
- `getLinks()`
- `hasLink($type, $name)`
- `addLink($type, $name, $content)`
- `removeLink($type, $name)`
- `getUrl($url)`

For example, you may only want the Twitter SDK on a specific page which you can enable from your controller using:
`$this->container->get('bw.base.page')->enableSDK('twitter')`
