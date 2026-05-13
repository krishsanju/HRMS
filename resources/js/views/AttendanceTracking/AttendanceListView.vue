<template>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">Attendance Tracking</h2>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <div class="flex justify-between items-center mb-4">
            <input type="text" v-model="searchQuery" placeholder="Search by employee name..." class="form-input rounded-md shadow-sm w-1/4">
            <select v-model="filterDepartment" class="form-select rounded-md shadow-sm w-1/5 ml-4">
                <option value="">All Departments</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
            </select>
            <input type="date" v-model="filterDateFrom" class="form-input rounded-md shadow-sm w-1/5 ml-4">
            <input type="date" v-model="filterDateTo" class="form-input rounded-md shadow-sm w-1/5 ml-4">
            <router-link :to="{ name: 'AttendanceCreate' }" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 ml-4">
                Add Entry
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-4">Loading attendance records...</div>
        <div v-else-if="error" class="text-center py-4 text-red-500">Error: {{ error.message }}</div>
        <div v-else-if="attendanceRecords.length === 0" class="text-center py-4 text-gray-500">No attendance records found.</div>
        <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="record in attendanceRecords" :key="record.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ record.employee.first_name }} {{ record.employee.last_name }} ({{ record.employee.employee_code }})</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ record.employee.department ? record.employee.department.name : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(record.check_in) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ record.check_out ? formatDate(record.check_out) : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ calculateWorkDuration(record.check_in, record.check_out) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <router-link :to="{ name: 'AttendanceEdit', params: { id: record.id } }" class="text-blue-600 hover:text-blue-900 mr-3">Edit</router-link>
                        <button @click="deleteAttendance(record.id)" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="mt-4 flex justify-between items-center">
            <button @click="prevPage" :disabled="currentPage === 1" class="px-4 py-2 bg-gray-300 rounded-md">Previous</button>
            <span>Page {{ currentPage }} of {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages" class="px-4 py-2 bg-gray-300 rounded-md">Next</button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '../../api';

const attendanceRecords = ref([]);
const departments = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');
const filterDepartment = ref('');
const filterDateFrom = ref('');
const filterDateTo = ref('');
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);

const fetchAttendanceRecords = async () => {
    loading.value = true;
    error.value = null;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchQuery.value || undefined,
            department_id: filterDepartment.value || undefined,
            date_from: filterDateFrom.value || undefined,
            date_to: filterDateTo.value || undefined,
        };
        const response = await api.get('/attendances', { params });
        attendanceRecords.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
    } catch (err) {
        error.value = err;
        console.error('Error fetching attendance records:', err);
    } finally {
        loading.value = false;
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

const deleteAttendance = async (id) => {
    if (confirm('Are you sure you want to delete this attendance record?')) {
        try {
            await api.delete(`/attendances/${id}`);
            fetchAttendanceRecords(); // Refresh list
        } catch (err) {
            console.error('Error deleting attendance record:', err);
            alert('Failed to delete attendance record.');
        }
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

const calculateWorkDuration = (checkIn, checkOut) => {
    if (!checkIn || !checkOut) return 'N/A';
    const start = new Date(checkIn);
    const end = new Date(checkOut);
    const diffMs = Math.abs(end - start);
    const hours = Math.floor(diffMs / (1000 * 60 * 60));
    const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    return `${hours}h ${minutes}m`;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        fetchAttendanceRecords();
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetchAttendanceRecords();
    }
};

onMounted(() => {
    fetchDepartments();
    fetchAttendanceRecords();
});

watch([searchQuery, filterDepartment, filterDateFrom, filterDateTo], () => {
    currentPage.value = 1; // Reset to first page on filter/search change
    fetchAttendanceRecords();
});
</script>