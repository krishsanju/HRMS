import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia, defineStore } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import Login from './Login.vue';

// Mock the auth store
const useAuthStore = defineStore('auth', {
  state: () => ({
    loading: false,
    error: null,
  }),
  actions: {
    async login() {
      return true;
    },
  },
});

// Mock routes for the router
const routes = [
  { path: '/login', name: 'Login', component: Login },
  { path: '/admin/dashboard', name: 'Dashboard', component: { template: '<div>Dashboard</div>' } },
  { path: '/register', name: 'Register', component: { template: '<div>Register</div>' } },
];

describe('Login.vue', () => {
  let router;

  beforeEach(() => {
    // Set up a fresh Pinia instance for each test
    setActivePinia(createPinia());

    // Create a new router instance for each test
    router = createRouter({
      history: createWebHistory(),
      routes,
    });
  });

  it('renders the login form correctly', async () => {
    const wrapper = mount(Login, {
      global: {
        plugins: [router],
        stubs: {
          // Stub router-link to prevent issues with rendering
          RouterLink: true,
        },
      },
    });

    // Check for the title
    expect(wrapper.find('h2').text()).toBe('Login to HR Admin Panel');

    // Check for input fields by their labels and types
    const emailInput = wrapper.find('input[type="email"]');
    expect(emailInput.exists()).toBe(true);

    const passwordInput = wrapper.find('input[type="password"]');
    expect(passwordInput.exists()).toBe(true);

    // Check for the submit button
    const submitButton = wrapper.find('button[type="submit"]');
    expect(submitButton.exists()).toBe(true);
    expect(submitButton.text()).toBe('Login');

    // Check for the link to the registration page
    expect(wrapper.find('a[href="/register"]').exists()).toBe(true);
  });

  it('displays an error message on login failure', async () => {
    const authStore = useAuthStore();
    authStore.error = 'Invalid credentials.';

    const wrapper = mount(Login, {
      global: {
        plugins: [router],
      },
    });

    await wrapper.vm.$nextTick(); // Wait for DOM update

    const errorDiv = wrapper.find('[data-testid="login-error"]');
    expect(errorDiv.exists()).toBe(true);
    expect(errorDiv.text()).toContain('Invalid credentials.');
  });
});