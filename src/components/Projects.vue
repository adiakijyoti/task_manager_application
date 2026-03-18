<template>
  <div class="container mt-5">
    <h2>Projects</h2>
    <div class="row">
      <div class="col-lg-8 col-md-12 mb-3">
        <ul class="list-group">
          <li v-for="project in projectStore.projects" :key="project.id" class="list-group-item d-flex justify-content-between align-items-center">
            {{ project.name }}
            <div>
              <router-link :to="{ name: 'ProjectBoard', params: { id: project.id } }" class="btn btn-sm btn-primary me-2">View</router-link>
              <button class="btn btn-sm btn-danger">Delete</button>
            </div>
          </li>
        </ul>
      </div>
      <div class="col-lg-4 col-md-12">
        <div class="card">
          <div class="card-header">Create Project</div>
          <div class="card-body">
            <form @submit.prevent="createProject">
              <div class="form-group">
                <label for="projectName">Project Name</label>
                <input type="text" id="projectName" v-model="newProject.name" class="form-control" required>
              </div>
              <div class="form-group mt-2">
                <label for="projectDescription">Description</label>
                <textarea id="projectDescription" v-model="newProject.description" class="form-control"></textarea>
              </div>
              <button type="submit" class="btn btn-primary mt-3">Create</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useProjectStore } from '../stores/project';
import { onMounted, reactive } from 'vue';

export default {
  setup() {
    const projectStore = useProjectStore();
    const newProject = reactive({ name: '', description: '' });

    onMounted(() => {
      projectStore.fetchProjects();
    });

    const createProject = async () => {
      await projectStore.createProject(newProject);
      newProject.name = '';
      newProject.description = '';
    };

    return { projectStore, newProject, createProject };
  },
};
</script>
