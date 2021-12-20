require('./bootstrap');
import vue from 'vue'
window.Vue = vue;
//vue.use(require('vue-resource'));
//Vue.component('data-component', require('./components/DataComponent.vue').default);
Vue.component('pagination', require('laravel-vue-pagination'));


import App from './components/App.vue';
import VueRouter from 'vue-router';
import VueAxios from 'vue-axios';
import axios from 'axios';
import {routes} from './routes';
 
Vue.use(VueRouter);
Vue.use(VueAxios, axios);
 
const router = new VueRouter({
    mode: 'history',
    routes: routes
});
 
const app = new Vue({
    el: '#app',
    router: router,
    render: h => h(App),
});