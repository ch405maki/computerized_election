<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { 
  Chart as ChartJS, 
  Title, 
  Tooltip, 
  Legend, 
  BarElement, 
  CategoryScale, 
  LinearScale,
  type TooltipItem
} from 'chart.js';

// Register ChartJS components
ChartJS.register(
  Title, 
  Tooltip, 
  Legend, 
  BarElement, 
  CategoryScale, 
  LinearScale
);

// Simplified interface for year-level data
export interface YearVoteData {
  year: string; // e.g., '1st Year', '2nd Year', '3rd Year', '4th Year'
  votes: number;
}

const props = defineProps<{
  votingData: YearVoteData[];
  isLoading: boolean;
}>();

// Generate distinct colors for each year level for better visual separation
const generateColors = (count: number) => {
  const colors = [
    'rgba(59, 130, 246, 0.7)',  // Blue
    'rgba(16, 185, 129, 0.7)',  // Green
    'rgba(245, 158, 11, 0.7)',  // Yellow
    'rgba(239, 68, 68, 0.7)',   // Red
    'rgba(139, 92, 246, 0.7)'   // Purple
  ];
  return Array.from({ length: count }, (_, i) => colors[i % colors.length]);
};

// Prepare chart data (Single dataset)
const chartData = computed(() => {
  const labels = props.votingData.map(d => d.year);
  const data = props.votingData.map(d => d.votes);
  const bgColors = generateColors(data.length);
  const borderColors = bgColors.map(color => color.replace('0.7', '1'));

  return {
    labels,
    datasets: [
      {
        label: 'Total Votes',
        data,
        backgroundColor: bgColors,
        borderColor: borderColors,
        borderWidth: 1,
        maxBarThickness: 40
      }
    ]
  };
});

// Chart configuration 
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      // Hidden because we only have one dataset and the X-axis explains the categories
      display: false 
    },
    tooltip: {
      callbacks: {
        label: (context: TooltipItem<'bar'>) => `${context.raw} votes`
      }
    },
    title: {
      display: true,
      text: 'Voter Turnout by Year Level',
      font: { size: 16 }
    }
  },
  scales: {
    x: {
      title: {
        display: true,
        text: 'Year Level',
        font: { weight: 'bold' as const } 
      }
    },
    y: {
      title: {
        display: true,
        text: 'Number of Votes',
        font: { weight: 'bold' as const }
      },
      beginAtZero: true,
      ticks: {
        stepSize: 1 // Ensures the Y-axis only shows whole numbers
      }
    }
  }
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Turnout by Year Level</CardTitle>
    </CardHeader>
    <CardContent>
      <div v-if="isLoading" class="h-96 flex items-center justify-center">
        <p class="text-muted-foreground">Loading turnout data...</p>
      </div>
      <div v-else-if="votingData.length === 0" class="h-96 flex items-center justify-center">
        <p class="text-muted-foreground">No voting data available</p>
      </div>
      <div v-else class="h-[500px]">
        <Bar 
          :options="chartOptions"
          :data="chartData"
        />
      </div>
    </CardContent>
  </Card>
</template>