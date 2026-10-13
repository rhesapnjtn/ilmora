import { createInertiaApp } from '@inertiajs/inertia-vue3'

import './bootstrap'

createInertiaApp({
    resolve: (name) => import(`./pages/${name}.js`),
    title: (title) => `${title} - Ilmora`,
    progress: {
        color: '#3F51B5',
        showSpinner: false,
    },
})