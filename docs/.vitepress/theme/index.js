import DefaultTheme from 'vitepress/theme'
import './custom.css'
import VersionTag from './components/VersionTag.vue'

export default {
  ...DefaultTheme,
  enhanceApp({ app }) {
    app.component('VersionTag', VersionTag)
  },
}
