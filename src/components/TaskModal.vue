<template>
  <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ task.title }}</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <p><strong>Description:</strong> {{ task.description }}</p>
          <p><strong>Priority:</strong> {{ task.priority }}</p>
          <p><strong>Due Date:</strong> {{ task.due_date }}</p>
          <p><strong>Assignee:</strong> {{ task.assignee_name }}</p>
          <div>
            <strong>Tags:</strong>
            <span v-for="tag in task.tags?.split(',')" :key="tag" class="badge bg-info ms-1">{{ tag }}</span>
          </div>
          <hr>
          <h6>Comments</h6>
          <div class="comments-section mb-3">
            <div v-for="comment in commentStore.comments" :key="comment.id" class="mb-2">
              <strong>{{ comment.username }}</strong> <small>at {{ new Date(comment.created_at).toLocaleString() }}</small>
              <p>{{ comment.content }}</p>
            </div>
          </div>
          <form @submit.prevent="addComment">
            <div class="form-group">
              <textarea v-model="newComment" class="form-control" placeholder="Add a comment..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm mt-2">Add Comment</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useCommentStore } from '../stores/comment';
import { onMounted, ref } from 'vue';

export default {
  props: {
    task: { type: Object, required: true },
  },
  emits: ['close'],
  setup(props) {
    const commentStore = useCommentStore();
    const newComment = ref('');

    onMounted(() => {
      commentStore.fetchComments(props.task.id);
    });

    const addComment = async () => {
      if (!newComment.value) return;
      await commentStore.createComment(props.task.id, { content: newComment.value });
      newComment.value = '';
    };

    return { commentStore, newComment, addComment };
  },
};
</script>

<style scoped>
.comments-section {
    max-height: 300px;
    overflow-y: auto;
}
</style>
