import { defineStore } from 'pinia';
import api from '../api';
import { useAuthStore } from './auth';

export const useCommentStore = defineStore('comment', {
  state: () => ({
    comments: [],
  }),
  actions: {
    async fetchComments(taskId) {
      const authStore = useAuthStore();
      if (!authStore.token) return;

      try {
        const response = await api.get(`/tasks/${taskId}/comments`, {
          headers: { Authorization: `Bearer ${authStore.token}` },
        });
        this.comments = response.data;
      } catch (error) {
        console.error('Error fetching comments:', error);
      }
    },
    async createComment(taskId, commentData) {
        const authStore = useAuthStore();
        if (!authStore.token) return;

        try {
            await api.post(`/tasks/${taskId}/comments`, commentData, {
                headers: { Authorization: `Bearer ${authStore.token}` },
            });
            await this.fetchComments(taskId); // Refresh the list
        } catch (error) {
            console.error('Error creating comment:', error);
        }
    },
  },
});