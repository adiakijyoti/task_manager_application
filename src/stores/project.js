import { defineStore } from 'pinia';
import api from '../api';
import { useAuthStore } from './auth';

export const useProjectStore = defineStore('project', {
  state: () => ({
    projects: [],
  }),
  actions: {
    async fetchProjects() {
      const authStore = useAuthStore();
      if (!authStore.token) return;

      try {
        const response = await api.get('/projects', {
          headers: { Authorization: `Bearer ${authStore.token}` },
        });
        this.projects = response.data;
      } catch (error) {
        console.error('Error fetching projects:', error);
      }
    },
    async createProject(projectData) {
        const authStore = useAuthStore();
        if (!authStore.token) return;

        try {
            await api.post('/projects', projectData, {
                headers: { Authorization: `Bearer ${authStore.token}` },
            });
            await this.fetchProjects(); // Refresh the list
        } catch (error) {
            console.error('Error creating project:', error);
        }
    },
  },
});