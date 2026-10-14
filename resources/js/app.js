import '../css/app.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { useAuth } from './stores/auth';

const { fetchUser } = useAuth();

fetchUser().finally(() => {
    const app = createApp(App);
    app.use(router);
    app.mount('#app');
});
