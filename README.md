<div align="center">

<h1 align="center" style="border-bottom: none;">WordPress AJAX Router</h1>

![Packagist Version](https://img.shields.io/packagist/v/xwp/ajax-server)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/xwp/ajax-server/php)
[![semantic-release: angular](https://img.shields.io/badge/semantic--release-angular-e10079?logo=semantic-release)](https://github.com/semantic-release/semantic-release)

</div>

## PSR-7 / PSR-15 compatible AJAX router for WordPress. 

Inspired by [NestJS](https://nestjs.com) and [Express](https://expressjs.com) - this library provides a simple way to handle AJAX requests in WordPress.

[![Inner Platform Effect](https://files.oblak.bot/yo-dawg-wp-ajax.jpg)](https://en.wikipedia.org/wiki/Inner-platform_effect)


## QuickStart

### Install the library

```bash
composer require x-wp/ajax-server
```

### Define a controller

```php
<?php

use Sunrise\Http\Router\Exception\PageNotFoundException;
use XWP\Decorator\Core\Controller;
use XWP\Decorator\Core\Use_Guards;
use XWP\Decorator\HTTP\Argument\Body;
use XWP\Decorator\HTTP\Get;
use XWP\Decorator\HTTP\Param;
use XWP\Decorator\HTTP\Post;
use XWP\Guard\Capability_Guard;
use XWP\Guard\Nonce_Guard;
use XWP\Response\JSON;

#[Use_Guards( new Nonce_Guard() )]
#[Controller( 'my-endpoint' )]
class My_Controller {
	#[Get( 'get-post/{id}', JSON::class )]
	public function get_post( #[Param( 'id' )]
    int $id, ): \WP_Post {
		return \get_post( $id );
	}

	#[Use_Guards( new Capability_Guard( 'manage_options' ) )]
	#[Post( 'modify-post/{id}', JSON::class )]
	public function modify_post( #[Param( 'id' )] int $id, #[Body] array $data ): int {
		$post = \get_post( $id );

		if ( ! $post ) {
			throw new PageNotFoundException( 'Invalid post', 404 );
		}

		return wp_update_post(
            array_merge(
                array( 'ID' => $id ),
                $data,
            ),
		);
	}
}
```

### Register a controller

```php
xwp_register_routes( My_Controller::class );
```

This will expose the following endpoints:
 * `GET  /wp-ajax/my-endpoint/get-post/{id}`
 * `POST /wp-ajax/my-endpoint/modify-post/{id}`

Ajax base URL is `/wp-ajax` by default, and can be changed in permalink settings.

### Getting Endpoint URLs

#### In PHP

```php
xwp_ajax_url( 'some', 'endpoint' ); // https://example.com/wp-ajax/some/endpoint
xwp_ajax_url( 'some/endpoint' );    // https://example.com/wp-ajax/some/endpoint
```

#### In JavaScript

Library adds a global object `xwp` attached to `window` object. Both in the admin and on the frontend.
You can disable this by using `xwp_ajax_vars` filter, or calling the `xwp_disable_ajax_js_vars`

```typescript
declare type XWP = {
  /**
   * URL for the wp-ajax endpoint
   */
  wpAjaxUrl: string;

  /**
   * URL for the admin-ajax endpoint
   */
  adminAjaxUrl: string;

  /**
   * Get URL for the wp-ajax endpoint
   * 
   * @param parts - parts of the URL
   */
  forWpAjax: (...parts: string[]): string;

  /**
   * Get URL for the admin-ajax endpoint
   * 
   * @param action - action name
   * @param nonce  - nonce value (optional)
   * @param data   - data to be sent
   */
  forAdminAjax: (action:string , nonce?: string, data: Record<string, any>|FormData): string;
}
```

```javascript
const baseUrl = window.xwp.wpAjaxUrl;                       // https://example.com/wp-ajax
const endUrl  = window.xwp.adminAjaxUrl;                    // https://example.com/wp-admin/admin-ajax.php
const ajaxEp  = window.xwp.forWpAjax( 'some', 'endpoint' ); // https://example.com/wp-ajax/some/endpoint
const adminEp = window.xwp.forAdminAjax( 'some_action' );   // https://example.com/wp-admin/admin-ajax.php?action=some_action
```
## FAQ

#### 1. Did you just reinvent the wheel?
Yes, we did. We basically took the wheels from NestJS and Express and put it on a WordPress car.

#### 2. Why?
Read [this](docs/why.md).

#### 3. Can I use this in my project?
Yes, you can. But remember, this is a work in progress and we are still figuring out the best way to do things. API is subject to change.

#### 4. Can I contribute?
Sure 💪💪💪.  
If you have an idea, a suggestion, or a bug report - feel free to open an issue.

### 5. How does it work?
Read [this](docs/how-it-works.md).

