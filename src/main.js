import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'
import { createApp } from 'vue'
import { VueReCaptcha } from 'vue-recaptcha-v3'

// Styles
import '@core-scss/template/index.scss'
import '@styles/styles.scss'

// Create vue app
const app = createApp(App)

app.use(VueReCaptcha, {
    siteKey: import.meta.env.VITE_RECAPTCHA_SITE_KEY,
    loaderOptions: {
        autoHideBadge: true,
        useRecaptchaNet: true,
        renderParameters: {
            hl: 'id'
        }
    }
})

// Register plugins
registerPlugins(app)

// Mount vue app
app.mount('#app')
