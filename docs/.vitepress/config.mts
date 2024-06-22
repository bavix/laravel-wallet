import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Laravel Wallet",
  description: "Easy work with virtual wallet",
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
          svg: '<svg height="800px" width="800px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve"><circle style="fill:#59aae7" cx="256" cy="256" r="247.916"/><path style="fill:#3d9ae3" d="M256,8.084c-10.96,0-21.752,0.72-32.337,2.099C345.304,26.029,439.242,130.04,439.242,256\n' +
              '\ts-93.939,229.971-215.579,245.817c10.585,1.379,21.377,2.099,32.337,2.099c136.921,0,247.916-110.996,247.916-247.916\n' +
              '\tS392.921,8.084,256,8.084z"/><path style="fill:#fcfcfc" d="M167.573,309.4l-79.955-39.978c-2.191-1.096-2.213-4.216-0.037-5.342l303.756-157.115\n' +
              '\tc2.231-1.154,4.807,0.786,4.315,3.249l-52.298,261.49c-0.373,1.866-2.369,2.916-4.119,2.167l-71.075-30.46\n' +
              '\tc-0.852-0.365-1.825-0.316-2.635,0.135l-91.844,51.024c-1.997,1.109-4.452-0.334-4.452-2.619v-79.87\n' +
              '\tC169.229,310.945,168.588,309.908,167.573,309.4z"/><path style="fill:#d8d7da" d="M202.069,336.347l-0.497-79.825c-0.003-0.511,0.262-0.986,0.697-1.253l129.671-79.214\n' +
              '\tc1.47-0.898,3.008,1.049,1.794,2.271l-98.682,99.383c-0.109,0.11-0.201,0.236-0.269,0.375l-16.88,33.757l-13.082,25.168\n' +
              '\tC204.118,338.36,202.078,337.868,202.069,336.347z"/><path d="M437.019,74.981C388.667,26.628,324.379,0,256,0S123.333,26.628,74.981,74.981S0,187.62,0,256\n' +
              '\ts26.628,132.667,74.981,181.019C123.333,485.372,187.62,512,256,512s132.667-26.628,181.019-74.981\n' +
              '\tC485.372,388.667,512,324.379,512,256S485.372,123.333,437.019,74.981z M256,495.832C123.756,495.832,16.168,388.244,16.168,256\n' +
              '\tS123.756,16.168,256,16.168S495.832,123.756,495.832,256S388.244,495.832,256,495.832z"/><path d="M352.42,282.405l-16.162,80.808l-66.295-28.412c-2.297-0.985-4.923-0.85-7.111,0.363l-85,47.223v-72.492\n' +
              '\tc0-3.062-1.73-5.861-4.469-7.231l-72.015-36.007l283.53-146.654l-24.605,123.023c-1,5.003,2.826,9.67,7.928,9.67l0,0\n' +
              '\tc3.853,0,7.171-2.721,7.928-6.499l27.903-139.517c0.609-3.047-0.582-6.174-3.064-8.043c-2.482-1.87-5.817-2.15-8.577-0.722\n' +
              '\tL79.822,259.599c-2.702,1.397-4.391,4.194-4.371,7.236s1.747,5.815,4.469,7.176l81.764,40.88v81.006c0,2.12,0.721,4.218,2.18,5.757\n' +
              '\tc1.614,1.703,3.759,2.557,5.905,2.557c1.352,0,2.704-0.338,3.927-1.018l93.544-51.969l71.597,30.684\n' +
              '\tc1.523,0.653,3.209,0.923,4.839,0.619c3.355-0.627,5.849-3.197,6.485-6.372l18.115-90.577c1-5.003-2.826-9.67-7.928-9.67l0,0\n' +
              '\tC356.493,275.907,353.175,278.627,352.42,282.405z"/><path d="M200.247,350.099c0.621,0.147,1.244,0.218,1.86,0.218c3.007,0,5.837-1.686,7.228-4.47l31.75-63.5l106.862-106.862\n' +
              '\tc2.898-2.898,3.168-7.51,0.627-10.727c-2.541-3.216-7.089-4.024-10.581-1.873l-140.126,86.232c-2.391,1.471-3.847,4.078-3.847,6.885\n' +
              '\tv86.232C194.021,345.982,196.599,349.238,200.247,350.099z M210.189,260.517l77.636-47.777l-59.101,59.101\n' +
              '\tc-0.613,0.614-1.125,1.324-1.513,2.101l-17.022,34.043V260.517z"/></svg>'
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
