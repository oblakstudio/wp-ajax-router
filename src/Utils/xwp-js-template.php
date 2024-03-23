<?php
/**
 * Script injection for the xwp-ajax.
 *
 * @package eXtended WordPress
 */

return <<<'HTML'
    <script defer>
        window.xwp = window.xwp || {};
        window.xwp.wpAjaxUrl = '%s';
        window.xwp.adminAjaxUrl = '%s';
        window.xwp.forWpAjax = (...parts) => `${window.xwp.wpAjaxUrl}/${[].concat(...parts).join('/')}`;
        window.xwp.forAdminAjax = (action, nonce = undefined, data = {}) => {
            let url = `${window.xwp.AdminAjax}?action=${action}`;

            if (typeof nonce === 'string' && nonce != '') {
                url += `&_wpnonce=${nonce.toString()}`;
            }

            if (data instanceof FormData) {
                for (let [key, value] of data.entries()) {
                    if (typeof value === 'string') {
                        url += `&${encodeURIComponent(key)}=${encodeURIComponent(value)}`;
                    }
                }

                return url;
            }

            Object.entries(data).forEach(([key, value]) => {
                url += `&${encodeURIComponent(key)}=${encodeURIComponent(value)}`;
            });

            return url;
        }
    </script>

HTML;
