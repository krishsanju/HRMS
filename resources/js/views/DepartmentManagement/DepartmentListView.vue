<template>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">Department Management</h2>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <div class="flex justify-between items-center mb-4">
            <input type="text" v-model="searchQuery" placeholder="Search departments..." class="form-input rounded-md shadow-sm w-1/3">
            <router-link :to="{ name: 'DepartmentCreate' }" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Add Department
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-4">Loading departments...</div>
        <div v-else-if="error" class="text-center py-4 text-red-500">Error: {{ error.message }}</div>
        <div v-else-if="departments.length === 0" class="text-center py-4 text-gray-500">No departments found.</div>
        <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="department in filteredDepartments" :key="department.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ department.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <router-link :to="{ name: 'DepartmentEdit', params: { id: department.id } }" class="text-blue-600 hover:text-blue-900 mr-3">Edit</router-link>
                        <button @click="deleteDepartment(department.id)" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import api from '../../api';

const departments = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');

const filteredDepartments = computed(() => {
    if (!searchQuery.value) {
        return departments.value;
    }
    return departments.value.filter(dept =>
        dept.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const fetchDepartments = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/departments');
        departments.value = response.data;
    } catch (err) {
        error.value = err;
        console.error('Error fetching departments:', err);
    } finally {
        loading.value = false;
    }
};

const deleteDepartment = async (id) => {
    if (confirm('Are you sure you want to delete this department? This will fail if employees are assigned.')) {
        try {
            await api.delete(`/departments/${id}`);
            fetchDepartments(); // Refresh list
        } catch (err) {
            console.error('Error deleting department:', err);
            alert('Failed to delete department. Ensure no employees are assigned.');
        }
    }
};

onMounted(() => {
    fetchDepartments();
});

watch(searchQuery, () => {
    // No need to re-fetch, filtering is client-side for small lists
});
</script>