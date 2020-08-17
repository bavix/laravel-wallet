function collect() {
  if (window.cozyHouse) {
    window.cozyHouse.push('docs', 'page', location.hash);
  }
}

const install = function(hook) {
  if (!$docsify.cozyHouse) {
    console.error('[Docsify] cozyHouse is required.');
    return;
  }

  if (window.cozyHouse) {
    window.cozyHouse.setApiUrl('https://cozy.babichev.net')
    window.cozyHouse.setToken($docsify.cozyHouse)
    hook.beforeEach(collect);
  }
};

$docsify.plugins = [].concat(install, $docsify.plugins);
