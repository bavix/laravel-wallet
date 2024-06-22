---
# https://vitepress.dev/reference/default-theme-home-page
layout: home

hero:
  name: "Laravel Wallet"
  text: It's simple!
  tagline: Easy work with virtual wallet
  image:
    src: https://github.com/bavix/laravel-wallet/assets/5111255/24cf424a-0177-4712-b74c-52b4ba88c428
    alt: Laravel Wallet
  actions:
    - theme: brand
      text: Getting started
      link: /guide/introduction/
    - theme: alt
      text: Upgrade Guide
      link: /guide/introduction/upgrade

features:
  - title: Default Wallet
    details: For simple projects when there is no need for multiple wallets.
    icon: 💰
    link: /guide/single/deposit
  - title: Multi wallets
    details: Many wallets for one model. Easy API.
    icon: 🎒
    link: /guide/multi/new-wallet
  - title: Purchases
    details: E-commerce. Create goods and buy them using wallets. There are also shopping carts, availability, taxes and fees.
    icon: 🛍️
    link: /guide/purchases/payment
  - title: Exchanges
    details: Exchanges between wallets. Convert currency from one wallet to another.
    icon: 💱
    link: /guide/single/exchange
  - title: Support UUID
    details: Models with UUID are supported.
    icon: ❄️
    link: /guide/additions/uuid
  - title: Events
    details: For more complex projects there are events and high performance API.
    icon: 📻
    link: /guide/events/balance-updated-event
---

