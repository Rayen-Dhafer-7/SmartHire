import { createApp } from 'vue'
import router from './router'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap-icons/font/bootstrap-icons.css'
import App from './App.vue'
import * as Sentry from '@sentry/vue'

const app = createApp(App)

Sentry.init({
  app,
  dsn: "https://0562b994dc3f798e1a5fcbf6a24b8bb1@o4511092967997440.ingest.de.sentry.io/4511093402894416",
  integrations: [
    Sentry.browserTracingIntegration({ router }),
  ],
  tracesSampleRate: 0.1,
  sendDefaultPii: true,
})

app.use(router)
app.mount('#app')