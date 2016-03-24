### Installation

1) Install and configure sonata admin and sonata media bundles

2) Retrieve bundle

```$ php composer.phar require aurumit/cms-bundle```

3) Register bundle

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
  return [
      // ...
      new Ait\CmsBundle\AitCmsBundle(),
      // ...
  ];
}
```

4) Generate local bundle for entities

```$ php app/console sonata:easy-extends:generate AitCmsBundle --dest=src```

5) Register generated bundle

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
  return [
      // ...
      new Application\Ait\CmsBundle\ApplicationAitCmsBundle(),
      // ...
  ];
}
```

6) Update database schema

```$ php app/console dosctine:schema:update --force --complete```

7) Default configuration

```yaml
ait_cms:
    enable_routing: true
    first_page_route_action: welcome
    class:
        parent_block: Application\Ait\CmsBundle\Entity\ParentBlock
        page: Application\Ait\CmsBundle\Entity\Page
        sonata_media: Application\Sonata\MediaBundle\Entity\Media
    admin:
        page:
            class: Ait\CmsBundle\Admin\PageAdmin
    translation:
        locales: [en]
        default_locale: en
```

8) Update sonata block configuration

```yaml
sonata_block:
    # ...
    blocks:
        #...
        ait_cms.admin.block.info_block: ~
        # ...
    # ...
```

9) Update sonata admin configuration

```yaml
sonata_admin:
    # ...
    dashboard:
        blocks:
            # ...
            -
                position: left
                type: ait_cms.admin.block.info_block
            -
                position: left
                type: sonata.admin.block.admin_list
                settings:
                    groups: [admin]
            -
                position: right
                type: sonata.admin.block.admin_list
                settings:
                    groups: [ait_cms]
            # ...
        groups:
            # ...
            ait_cms:
                label: Content management
            admin:
                label: Site management
            # ...
        # ...
    # ...
```
