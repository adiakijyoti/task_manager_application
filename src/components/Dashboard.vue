<template>
  <div class="container mt-5">
    <h2>Dashboard</h2>
    <div class="row mt-4">
      <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card text-white bg-primary mb-3">
          <div class="card-header">Total Projects</div>
          <div class="card-body">
            <h5 class="card-title">{{ projectStore.projects.length }}</h5>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card text-white bg-success mb-3">
          <div class="card-header">Total Tasks</div>
          <div class="card-body">
            <h5 class="card-title">{{ taskStore.tasks.length }}</h5>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card text-white bg-info mb-3">
          <div class="card-header">Total Users</div>
          <div class="card-body">
            <h5 class="card-title">{{ userStore.users.length }}</h5>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Task Status Distribution</div>
                <div class="card-body">
                    <TaskStatusPieChart :tasks="taskStore.tasks" />
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import { useProjectStore } from '../stores/project';
import { useTaskStore } from '../stores/task';
import { useUserStore } from '../stores/user';
import { onMounted } from 'vue';
import TaskStatusPieChart from './TaskStatusPieChart.vue';

export default {
  components: { TaskStatusPieChart },
  setup() {
    const projectStore = useProjectStore();
    const taskStore = useTaskStore();
    const userStore = useUserStore();

    onMounted(async () => {
      await projectStore.fetchProjects();
      await taskStore.fetchAllTasks();
      await userStore.fetchUsers();
    });

    return { projectStore, taskStore, userStore };
  },
};
</script>
