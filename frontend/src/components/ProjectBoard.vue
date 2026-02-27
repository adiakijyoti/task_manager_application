<template>
  <div class="container-fluid mt-4">
    <h3>{{ project.name }}</h3>
    <p>{{ project.description }}</p>
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" v-model="searchQuery" class="form-control" placeholder="Search tasks...">
        </div>
        <div class="col-md-3">
            <select v-model="selectedPriority" class="form-select">
                <option value="">All Priorities</option>
                <option>Low</option>
                <option>Medium</option>
                <option>High</option>
            </select>
        </div>
        <div class="col-md-3">
            <select v-model="selectedAssignee" class="form-select">
                <option :value="null">All Assignees</option>
                <option v-for="user in userStore.users" :key="user.id" :value="user.id">{{ user.username }}</option>
            </select>
        </div>
    </div>
    <hr>
    <div class="row">
        <TaskModal v-if="isModalVisible" :task="selectedTask" @close="closeTaskModal" />
      <!-- To Do Column -->
      <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card">
          <div class="card-header bg-light">To Do</div>
          <div class="card-body">
            <draggable class="list-group" :list="todoTasks" group="tasks" @change="handleChange($event, 'To Do')" item-key="id">
              <template #item="{element}">
                <div class="card task-card mb-2" @click="openTaskModal(element)">
                  <div class="card-body">
                    <h5 class="card-title">{{ element.title }}</h5>
                    <p class="card-text">{{ element.description }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span v-if="element.due_date" class="badge bg-secondary me-1">{{ element.due_date }}</span>
                            <span class="badge bg-primary me-1">{{ element.priority }}</span>
                            <span v-if="element.assignee_name" class="badge bg-success">{{ element.assignee_name }}</span>
                        </div>
                        <div>
                            <span v-for="tag in element.tags?.split(',')" :key="tag" class="badge bg-info ms-1">{{ tag }}</span>
                        </div>
                    </div>
                  </div>
                </div>
              </template>
            </draggable>
            <!-- Add task form -->
            <form @submit.prevent="createTask('To Do')" class="mt-3">
                <div class="form-group">
                    <input type="text" v-model="newTask.title" class="form-control" placeholder="New task title" required>
                </div>
                <div class="form-group mt-2">
                    <textarea v-model="newTask.description" class="form-control" placeholder="Task description"></textarea>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <select v-model="newTask.priority" class="form-select">
                            <option>Low</option>
                            <option selected>Medium</option>
                            <option>High</option>
                        </select>
                    </div>
                    <div class="col">
                        <input type="date" v-model="newTask.due_date" class="form-control">
                    </div>
                </div>
                <div class="form-group mt-2">
                    <input type="text" v-model="newTask.tags" class="form-control" placeholder="Tags (comma-separated)">
                </div>
                <div class="form-group mt-2">
                     <select v-model="newTask.assignee_id" class="form-select">
                        <option :value="null">Unassigned</option>
                        <option v-for="user in userStore.users" :key="user.id" :value="user.id">{{ user.username }}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm mt-2">Add Task</button>
            </form>
          </div>
        </div>
      </div>

      <!-- In Progress Column -->
      <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card">
          <div class="card-header bg-light">In Progress</div>
          <div class="card-body">
            <draggable class="list-group" :list="inProgressTasks" group="tasks" @change="handleChange($event, 'In Progress')" item-key="id">
              <template #item="{element}">
                <div class="card task-card mb-2" @click="openTaskModal(element)">
                  <div class="card-body">
                    <h5 class="card-title">{{ element.title }}</h5>
                    <p class="card-text">{{ element.description }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span v-if="element.due_date" class="badge bg-secondary me-1">{{ element.due_date }}</span>
                            <span class="badge bg-primary me-1">{{ element.priority }}</span>
                            <span v-if="element.assignee_name" class="badge bg-success">{{ element.assignee_name }}</span>
                        </div>
                        <div>
                            <span v-for="tag in element.tags?.split(',')" :key="tag" class="badge bg-info ms-1">{{ tag }}</span>
                        </div>
                    </div>
                  </div>
                </div>
              </template>
            </draggable>
          </div>
        </div>
      </div>

      <!-- Done Column -->
      <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card">
          <div class="card-header bg-light">Done</div>
          <div class="card-body">
            <draggable class="list-group" :list="doneTasks" group="tasks" @change="handleChange($event, 'Done')" item-key="id">
              <template #item="{element}">
                <div class="card task-card mb-2" @click="openTaskModal(element)">
                  <div class="card-body">
                    <h5 class="card-title">{{ element.title }}</h5>
                    <p class="card-text">{{ element.description }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span v-if="element.due_date" class="badge bg-secondary me-1">{{ element.due_date }}</span>
                            <span class="badge bg-primary me-1">{{ element.priority }}</span>
                            <span v-if="element.assignee_name" class="badge bg-success">{{ element.assignee_name }}</span>
                        </div>
                        <div>
                            <span v-for="tag in element.tags?.split(',')" :key="tag" class="badge bg-info ms-1">{{ tag }}</span>
                        </div>
                    </div>
                  </div>
                </div>
              </template>
            </draggable>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useTaskStore } from '../stores/task';
import { useProjectStore } from '../stores/project';
import { useUserStore } from '../stores/user';
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import draggable from 'vuedraggable';
import TaskModal from './TaskModal.vue';

export default {
  components: { draggable, TaskModal },
  setup() {
    const taskStore = useTaskStore();
    const projectStore = useProjectStore();
    const userStore = useUserStore();
    const route = useRoute();
    const projectId = route.params.id;

    const project = ref({});
    const newTask = reactive({ title: '', description: '', priority: 'Medium', due_date: null, tags: '', assignee_id: null });
    const selectedTask = ref(null);
    const isModalVisible = ref(false);
    const searchQuery = ref('');
    const selectedPriority = ref('');
    const selectedAssignee = ref(null);
    let socket = null;

    onMounted(async () => {
      await taskStore.fetchTasks(projectId);
      await userStore.fetchUsers();
      project.value = projectStore.projects.find(p => p.id == projectId) || {};

      // WebSocket connection
      socket = new WebSocket('ws://localhost:8080');

      socket.onopen = () => {
        console.log('WebSocket connection established');
        socket.send(JSON.stringify({ action: 'subscribe', projectId }));
      };

      socket.onmessage = (event) => {
        const data = JSON.parse(event.data);
        if (data.action === 'task_updated') {
            taskStore.fetchTasks(projectId);
        }
      };

      socket.onclose = () => {
        console.log('WebSocket connection closed');
      };
    });

    const filteredTasks = computed(() => {
        return taskStore.tasks.filter(task => {
            const searchMatch = task.title.toLowerCase().includes(searchQuery.value.toLowerCase());
            const priorityMatch = !selectedPriority.value || task.priority === selectedPriority.value;
            const assigneeMatch = !selectedAssignee.value || task.assignee_id == selectedAssignee.value;
            return searchMatch && priorityMatch && assigneeMatch;
        });
    });

    const todoTasks = computed(() => filteredTasks.value.filter(t => t.status === 'To Do'));
    const inProgressTasks = computed(() => filteredTasks.value.filter(t => t.status === 'In Progress'));
    const doneTasks = computed(() => filteredTasks.value.filter(t => t.status === 'Done'));

    const createTask = async (status) => {
        if (!newTask.title) return;
        await taskStore.createTask(projectId, { ...newTask, status });
        Object.assign(newTask, { title: '', description: '', priority: 'Medium', due_date: null, tags: '', assignee_id: null });
    };

    const handleChange = (event, newStatus) => {
        if (event.added) {
            const task = event.added.element;
            taskStore.updateTask(task.id, { ...task, status: newStatus });
        }
    };

    const openTaskModal = (task) => {
        selectedTask.value = task;
        isModalVisible.value = true;
    };

    const closeTaskModal = () => {
        isModalVisible.value = false;
        selectedTask.value = null;
    };

    return { project, todoTasks, inProgressTasks, doneTasks, newTask, createTask, handleChange, userStore, selectedTask, isModalVisible, openTaskModal, closeTaskModal, searchQuery, selectedPriority, selectedAssignee };
  },
};
</script>

<style scoped>
.task-card { cursor: grab; }
.list-group { min-height: 100px; }
</style>
