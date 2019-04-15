require('./bootstrap');

window.Vue = require('vue');
window.swal = require('sweetalert2');

import vSelect from 'vue-select'

import es from 'vee-validate/dist/locale/es';
import VeeValidate, { Validator } from 'vee-validate';
// Localize takes the locale object as the second argument (optional) and merges it.
Validator.localize('es', es);
// Install the Plugin.
Vue.use(VeeValidate);

Vue.component('v-select', vSelect)
Vue.component('minuta-component', require('./components/MinutaComponent.vue'));

import Element from 'element-ui'
import locale from 'element-ui/lib/locale/lang/en'
import 'element-ui/lib/theme-chalk/index.css';
Vue.use(Element, {locale})

// const app = new Vue({
//     el: '#app'
// });
