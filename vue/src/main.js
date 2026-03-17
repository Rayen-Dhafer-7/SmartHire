import { createApp } from 'vue'
import router from './router'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap-icons/font/bootstrap-icons.css'
import './assets/styles/colors.css'
import './assets/styles/layout.css'
import './assets/styles/forms.css'
import './assets/styles/dashboard.css'
import './assets/styles/globals.css'
import App from './App.vue'

createApp(App)
    .use(router)
    .mount('#app')
