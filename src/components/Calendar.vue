<template>
  <div class="container mt-5">
    <h2>Calendar</h2>
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { useTaskStore } from '../stores/task';
import { onMounted, computed } from 'vue';

export default {
  components: {
    FullCalendar,
  },
  setup() {
    const taskStore = useTaskStore();

    onMounted(() => {
      taskStore.fetchAllTasks();
    });

    const calendarEvents = computed(() => {
      return taskStore.tasks
        .filter(task => task.due_date)
        .map(task => ({
          title: task.title,
          date: task.due_date,
        }));
    });

    const calendarOptions = computed(() => ({
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      events: calendarEvents.value,
    }));

    return { calendarOptions };
  },
};
</script>
