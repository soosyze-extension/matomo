
var _paq = window._paq || [];

_paq.push([function () {
        var self = this;
        function getOriginalVisitorCookieTimeout() {
            var now = new Date();
            var nowTs = Math.round(now.getTime() / 1000);
            var visitorInfo = self.getVisitorInfo();
            var createTs = parseInt(visitorInfo[2]);
            var cookieTimeout = 33696000; /* 13 mois en secondes*/
            var originalTimeout = createTs + cookieTimeout - nowTs;

            return originalTimeout;
        }
        this.setVisitorCookieTimeout(getOriginalVisitorCookieTimeout());
    }]);

/* tracker methods like \"setCustomDimension\" should be called before \"trackPageView\" */
_paq.push(["trackPageView"]);
_paq.push(["enableLinkTracking"]);

(function () {
    var u = config.matomo.setTrackerUrl;

    _paq.push(["setTrackerUrl", u + "matomo.php"]);
    _paq.push(["setSiteId", config.matomo.setSiteId]);

    var d = document;
    var g = d.createElement("script");
    var s = d.getElementsByTagName("script")[0];

    g.type = "text/javascript";
    g.async = true;
    g.defer = true;
    g.src = u + "matomo.js";
    s.parentNode.insertBefore(g, s);
})();