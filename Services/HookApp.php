<?php

namespace SoosyzeExtension\Matomo\Services;

class HookApp
{
    protected $config;

    protected $tpl;

    protected $user;

    public function __construct($config, $template, $user)
    {
        $this->config = $config;
        $this->tpl    = $template;
        $this->user   = $user;
        $this->views  = dirname(__DIR__) . '/Views/';
    }

    public function hookResponseAfter($request, &$response)
    {
        if (!($response instanceof \SoosyzeCore\Template\Services\Templating)) {
            return;
        }

        $url = $this->config->get('settings.analytics_url', false);
        $id  = $this->config->get('settings.analytics_id', false);

        if (!$url || !$id || !$this->isVisibilityPages($request) || !$this->isVisibilityRoles()) {
            return;
        }

        $script = $response->getBlock('this')->getVar('scripts');
        /* Obligation CNIL */
        $rgpd   = '_paq.push([function() {'
            . 'var self = this;'
            . 'function getOriginalVisitorCookieTimeout() {'
            . 'var now = new Date(), '
            . 'nowTs = Math.round(now.getTime() / 1000), '
            . 'visitorInfo = self.getVisitorInfo();'
            . 'var createTs = parseInt(visitorInfo[2]);'
            . 'var cookieTimeout = 33696000; /* 13 mois en secondes*/'
            . 'var originalTimeout = createTs + cookieTimeout - nowTs;'
            . 'return originalTimeout;'
            . '}'
            . 'this.setVisitorCookieTimeout( getOriginalVisitorCookieTimeout() );'
            . '}]);';

        $script .= '<script type="text/javascript">'
            . 'var _paq = window._paq || [];'
            . $rgpd
            /* tracker methods like \"setCustomDimension\" should be called before \"trackPageView\" */
            . '_paq.push(["trackPageView"]);'
            . '_paq.push(["enableLinkTracking"]);'
            . '(function() {'
            . 'var u="' . htmlspecialchars($url) . '";'
            . '_paq.push(["setTrackerUrl", u+"matomo.php"]);'
            . '_paq.push(["setSiteId", "' . htmlspecialchars($id) . '"]);'
            . 'var d=document, '
            . 'g=d.createElement("script"), '
            . 's=d.getElementsByTagName("script")[0];'
            . 'g.type="text/javascript";'
            . 'g.async=true;'
            . 'g.defer=true;'
            . 'g.src=u+"matomo.js";'
            . 's.parentNode.insertBefore(g,s);'
            . '})();'
            . '</script>';

        $response->view('this', [ 'scripts' => $script ]);
    }

    protected function isVisibilityRoles()
    {
        $user            = $this->user->isConnected();
        $analytics_roles = explode(',', $this->config->get('settings.analytics_roles'));
        $visibility      = $this->config->get('settings.analytics_visibility_roles');

        /* S'il n'y a pas d'utilisateur et que l'on demande de suivre les utilisateurs non connectés. */
        if (!$user && in_array(1, $analytics_roles)) {
            return !$visibility;
        }

        $roles = $this->user->getRolesUser($user[ 'user_id' ]);

        foreach ($analytics_roles as $analytics_role) {
            foreach ($roles as $role) {
                if ($analytics_role == $role[ 'role_id' ]) {
                    return !$visibility;
                }
            }
        }

        return $visibility;
    }

    protected function isVisibilityPages($request)
    {
        $uri = $request->getUri();
        parse_str($uri->getQuery(), $query);

        $path = empty($query[ 'q' ])
            ? '/'
            : $query[ 'q' ];

        $visibility = $this->config->get('settings.analytics_visibility_pages');
        $pages      = $this->config->get('settings.analytics_pages', '');

        foreach (explode("\n", $pages) as $page) {
            if ($page === $path) {
                return $visibility;
            }
            $str     = preg_quote($page, '/');
            $pattern = strtr($str, [ '%' => '.*' ]);
            if (preg_match("/^$pattern$/", $path)) {
                return $visibility;
            }
        }

        return !$visibility;
    }
}
