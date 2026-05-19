import { createApp, h } from 'vue'
import type { DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

import vuetify from './plugins/vuetify'
import store   from './store'
import './assets/main.css'

createInertiaApp({
  progress: { color: '#1565C0' },
  title:    (title) => title ? `${title} — HKGI Service` : 'HKGI Service',

  resolve: (name) => {
    const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },

  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(vuetify)
      .use(store)
      .mount(el)
  },
})