<template>
    <header class="flex items-center justify-between px-6 py-4 bg-white border-b-4 border-blue-600">
        <div class="flex items-center">
            <button @click="toggleSidebar" class="text-gray-500 focus:outline-none lg:hidden">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                </svg>
            </button>
            <div class="relative mx-4 lg:mx-0">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        </path>
                    </svg>
                </span>
                <input class="form-input w-32 sm:w-64 rounded-md pl-10 pr-4 focus:border-blue-600" type="text"
                    placeholder="Search">
            </div>
        </div>

        <div class="flex items-center">
            <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="relative block h-8 w-8 rounded-full overflow-hidden shadow focus:outline-none">
                    <img class="h-full w-full object-cover" src="https://ui-avatars.com/api/?name=HR+Admin&background=0D8ABC&color=fff" alt="Your avatar">
                </button>
                <div v-show="dropdownOpen" @click="dropdownOpen = false" class="fixed inset-0 z-10"></div>

                <div v-show="dropdownOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-md overflow-hidden shadow-xl z-10">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-600 hover:text-white">Profile</a>
                    <a href="#" @click.prevent="handleLogout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-600 hover:text-white">Logout</a>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store';

const dropdownOpen = ref(false);
const authStore = useAuthStore();
const router = useRouter();

const toggleSidebar = () => {
    // Implement sidebar toggle logic if needed, e.g., emit event to parent or use global state
    console.log('Toggle sidebar');
};

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'Login' });
};
</script>