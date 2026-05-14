<template>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">Employee Management</h2>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <div class="flex justify-between items-center mb-4">
            <input type="text" v-model="searchQuery" placeholder="Search by name or email..." class="form-input rounded-md shadow-sm w-1/3">
            <select v-model="filterDepartment" class="form-select rounded-md shadow-sm w-1/4 ml-4">
                <option value="">All Departments</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
            </select>
            <select v-model="filterStatus" class="form-select rounded-md shadow-sm w-1/4 ml-4">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="on_leave">On Leave</option>
                <option value="terminated">Terminated</option>
            </select>
            <router-link :to="{ name: 'EmployeeCreate' }" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 ml-4">
                Add Employee
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-4">Loading employees...</div>
        <div v-else-if="error" class="text-center py-4 text-red-500">Error: {{ error.message }}</div>
        <div v-else-if="employees.length === 0" class="text-center py-4 text-gray-500">No employees found.</div>
        <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th @click="handleSort('employee_code')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Code <span v-if="sortBy === 'employee_code'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th @click="handleSort('first_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Name <span v-if="sortBy === 'first_name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th @click="handleSort('email')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Email <span v-if="sortBy === 'email'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th @click="handleSort('department_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Department <span v-if="sortBy === 'department_name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th @click="handleSort('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Status <span v-if="sortBy === 'status'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="employee in employees" :key="employee.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ employee.employee_code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ employee.first_name }} {{ employee.last_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ employee.email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ employee.department ? employee.department.name : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="{'bg-green-100 text-green-800': employee.status === 'active', 'bg-red-100 text-red-800': employee.status === 'terminated', 'bg-yellow-100 text-yellow-800': employee.status === 'on_leave'}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                            {{ employee.status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <router-link :to="{ name: 'EmployeeEdit', params: { id: employee.id } }" class="text-blue-600 hover:text-blue-900 mr-3">Edit</router-link>
                        <button @click="deleteEmployee(employee.id)" class="text-red-600 hover:text-red-900">Delete</button>
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

const employees = ref([]);
const departments = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');
const filterDepartment = ref('');
const filterStatus = ref('');
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);

const sortBy = ref('first_name');
const sortOrder = ref('asc');

const fetchEmployees = async () => {
    loading.value = true;
    error.value = null;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchQuery.value || undefined,
            department_id: filterDepartment.value || undefined,
            status: filterStatus.value || undefined,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
        };
        const response = await api.get('/employees', { params });
        employees.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
    } catch (err) {
        error.value = err;
        console.error('Error fetching employees:', err);
    } finally {
        loading.value = false;
    }
};

const fetchDepartments = async () => {
    try {
        const response = await api.get('/departments'); // Assuming departments are not paginated for dropdown
        departments.value = response.data;
    } catch (err) {
        console.error('Error fetching departments:', err);
    }
};

const handleSort = (column) => {
    if (sortBy.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortOrder.value = 'asc';
    }
    fetchEmployees(); // Re-fetch data with new sort parameters
};

const deleteEmployee = async (id) => {
    if (confirm('Are you sure you want to delete this employee?')) {
        try {
            await api.delete(`/employees/${id}`);
            fetchEmployees(); // Refresh list
        } catch (err) {
            console.error('Error deleting employee:', err);
            alert('Failed to delete employee.');
        }
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        fetchEmployees();
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetchEmployees();
    }
};

onMounted(() => {
    fetchDepartments();
    fetchEmployees();
});

watch([searchQuery, filterDepartment, filterStatus], () => {
    currentPage.value = 1; // Reset to first page on filter/search change
    fetchEmployees();
});
</script>