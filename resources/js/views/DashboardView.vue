<template>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">Dashboard</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-medium text-gray-700">Total Employees</h3>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ metrics.totalEmployees }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-medium text-gray-700">Active Employees</h3>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ metrics.activeEmployees }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-medium text-gray-700">Pending Leave Requests</h3>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ metrics.pendingLeaveRequests }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-medium text-gray-700">Approved Leave Requests</h3>
            <p class="text-3xl font-bold text-teal-600 mt-2">{{ metrics.approvedLeaveRequests }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-medium text-gray-700">Today's Check-ins</h3>
            <p class="text-3xl font-bold text-purple-600 mt-2">{{ metrics.todayCheckIns }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-medium text-gray-700">Departments</h3>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ metrics.totalDepartments }}</p>
        </div>
    </div>

    <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-medium text-gray-700 mb-4">Recent Activities</h3>
        <p v-if="loading" class="text-gray-500">Loading recent activities...</p>
        <ul v-else-if="recentActivities.length">
            <li v-for="activity in recentActivities" :key="activity.id" class="border-b py-2 last:border-b-0">
                <span class="font-medium">{{ activity.type }}:</span> {{ activity.description }} - <span class="text-gray-500 text-sm">{{ activity.date }}</span>
            </li>
        </ul>
        <p v-else class="text-gray-500">No recent activities.</p>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';

const metrics = ref({
    totalEmployees: 0,
    activeEmployees: 0,
    pendingLeaveRequests: 0,
    approvedLeaveRequests: 0,
    todayCheckIns: 0,
    totalDepartments: 0,
});

const recentActivities = ref([]);
const loading = ref(true);

const fetchDashboardMetrics = async () => {
    try {
        const response = await api.get('/dashboard/metrics');
        metrics.value = response.data;
        // Assuming recent activities are part of dashboard metrics or a separate endpoint
        recentActivities.value = response.data.recentActivities || [];
    } catch (error) {
        console.error('Error fetching dashboard metrics:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchDashboardMetrics();
});
</script>