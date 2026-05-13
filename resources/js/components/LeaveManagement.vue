<template>
    <div class="bg-white shadow-md rounded-lg p-6">
        <h3 class="text-xl font-semibold mb-4">Leave Requests Overview</h3>

        <!-- Filters and Sorting -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="employeeNameFilter" class="block text-sm font-medium text-gray-700">Employee Name</label>
                <input type="text" id="employeeNameFilter" v-model="filters.employee_name" @input="debouncedFetchLeaves"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <div>
                <label for="departmentFilter" class="block text-sm font-medium text-gray-700">Department</label>
                <select id="departmentFilter" v-model="filters.department_id" @change="fetchLeaves"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">All Departments</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
            </div>
            <div>
                <label for="sortBy" class="block text-sm font-medium text-gray-700">Sort By</label>
                <select id="sortBy" v-model="sort.by" @change="fetchLeaves"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="submission_date">Submission Date</option>
                    <option value="employee_name">Employee Name</option>
                    <option value="start_date">Start Date</option>
                </select>
            </div>
            <div>
                <label for="sortOrder" class="block text-sm font-medium text-gray-700">Sort Order</label>
                <select id="sortOrder" v-model="sort.order" @change="fetchLeaves"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>
        </div>

        <!-- Tabs for Approved/Pending -->
        <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'pending'" :class="[activeTab === 'pending' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                    Pending Leaves ({{ pendingLeaves.length }})
                </button>
                <button @click="activeTab === 'approved'" :class="[activeTab === 'approved' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                    Approved Leaves ({{ approvedLeaves.length }})
                </button>
            </nav>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8 text-gray-500">
            Loading leave requests...
        </div>

        <!-- Error State -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ error }}</span>
        </div>

        <!-- No Data State -->
        <div v-if="!loading && !error && (activeTab === 'pending' && pendingLeaves.length === 0 || activeTab === 'approved' && approvedLeaves.length === 0)" class="text-center py-8 text-gray-500">
            No {{ activeTab }} leave requests found.
        </div>

        <!-- Leave Lists -->
        <div v-if="!loading && !error">
            <div v-show="activeTab === 'pending'">
                <LeaveTable :leaves="pendingLeaves" />
            </div>
            <div v-show="activeTab === 'approved'">
                <LeaveTable :leaves="approvedLeaves" />
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="mt-6 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button @click="changePage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    Previous
                </button>
                <button v-for="page in pagination.last_page" :key="page" @click="changePage(page)"
                        :class="[page === pagination.current_page ? 'bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50', 'relative inline-flex items-center px-4 py-2 border text-sm font-medium']">
                    {{ page }}
                </button>
                <button @click="changePage(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    Next
                </button>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import LeaveTable from './LeaveTable.vue'; // Assuming a sub-component for the table structure

const activeTab = ref('pending');
const pendingLeaves = ref([]);
const approvedLeaves = ref([]);
const departments = ref([]); // To populate department filter
const loading = ref(false);
const error = ref(null);
const filters = ref({
    employee_name: '',
    department_id: '',
});
const sort = ref({
    by: 'submission_date',
    order: 'desc',
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    prev_page_url: null,
    next_page_url: null,
    total: 0,
});

// Debounce function for employee name filter
let debounceTimer = null;
const debouncedFetchLeaves = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchLeaves();
    }, 300); // 300ms debounce
};

const fetchLeaves = async (page = 1) => {
    loading.value = true;
    error.value = null;
    try {
        const params = {
            page: page,
            sort_by: sort.value.by,
            sort_order: sort.value.order,
            employee_name: filters.value.employee_name,
            department_id: filters.value.department_id,
        };

        // Fetch pending leaves
        const pendingResponse = await axios.get('/api/leaves', { params: { ...params, status: 'pending' } });
        pendingLeaves.value = pendingResponse.data.data;

        // Fetch approved leaves
        const approvedResponse = await axios.get('/api/leaves', { params: { ...params, status: 'approved' } });
        approvedLeaves.value = approvedResponse.data.data;

        // Update pagination from one of the responses (assuming both have same pagination structure)
        // For simplicity, we'll use pendingResponse's pagination. In a real app, you might fetch pagination once
        // or handle it per tab if pagination is independent.
        pagination.value = {
            current_page: pendingResponse.data.current_page,
            last_page: pendingResponse.data.last_page,
            prev_page_url: pendingResponse.data.prev_page_url,
            next_page_url: pendingResponse.data.next_page_url,
            total: pendingResponse.data.total,
        };

    } catch (err) {
        console.error('Failed to fetch leave requests:', err);
        error.value = 'Failed to load leave requests. Please try again.';
    } finally {
        loading.value = false;
    }
};

const fetchDepartments = async () => {
    try {
        const response = await axios.get('/api/departments');
        departments.value = response.data;
    } catch (err) {
        console.error('Failed to fetch departments:', err);
        // Handle error, maybe display a message
    }
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchLeaves(page);
    }
};

onMounted(() => {
    fetchDepartments();
    fetchLeaves();
});

// Watch for changes in activeTab to re-fetch if needed (though current implementation fetches both)
// watch(activeTab, () => {
//     fetchLeaves();
// });
</script>