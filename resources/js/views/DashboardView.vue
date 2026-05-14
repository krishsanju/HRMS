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

    <div class="mt-8">
        <RecentActivities :activities="recentActivities" :loading="loading" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../api';
import RecentActivities from '../components/Dashboard/RecentActivities.vue';

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

const fetchDashboardData = async () => {
    try {
        const [metricsRes, activitiesRes] = await Promise.all([
            api.get('/dashboard/metrics'),
            api.get('/activities')
        ]);
        metrics.value = metricsRes.data;
        recentActivities.value = activitiesRes.data;
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchDashboardData();

    window.Echo.channel('activities')
        .listen('ActivityLogged', (e) => {
            console.log('New activity received:', e.activity);
            recentActivities.value.unshift(e.activity);
            if (recentActivities.value.length > 10) {
                recentActivities.value.pop();
            }
        });
});

onUnmounted(() => {
    window.Echo.leave('activities');
});
</script>