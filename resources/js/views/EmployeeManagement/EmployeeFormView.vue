<template>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">{{ isEdit ? 'Edit Employee' : 'Add Employee' }}</h2>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <form @submit.prevent="saveEmployee">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="employee_code" class="block text-sm font-medium text-gray-700">Employee Code</label>
                    <input type="text" id="employee_code" v-model="employee.employee_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p v-if="errors.employee_code" class="text-red-500 text-xs mt-1">{{ errors.employee_code[0] }}</p>
                </div>
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" id="first_name" v-model="employee.first_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">{{ errors.first_name[0] }}</p>
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" id="last_name" v-model="employee.last_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">{{ errors.last_name[0] }}</p>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" v-model="employee.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
                </div>
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="department_id" v-model="employee.department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Department</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>
                    <p v-if="errors.department_id" class="text-red-500 text-xs mt-1">{{ errors.department_id[0] }}</p>
                </div>
                <div>
                    <label for="joining_date" class="block text-sm font-medium text-gray-700">Joining Date</label>
                    <input type="date" id="joining_date" v-model="employee.joining_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p v-if="errors.joining_date" class="text-red-500 text-xs mt-1">{{ errors.joining_date[0] }}</p>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" v-model="employee.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="on_leave">On Leave</option>
                        <option value="terminated">Terminated</option>
                    </select>
                    <p v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status[0] }}</p>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 mr-2">
                    {{ isEdit ? 'Update Employee' : 'Create Employee' }}
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

const employee = ref({
    employee_code: '',
    first_name: '',
    last_name: '',
    email: '',
    department_id: '',
    joining_date: '',
    status: 'active',
});

const departments = ref([]);
const errors = ref({});

const fetchEmployee = async (id) => {
    try {
        const response = await api.get(`/employees/${id}`);
        employee.value = response.data;
    } catch (err) {
        console.error('Error fetching employee:', err);
        alert('Failed to load employee data.');
        router.push({ name: 'EmployeeList' });
    }
};

const fetchDepartments = async () => {
    try {
        const response = await api.get('/departments');
        departments.value = response.data;
    } catch (err) {
        console.error('Error fetching departments:', err);
    }
};

const saveEmployee = async () => {
    errors.value = {}; // Clear previous errors
    try {
        if (isEdit.value) {
            await api.put(`/employees/${route.params.id}`, employee.value);
        } else {
            await api.post('/employees', employee.value);
        }
        router.push({ name: 'EmployeeList' });
    } catch (err) {
        if (err.response && err.response.status === 422) {
            errors.value = err.response.data.errors;
        } else {
            console.error('Error saving employee:', err);
            alert('Failed to save employee.');
        }
    }
};

onMounted(() => {
    fetchDepartments();
    if (isEdit.value) {
        fetchEmployee(route.params.id);
    }
});
</script>