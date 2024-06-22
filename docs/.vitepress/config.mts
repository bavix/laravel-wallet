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
        rel: 'mask-icon',
        href: 'https://github.com/bavix/laravel-wallet/assets/5111255/f48a8e79-8a9d-469a-b056-b3d04835992d',
        color: "#f4e664",
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
      { text: 'Chat', link: 'https://t.me/laravel_wallet' },
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
        ]
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
        ]
      },
      {
        text: 'Multi Wallet',
        items: [
          { text: 'New Wallet', link: '/guide/multi/new-wallet' },
          { text: 'Transfer', link: '/guide/multi/transfer' },
          { text: 'Transaction Filter', link: '/guide/multi/transaction-filter' },
        ]
      },
      {
        text: 'Fractional Wallet',
        items: [
          { text: 'Deposit', link: '/guide/fractional/deposit' },
          { text: 'Withdraw', link: '/guide/fractional/withdraw' },
          { text: 'Transfer', link: '/guide/fractional/transfer' },
        ]
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
        ]
      },
      {
        text: 'Database Transaction',
        items: [
          { text: 'Atomic Service', link: '/guide/db/atomic-service' },
          { text: 'Race Condition', link: '/guide/db/race-condition' },
          { text: 'Transaction', link: '/guide/db/transaction' },
        ]
      },
      {
        text: 'Events',
        items: [
          { text: 'Balance Updated', link: '/guide/events/balance-updated-event' },
          { text: 'Wallet Created', link: '/guide/events/wallet-created-event' },
          { text: 'Transaction Created', link: '/guide/events/transaction-created-event' },
          { text: 'Customize', link: '/guide/events/customize' },
        ]
      },
      {
        text: 'Helpers',
        items: [
          { text: 'Formatter', link: '/guide/helpers/formatter' },
        ]
      },
      {
        text: 'High performance api handles',
        items: [
          { text: 'Batch Transactions', link: '/guide/high-performance/batch-transactions' },
          { text: 'Batch Transfers', link: '/guide/high-performance/batch-transfers' },
        ]
      },
      {
        text: 'CQRS',
        items: [
          { text: 'Create Wallet', link: '/guide/cqrs/create-wallet' },
        ]
      },
      {
        text: 'Additions',
        items: [
          { text: 'Wallet Swap', link: '/guide/additions/swap' },
          { text: 'Support UUID', link: '/guide/additions/uuid' },
        ]
      },
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/bavix/laravel-wallet' },
    ],

    footer: {
      message: 'Released under the <a href="https://github.com/bavix/laravel-wallet/blob/master/LICENSE">MIT License</a>.',
      copyright: 'Copyright © 2018-present <a href="https://github.com/rez1dent3">Babichev Maksim</a>'
    }
  }
})
