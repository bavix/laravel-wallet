function init(id) {
  const script = document.createElement('script');
  script.async = true;
  script.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
  document.body.appendChild(script);

  window.dataLayer = window.dataLayer || [];
  window.gtag = window.gtag || function gtag(){dataLayer.push(arguments);}

  window.gtag('js', new Date());
  window.gtag('config', id);
}

function collect() {
  if (window.dataLayer === undefined) {
    init($docsify.ga)
  }

  // usage: https://developers.google.com/analytics/devguides/collection/gtagjs/pages
  window.gtag('event', 'page_view', {
    page_title: document.title,
    page_location: location.href,
    page_path: location.pathname,
  });
}

const install = function(hook) {
  if (!$docsify.ga) {
    console.error('[Docsify] ga is required.');
    return;
  }

  hook.beforeEach(collect);
};

$docsify.plugins = [].concat(install, $docsify.plugins);
