import './bootstrap';
import { createApp } from 'vue';
import ProductList from './components/ProductList.vue';

const app = createApp(ProductList);

app.mount('#app');
