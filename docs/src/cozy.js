import cozyHouse from '@bavix/cozy-house-kit'

function collect() {
  if (cozyHouse) {
    cozyHouse.push('docs', 'page', location.hash);
  }
}

const install = function(hook) {
  if (!$docsify.cozyHouse) {
    console.error('[Docsify] cozyHouse is required.');
    return;
  }

  if (cozyHouse) {
    cozyHouse.setApiUrl('https://cozy.babichev.net')
    cozyHouse.setToken($docsify.cozyHouse)
    hook.beforeEach(collect);
  }
};

$docsify.plugins = [].concat(install, $docsify.plugins);
