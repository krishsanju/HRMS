import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../store';

import Login from '../components/Auth/Login.vue';
import AdminLayout from '../components/Layout/AdminLayout.vue';
import DashboardView from '../views/DashboardView.vue';
import EmployeeListView from '../views/EmployeeManagement/EmployeeListView.vue';
import EmployeeFormView from '../views/EmployeeManagement/EmployeeFormView.vue';
import DepartmentListView from '../views/DepartmentManagement/DepartmentListView.vue';
import DepartmentFormView from '../views/DepartmentManagement/DepartmentFormView.vue';
import LeaveRequestListView from '../views/LeaveRequestManagement/LeaveRequestListView.vue';
import AttendanceListView from '../views/AttendanceTracking/AttendanceListView.vue';
import AttendanceFormView from '../views/AttendanceTracking/AttendanceFormView.vue';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/admin',
        name: 'Admin',
        component: AdminLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: 'dashboard',
                name: 'Dashboard',
                component: DashboardView,
                meta: { requiresAuth: true }
            },
            {
                path: 'employees',
                name: 'EmployeeList',
                component: EmployeeListView,
                meta: { requiresAuth: true }
            },
            {
                path: 'employees/create',
                name: 'EmployeeCreate',
                component: EmployeeFormView,
                meta: { requiresAuth: true }
            },
            {
                path: 'employees/edit/:id',
                name: 'EmployeeEdit',
                component: EmployeeFormView,
                props: true,
                meta: { requiresAuth: true }
            },
            {
                path: 'departments',
                name: 'DepartmentList',
                component: DepartmentListView,
                meta: { requiresAuth: true }
            },
            {
                path: 'departments/create',
                name: 'DepartmentCreate',
                component: DepartmentFormView,
                meta: { requiresAuth: true }
            },
            {
                path: 'departments/edit/:id',
                name: 'DepartmentEdit',
                component: DepartmentFormView,
                props: true,
                meta: { requiresAuth: true }
            },
            {
                path: 'leave-requests',
                name: 'LeaveRequestList',
                component: LeaveRequestListView,
                meta: { requiresAuth: true }
            },
            {
                path: 'attendance',
                name: 'AttendanceList',
                component: AttendanceListView,
                meta: { requiresAuth: true }
            },
            {
                path: 'attendance/create',
                name: 'AttendanceCreate',
                component: AttendanceFormView,
                meta: { requiresAuth: true }
            },
            {
                path: 'attendance/edit/:id',
                name: 'AttendanceEdit',
                component: AttendanceFormView,
                props: true,
                meta: { requiresAuth: true }
            },
        ]
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/admin/dashboard' // Redirect unknown paths to dashboard if authenticated, or login if not
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'Login' });
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'Dashboard' });
    } else {
        next();
    }
});

export default router;