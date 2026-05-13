<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar />
        <div class="flex-1 flex flex-col overflow-hidden">
            <Header />
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from './Sidebar.vue';
import Header from './Header.vue';
</script>