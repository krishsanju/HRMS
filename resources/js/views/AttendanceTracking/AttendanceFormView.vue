<template>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">{{ isEdit ? 'Edit Attendance' : 'Add Attendance' }}</h2>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <form @submit.prevent="saveAttendance">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                    <select id="employee_id" v-model="attendance.employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Employee</option>
                        <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.first_name }} {{ emp.last_name }} ({{ emp.employee_code }})</option>
                    </select>
                    <p v-if="errors.employee_id" class="text-red-500 text-xs mt-1">{{ errors.employee_id[0] }}</p>
                </div>
                <div>
                    <label for="check_in" class="block text-sm font-medium text-gray-700">Check-in Time</label>
                    <input type="datetime-local" id="check_in" v-model="attendance.check_in" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p v-if="errors.check_in" class="text-red-500 text-xs mt-1">{{ errors.check_in[0] }}</p>
                </div>
                <div>
                    <label for="check_out" class="block text-sm font-medium text-gray-700">Check-out Time (Optional)</label>
                    <input type="datetime-local" id="check_out" v-model="attendance.check_out" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p v-if="errors.check_out" class="text-red-500 text-xs mt-1">{{ errors.check_out[0] }}</p>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 mr-2">
                    {{ isEdit ? 'Update Attendance' : 'Add Attendance' }}
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

const attendance = ref({
    employee_id: '',
    check_in: '',
    check_out: null,
});

const employees = ref([]);
const errors = ref({});

const fetchAttendance = async (id) => {
    try {
        const response = await api.get(`/attendances/${id}`);
        // Format dates for datetime-local input
        attendance.value = {
            ...response.data,
            check_in: response.data.check_in ? new Date(response.data.check_in).toISOString().slice(0, 16) : '',
            check_out: response.data.check_out ? new Date(response.data.check_out).toISOString().slice(0, 16) : null,
        };
    } catch (err) {
        console.error('Error fetching attendance:', err);
        alert('Failed to load attendance data.');
        router.push({ name: 'AttendanceList' });
    }
};

const fetchEmployees = async () => {
    try {
        // Fetch all employees, potentially paginated if list is very large
        const response = await api.get('/employees-paginated', { params: { per_page: -1 } }); // Fetch all for dropdown
        employees.value = response.data.data;
    } catch (err) {
        console.error('Error fetching employees:', err);
    }
};

const saveAttendance = async () => {
    errors.value = {}; // Clear previous errors
    try {
        const payload = {
            ...attendance.value,
            // Ensure check_out is null if empty string
            check_out: attendance.value.check_out || null
        };

        if (isEdit.value) {
            await api.put(`/attendances/${route.params.id}`, payload);
        } else {
            await api.post('/attendances', payload);
        }
        router.push({ name: 'AttendanceList' });
    } catch (err) {
        if (err.response && err.response.status === 422) {
            errors.value = err.response.data.errors;
        } else {
            console.error('Error saving attendance:', err);
            alert('Failed to save attendance.');
        }
    }
};

onMounted(() => {
    fetchEmployees();
    if (isEdit.value) {
        fetchAttendance(route.params.id);
    }
});
</script>