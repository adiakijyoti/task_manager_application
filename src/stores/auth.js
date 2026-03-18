import { defineStore } from 'pinia';
import api from '../api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false,
    token: null,
    csrfToken: null,
  }),
  actions: {
    async login(email, password) {
      try {
        const response = await api.post('/users/login', {
          email,
          password,
        });
        const data = JSON.parse(response.data);
        if (data.token) {
          this.token = data.token;
          this.csrfToken = data.csrf_token;
          this.isAuthenticated = true;
          localStorage.setItem('token', data.token);
          localStorage.setItem('csrf_token', data.csrf_token);
          api.defaults.headers.common['X-CSRF-TOKEN'] = data.csrf_token;
        }
      } catch (error) {
        console.error(error);
        this.isAuthenticated = false;
      }
    },
    logout() {
      this.token = null;
      this.csrfToken = null;
      this.isAuthenticated = false;
      this.user = null;
      localStorage.removeItem('token');
      localStorage.removeItem('csrf_token');
      delete api.defaults.headers.common['X-CSRF-TOKEN'];
    },
    checkAuth() {
        const token = localStorage.getItem('token');
        const csrfToken = localStorage.getItem('csrf_token');
        if (token && csrfToken) {
            this.token = token;
            this.csrfToken = csrfToken;
            this.isAuthenticated = true;
            api.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        }
    }
  },
});