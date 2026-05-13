<template>
    <router-view />
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './store';

// Initialize auth state on app load
onMounted(() => {
    const authStore = useAuthStore();
    authStore.initializeAuth();
});
</script>