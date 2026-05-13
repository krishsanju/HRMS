<template>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">{{ isEdit ? 'Edit Department' : 'Add Department' }}</h2>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <form @submit.prevent="saveDepartment">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Department Name</label>
                <input type="text" id="name" v-model="department.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
            </div>

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 mr-2">
                    {{ isEdit ? 'Update Department' : 'Create Department' }}
                </button>
                <button type="button" @click="$router.back()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const department = ref({
    name: '',
});

const errors = ref({});

const fetchDepartment = async (id) => {
    try {
        const response = await api.get(`/departments/${id}`);
        department.value = response.data;
    } catch (err) {
        console.error('Error fetching department:', err);
        alert('Failed to load department data.');
        router.push({ name: 'DepartmentList' });
    }
};

const saveDepartment = async () => {
    errors.value = {}; // Clear previous errors
    try {
        if (isEdit.value) {
            await api.put(`/departments/${route.params.id}`, department.value);
        } else {
            await api.post('/departments', department.value);
        }
        router.push({ name: 'DepartmentList' });
    } catch (err) {
        if (err.response && err.response.status === 422) {
            errors.value = err.response.data.errors;
        } else {
            console.error('Error saving department:', err);
            alert('Failed to save department.');
        }
    }
};

onMounted(() => {
    if (isEdit.value) {
        fetchDepartment(route.params.id);
    }
});
</script>