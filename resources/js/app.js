import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './src/router/index.js';
import AppShell from './src/layouts/AppShell.vue';

const app = createApp(AppShell);

app.use(createPinia());
app.use(router);

app.mount('#app');
