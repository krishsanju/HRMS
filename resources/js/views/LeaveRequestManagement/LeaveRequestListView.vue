<template>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">Leave Request Management</h2>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <div class="flex justify-between items-center mb-4">
            <input type="text" v-model="searchQuery" placeholder="Search by employee name..." class="form-input rounded-md shadow-sm w-1/3">
            <select v-model="filterStatus" class="form-select rounded-md shadow-sm w-1/4 ml-4">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <select v-model="sortBy" class="form-select rounded-md shadow-sm w-1/4 ml-4">
                <option value="from_date">Start Date</option>
                <option value="created_at">Submission Date</option>
                <option value="employee_id">Employee</option>
            </select>
            <select v-model="sortDirection" class="form-select rounded-md shadow-sm w-20 ml-2">
                <option value="asc">ASC</option>
                <option value="desc">DESC</option>
            </select>
        </div>

        <div v-if="loading" class="text-center py-4">Loading leave requests...</div>
        <div v-else-if="error" class="text-center py-4 text-red-500">Error: {{ error.message }}</div>
        <div v-else-if="leaveRequests.length === 0" class="text-center py-4 text-gray-500">No leave requests found.</div>
        <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="request in leaveRequests" :key="request.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ request.employee.first_name }} {{ request.employee.last_name }} ({{ request.employee.employee_code }})</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ request.employee.department ? request.employee.department.name : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ request.leave_type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ request.from_date }} to {{ request.to_date }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ calculateDuration(request.from_date, request.to_date) }} days</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm max-w-xs overflow-hidden text-ellipsis">{{ request.reason }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="{'bg-yellow-100 text-yellow-800': request.status === 'pending', 'bg-green-100 text-green-800': request.status === 'approved', 'bg-red-100 text-red-800': request.status === 'rejected'}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                            {{ request.status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <LeaveRequestActions :request="request" @request-updated="fetchLeaveRequests" />
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
import LeaveRequestActions from './LeaveRequestActions.vue';

const leaveRequests = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');
const filterStatus = ref('');
const sortBy = ref('from_date');
const sortDirection = ref('desc');
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);

const fetchLeaveRequests = async () => {
    loading.value = true;
    error.value = null;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchQuery.value || undefined,
            status: filterStatus.value || undefined,
            sort_by: sortBy.value,
            sort_direction: sortDirection.value,
        };
        const response = await api.get('/leaves', { params });
        leaveRequests.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
    } catch (err) {
        error.value = err;
        console.error('Error fetching leave requests:', err);
    } finally {
        loading.value = false;
    }
};

const calculateDuration = (startDate, endDate) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 to include start day
    return diffDays;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        fetchLeaveRequests();
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetchLeaveRequests();
    }
};

onMounted(() => {
    fetchLeaveRequests();
});

watch([searchQuery, filterStatus, sortBy, sortDirection], () => {
    currentPage.value = 1; // Reset to first page on filter/search/sort change
    fetchLeaveRequests();
});
</script>