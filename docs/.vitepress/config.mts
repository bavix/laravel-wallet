import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Laravel Wallet",
  description: "It's easy to work with a virtual wallet",
  base: '/laravel-wallet/',
  lastUpdated: true,
  head: [
    [
      'script',
      { async: '', src: 'https://www.googletagmanager.com/gtag/js?id=G-LNEGT551DV' }
    ],
    [
      'script',
      {},
      `window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-LNEGT551DV');`
    ],
    [
      'link', {
        rel: 'icon',
        href: 'https://github.com/bavix/laravel-wallet/assets/5111255/f48a8e79-8a9d-469a-b056-b3d04835992d',
        sizes: "any",
        type: "image/svg+xml",
      }
    ],
  ],
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    search: {
      provider: 'local'
    },
    editLink: {
      pattern: 'https://github.com/bavix/laravel-wallet/edit/master/docs/:path'
    },
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Guide', link: '/guide/introduction/' },
      { text: 'Issues', link: 'https://github.com/bavix/laravel-wallet/issues' },
      { text: 'Discussions', link: 'https://github.com/bavix/laravel-wallet/discussions' },
      { text: 'Donate', link: 'https://opencollective.com/laravel-wallet' },
    ],

    sidebar: [
      {
        text: 'Getting started',
        items: [
          { text: 'Introduction', link: '/guide/introduction/' },
          { text: 'Installation', link: '/guide/introduction/installation' },
          { text: 'Configuration', link: '/guide/introduction/configuration' },
          { text: 'Basic Usage', link: '/guide/introduction/basic-usage' },
          { text: 'Upgrade', link: '/guide/introduction/upgrade' },
        ],
        collapsed: false,
      },
      {
        text: 'Single/Default Wallet',
        items: [
          { text: 'Deposit', link: '/guide/single/deposit' },
          { text: 'Withdraw', link: '/guide/single/withdraw' },
          { text: 'Transfer', link: '/guide/single/transfer' },
          { text: 'Refresh Balance', link: '/guide/single/refresh' },
          { text: 'Confirm Transaction', link: '/guide/single/confirm' },
          { text: 'Cancel Transaction', link: '/guide/single/cancel' },
          { text: 'Exchange', link: '/guide/single/exchange' },
          { text: 'Credit Limits', link: '/guide/single/credit-limits' },
        ],
        collapsed: false,
      },
      {
        text: 'Multi Wallet',
        items: [
          { text: 'New Wallet', link: '/guide/multi/new-wallet' },
          { text: 'Transfer', link: '/guide/multi/transfer' },
          { text: 'Transaction Filter', link: '/guide/multi/transaction-filter' },
        ],
        collapsed: false,
      },
      {
        text: 'Fractional Wallet',
        items: [
          { text: 'Deposit', link: '/guide/fractional/deposit' },
          { text: 'Withdraw', link: '/guide/fractional/withdraw' },
          { text: 'Transfer', link: '/guide/fractional/transfer' },
        ],
        collapsed: false,
      },
      {
        text: 'Purchases',
        items: [
          { text: 'Payment', link: '/guide/purchases/payment' },
          { text: 'Payment Free', link: '/guide/purchases/payment-free' },
          { text: 'Refund', link: '/guide/purchases/refund' },
          { text: 'Gift', link: '/guide/purchases/gift' },
          { text: 'Cart', link: '/guide/purchases/cart' },
          { text: 'Commissions', link: '/guide/purchases/commissions' },
          { text: 'Customize receiving', link: '/guide/purchases/receiving' },
        ],
        collapsed: false,
      },
      {
        text: 'Database Transaction',
        items: [
          { text: 'Atomic Service', link: '/guide/db/atomic-service' },
          { text: 'Race Condition', link: '/guide/db/race-condition' },
          { text: 'Transaction', link: '/guide/db/transaction' },
        ],
        collapsed: false,
      },
      {
        text: 'Events',
        items: [
          { text: 'Balance Updated', link: '/guide/events/balance-updated-event' },
          { text: 'Wallet Created', link: '/guide/events/wallet-created-event' },
          { text: 'Transaction Created', link: '/guide/events/transaction-created-event' },
          { text: 'Customize', link: '/guide/events/customize' },
        ],
        collapsed: false,
      },
      {
        text: 'Helpers',
        items: [
          { text: 'Formatter', link: '/guide/helpers/formatter' },
        ],
        collapsed: false,
      },
      {
        text: 'High performance api handles',
        items: [
          { text: 'Batch Transactions', link: '/guide/high-performance/batch-transactions' },
          { text: 'Batch Transfers', link: '/guide/high-performance/batch-transfers' },
        ],
        collapsed: false,
      },
      {
        text: 'CQRS',
        items: [
          { text: 'Create Wallet', link: '/guide/cqrs/create-wallet' },
        ],
        collapsed: false,
      },
      {
        text: 'Additions',
        items: [
          { text: 'Wallet Swap', link: '/guide/additions/swap' },
          { text: 'Support UUID', link: '/guide/additions/uuid' },
        ],
        collapsed: false,
      },
    ],

    socialLinks: [
      {
        link: 'https://t.me/laravel_wallet',
        icon: {
          svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" xml:space="preserve"><circle cx="256" cy="256" r="247.916" fill="#59aae7"/><path d="M256 8.084c-10.96 0-21.752.72-32.337 2.099C345.304 26.029 439.242 130.04 439.242 256s-93.939 229.971-215.579 245.817A250.202 250.202 0 0 0 256 503.916c136.921 0 247.916-110.996 247.916-247.916S392.921 8.084 256 8.084z" fill="#3d9ae3"/><path d="m167.573 309.4-79.955-39.978c-2.191-1.096-2.213-4.216-.037-5.342l303.756-157.115c2.231-1.154 4.807.786 4.315 3.249l-52.298 261.49a2.997 2.997 0 0 1-4.119 2.167l-71.075-30.46a2.993 2.993 0 0 0-2.635.135l-91.844 51.024c-1.997 1.109-4.452-.334-4.452-2.619v-79.87a2.997 2.997 0 0 0-1.656-2.681z" fill="#fcfcfc"/><path d="m202.069 336.347-.497-79.825a1.46 1.46 0 0 1 .697-1.253l129.671-79.214c1.47-.898 3.008 1.049 1.794 2.271l-98.682 99.383c-.109.11-.201.236-.269.375l-16.88 33.757-13.082 25.168c-.703 1.351-2.743.859-2.752-.662z" fill="#d8d7da"/><path d="M437.019 74.981C388.667 26.628 324.379 0 256 0S123.333 26.628 74.981 74.981 0 187.62 0 256s26.628 132.667 74.981 181.019C123.333 485.372 187.62 512 256 512s132.667-26.628 181.019-74.981C485.372 388.667 512 324.379 512 256s-26.628-132.667-74.981-181.019zM256 495.832C123.756 495.832 16.168 388.244 16.168 256S123.756 16.168 256 16.168 495.832 123.756 495.832 256 388.244 495.832 256 495.832z"/><path d="m352.42 282.405-16.162 80.808-66.295-28.412a8.092 8.092 0 0 0-7.111.363l-85 47.223v-72.492a8.085 8.085 0 0 0-4.469-7.231l-72.015-36.007 283.53-146.654-24.605 123.023c-1 5.003 2.826 9.67 7.928 9.67a8.088 8.088 0 0 0 7.928-6.499l27.903-139.517a8.085 8.085 0 0 0-11.641-8.765L79.822 259.599a8.086 8.086 0 0 0 .098 14.412l81.764 40.88v81.006c0 2.12.721 4.218 2.18 5.757a8.109 8.109 0 0 0 5.905 2.557 8.072 8.072 0 0 0 3.927-1.018l93.544-51.969 71.597 30.684c1.523.653 3.209.923 4.839.619a8.097 8.097 0 0 0 6.485-6.372l18.115-90.577c1-5.003-2.826-9.67-7.928-9.67a8.081 8.081 0 0 0-7.928 6.497z"/><path d="M200.247 350.099a8.085 8.085 0 0 0 9.088-4.252l31.75-63.5 106.862-106.862a8.083 8.083 0 0 0-9.954-12.6l-140.126 86.232a8.084 8.084 0 0 0-3.847 6.885v86.232a8.084 8.084 0 0 0 6.227 7.865zm9.942-89.582 77.636-47.777-59.101 59.101a8.094 8.094 0 0 0-1.513 2.101l-17.022 34.043v-47.468z"/></svg>'
        },
      },
      { icon: 'github', link: 'https://github.com/bavix/laravel-wallet' },
    ],

    footer: {
      message: 'Released under the <a href="https://github.com/bavix/laravel-wallet/blob/master/LICENSE">MIT License</a>.',
      copyright: 'Copyright © 2018-present <a href="https://github.com/rez1dent3">Babichev Maksim</a>'
    }
  }
})
