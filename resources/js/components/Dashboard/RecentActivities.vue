<template>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-medium text-gray-700 mb-4">Recent Activities</h3>
        <p v-if="loading" class="text-gray-500">Loading recent activities...</p>
        <ul v-else-if="activities.length" class="space-y-4">
            <li v-for="activity in activities" :key="activity.id">
                <router-link :to="getSubjectLink(activity)" class="block hover:bg-gray-50 p-3 rounded-md transition-colors">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-800">
                            <span class="font-semibold">{{ activity.user.name }}</span>
                            {{ getActivityText(activity.activity_type) }}
                            <span class="font-semibold">{{ getSubjectName(activity) }}</span>.
                        </p>
                        <p class="text-xs text-gray-500 flex-shrink-0 ml-4">{{ formatRelativeTime(activity.created_at) }}</p>
                    </div>
                </router-link>
            </li>
        </ul>
        <p v-else class="text-gray-500">No recent activities.</p>
        <!-- A 'View All' link can be added here -->
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

defineProps({
    activities: {
        type: Array,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const activityTextMap = {
    'employee_created': 'added a new employee:',
    'employee_updated': 'updated the details for',
    'department_created': 'created a new department:',
    'leave_request_submitted': 'submitted a leave request for',
    'leave_request_approved': 'approved a leave request for',
    'leave_request_rejected': 'rejected a leave request for',
};

const getActivityText = (type) => activityTextMap[type] || 'performed an action on';

const getSubjectName = (activity) => {
    if (!activity.subject) return '[Deleted]';
    switch (activity.subject_type) {
        case 'App\Models\Employee':
            return `${activity.subject.first_name} ${activity.subject.last_name}`;
        case 'App\Models\Department':
            return activity.subject.name;
        case 'App\Models\LeaveRequest':
            return `${activity.subject.employee.first_name} ${activity.subject.employee.last_name}`;
        default:
            return 'a record';
    }
};

const getSubjectLink = (activity) => {
    if (!activity.subject) return '#';
    switch (activity.subject_type) {
        case 'App\Models\Employee':
            return { name: 'EmployeeEdit', params: { id: activity.subject.id } };
        case 'App\Models\Department':
            return { name: 'DepartmentEdit', params: { id: activity.subject.id } };
        case 'App\Models\LeaveRequest':
            return { name: 'LeaveRequestList' }; // Or a detail view if it exists
        default:
            return '#';
    }
};

const formatRelativeTime = (timestamp) => {
    const now = new Date();
    const past = new Date(timestamp);
    const diffInSeconds = Math.floor((now - past) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
    const diffInDays = Math.floor(diffInHours / 24);
    return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
};
</script>