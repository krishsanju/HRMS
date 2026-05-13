<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="px-8 py-6 mt-4 text-left bg-white shadow-lg rounded-lg w-full max-w-md">
            <h3 class="text-2xl font-bold text-center">HR Admin Panel Registration</h3>
            <form @submit.prevent="handleRegister">
                <div class="mt-4">
                    <div>
                        <label class="block" for="name">Name</label>
                        <input id="name" type="text" placeholder="Name" class="w-full px-4 py-2 mt-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-600"
                            v-model="form.name" required>
                    </div>
                    <div class="mt-4">
                        <label class="block" for="email">Email</label>
                        <input id="email" type="email" placeholder="Email" class="w-full px-4 py-2 mt-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-600"
                            v-model="form.email" required>
                    </div>
                    <div class="mt-4">
                        <label class="block" for="password">Password</label>
                        <input id="password" type="password" placeholder="Password" class="w-full px-4 py-2 mt-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-600"
                            v-model="form.password" required>
                    </div>
                    <div class="mt-4">
                        <label class="block" for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" type="password" placeholder="Confirm password" class="w-full px-4 py-2 mt-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-600"
                            v-model="form.password_confirmation" required>
                    </div>
                    <div v-if="authStore.error" class="text-red-500 text-sm mt-2">
                        {{ authStore.error }}
                    </div>
                    <div class="flex items-baseline justify-between">
                        <button type="submit" class="px-6 py-2 mt-4 text-white bg-blue-600 rounded-lg hover:bg-blue-900"
                            :disabled="authStore.loading">
                            {{ authStore.loading ? 'Creating...' : 'Register' }}
                        </button>
                        <router-link :to="{ name: 'Login' }" class="text-sm text-blue-600 hover:underline">
                            Back to login
                        </router-link>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store';

const authStore = useAuthStore();
const router = useRouter();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const handleRegister = async () => {
    const success = await authStore.register(form.value);
    if (success) {
        router.push({ name: 'Dashboard' });
    }
};
</script>
