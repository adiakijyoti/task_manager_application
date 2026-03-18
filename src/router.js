
import { createRouter, createWebHistory } from 'vue-router';
import Register from './components/Register.vue';
import Login from './components/Login.vue';
import Projects from './components/Projects.vue';
import ProjectBoard from './components/ProjectBoard.vue';
import Dashboard from './components/Dashboard.vue';
import Calendar from './components/Calendar.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Dashboard,
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
  },
  {
    path: '/projects',
    name: 'Projects',
    component: Projects,
  },
  {
    path: '/projects/:id',
    name: 'ProjectBoard',
    component: ProjectBoard,
  },
  {
      path: '/dashboard',
      name: 'Dashboard',
      component: Dashboard,
  },
  {
      path: '/calendar',
      name: 'Calendar',
      component: Calendar,
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
