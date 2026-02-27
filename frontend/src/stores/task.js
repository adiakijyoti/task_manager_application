import { defineStore } from 'pinia';
import api from '../api';
import { useAuthStore } from './auth';

export const useTaskStore = defineStore('task', {
  state: () => ({
    tasks: [],
  }),
  actions: {
    async fetchTasks(projectId) {
      const authStore = useAuthStore();
      if (!authStore.token) return;

      try {
        const response = await api.get(`/projects/${projectId}/tasks`, {
          headers: { Authorization: `Bearer ${authStore.token}` },
        });
        this.tasks = response.data;
      } catch (error) {
        console.error('Error fetching tasks:', error);
      }
    },
    async fetchAllTasks() {
        const authStore = useAuthStore();
        if (!authStore.token) return;

        try {
            const response = await api.get(`/tasks`, {
                headers: { Authorization: `Bearer ${authStore.token}` },
            });
            // We can reuse the same tasks state property for the dashboard
            this.tasks = response.data;
        } catch (error) {
            console.error('Error fetching all tasks:', error);
        }
    },
    async createTask(projectId, taskData) {
        const authStore = useAuthStore();
        if (!authStore.token) return;

        try {
            await api.post(`/projects/${projectId}/tasks`, taskData, {
                headers: { Authorization: `Bearer ${authStore.token}` },
            });
            await this.fetchTasks(projectId); // Refresh the list
        } catch (error) {
            console.error('Error creating task:', error);
        }
    },
    async updateTask(taskId, taskData) {
        const authStore = useAuthStore();
        if (!authStore.token) return;

        try {
            await api.put(`/tasks/${taskId}`, taskData, {
                headers: { Authorization: `Bearer ${authStore.token}` },
            });
        } catch (error) {
            console.error('Error updating task:', error);
        }
    },
  },
});