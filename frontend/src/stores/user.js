import { defineStore } from 'pinia';
import api from '../api';
import { useAuthStore } from './auth';

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
  }),
  actions: {
    async fetchUsers() {
      const authStore = useAuthStore();
      if (!authStore.token) return;

      try {
        const response = await api.get('/users', {
          headers: { Authorization: `Bearer ${authStore.token}` },
        });
        this.users = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
  },
});