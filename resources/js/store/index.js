import { defineStore } from 'pinia';
import api from '../api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('authToken') || null,
        user: JSON.parse(localStorage.getItem('authUser')) || null,
        loading: false,
        error: null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user && state.user.role === 'admin', // Assuming a 'role' field in user object
    },
    actions: {
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/login', credentials);
                this.token = response.data.token;
                this.user = response.data.user; // Assuming API returns user data
                localStorage.setItem('authToken', this.token);
                localStorage.setItem('authUser', JSON.stringify(this.user));
                api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                return true;
            } catch (error) {
                this.error = error.response?.data?.message || 'Login failed';
                console.error('Login error:', error);
                return false;
            } finally {
                this.loading = false;
            }
        },
        async register(payload) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/register', payload);
                this.token = response.data.token;
                this.user = response.data.user;
                localStorage.setItem('authToken', this.token);
                localStorage.setItem('authUser', JSON.stringify(this.user));
                api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                return true;
            } catch (error) {
                const errors = error.response?.data?.errors;
                this.error = errors ? Object.values(errors).flat()[0] : error.response?.data?.message || 'Registration failed';
                console.error('Registration error:', error);
                return false;
            } finally {
                this.loading = false;
            }
        },
        async logout() {
            this.loading = true;
            try {
                await api.post('/logout');
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('authToken');
                localStorage.removeItem('authUser');
                delete api.defaults.headers.common['Authorization'];
                this.loading = false;
            }
        },
        initializeAuth() {
            if (this.token) {
                api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            }
        }
    },
});
