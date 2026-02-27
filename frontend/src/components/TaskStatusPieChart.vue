<template>
  <Pie :data="chartData" :options="chartOptions" />
</template>

<script>
import { Pie } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
} from 'chart.js';
import { computed } from 'vue';

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

export default {
  name: 'TaskStatusPieChart',
  components: { Pie },
  props: {
    tasks: {
      type: Array,
      required: true,
    },
  },
  setup(props) {
    const chartData = computed(() => {
      const statusCounts = {
        'To Do': 0,
        'In Progress': 0,
        'Done': 0,
      };

      props.tasks.forEach(task => {
        if (statusCounts.hasOwnProperty(task.status)) {
          statusCounts[task.status]++;
        }
      });

      return {
        labels: Object.keys(statusCounts),
        datasets: [
          {
            backgroundColor: ['#f87979', '#f0e68c', '#90ee90'],
            data: Object.values(statusCounts),
          },
        ],
      };
    });

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
    };

    return { chartData, chartOptions };
  },
};
</script>
