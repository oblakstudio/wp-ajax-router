# How it works

> Theory is when you know everything but nothing works.  
> Practice is when everything works but no one knows why.  
> Here we combine theory and practice: nothing works and no one knows why.
>
> _Unknown_

# Basics

Library wraps `sunrise/http-router` library for now. Plan is to create a context aware router that will be able to handle both REST and AJAX requests. This router will then be globally usable in the DI system which we're planning to implement.

## Execution flow

By default - library does nothing. It needs at least one controller for it to initialize.

When you register a controller (or multiple controllers) - the following flow is executed:

### Ajax_Server

1. `XWP\Ajax_Server::instance()` function is called which will register the initialiation on `plugins_loaded` (priority: `PHP_INT_MAX`)
2. Class will check for existence of `xwp_ajax_slug` option and set it to `wp-ajax` if it doesn't exist (this will flush the rewrite rules)
3. Class will add the `xwp_ajax` query var to the list of query vars.
4. Singleton will create and instance of `Request_Dispatcher` on `parse_request` action if the query var is present.

### Request_Dispatcher

1. Defines the standard PHP constants (`DOING_AJAX`, `WP_ADMIN`)
2. Initializes router
3. On `parse_request:10` action dispatcher will initialize the controller classes and their decorators
4. Controller decorator wraps the controller and forwards the response to a `Response_Handler` which creates a `PSR-7` response object
5. Response object is then sent to the client


# Looks good - how do I use it?

We're still working on the documentation. But you can check the [NestJS](https://docs.nestjs.com/) documentation for the basics. We're trying to mimic the way NestJS handles things.
