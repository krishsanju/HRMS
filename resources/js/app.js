import './bootstrap';
import { createApp } from 'vue';
import LeaveManagement from './components/LeaveManagement.vue';

const app = createApp({});

app.component('leave-management', LeaveManagement);

app.mount('#app');