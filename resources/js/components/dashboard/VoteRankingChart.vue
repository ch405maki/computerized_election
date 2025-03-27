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
  LinearScale
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

interface CandidateVote {
  name: string;
  votes: number;
  image?: string;
}

interface PositionVotes {
  position: string;
  candidates: CandidateVote[];
}

const props = defineProps<{
  voteRanking: PositionVotes[];
  isLoading: boolean;
}>();

// Function to generate dynamic colors
const generateColors = (count: number) => {
  const colors = [
    'rgba(59, 130, 246, 0.7)',  // Blue
    'rgba(239, 68, 68, 0.7)',   // Red
    'rgba(245, 158, 11, 0.7)',  // Amber
    'rgba(16, 185, 129, 0.7)',  // Green
    'rgba(139, 92, 246, 0.7)',  // Violet
  ];
  return Array.from({ length: count }, (_, i) => colors[i % colors.length]);
};

// Prepare chart data (Top 2 candidates per position)
const chartData = computed(() => {
  const positionLabels = props.voteRanking.map(pos => pos.position);
  const datasets = props.voteRanking.flatMap((position, posIndex) => {
    // Get only the top 2 candidates per position
    const topCandidates = position.candidates
      .sort((a, b) => b.votes - a.votes) // Sort by votes (desc)
      .slice(0, 3); // Take top 2

    return topCandidates.map((candidate, candIndex) => ({
      label: candidate.name,
      data: props.voteRanking.map((_, i) => (i === posIndex ? candidate.votes : 0)), // Align votes with position index
      backgroundColor: generateColors(topCandidates.length)[candIndex],
      borderColor: generateColors(topCandidates.length)[candIndex].replace('0.7', '1'),
      borderWidth: 1
    }));
  });

  return {
    labels: positionLabels,
    datasets
  };
});

// Chart configuration (VERTICAL BAR CHART)
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true
    },
    tooltip: {
      callbacks: {
        label: (context: any) => `${context.dataset.label}: ${context.raw} votes`
      }
    },
    title: {
      display: true,
      text: 'Vote Ranking',
      font: {
        size: 16
      }
    }
  },
  scales: {
    x: {
      title: {
        display: true,
        text: 'Positions',
        font: {
          weight: 'bold'
        }
      },
      ticks: {
        autoSkip: false
      }
    },
    y: {
      title: {
        display: true,
        text: 'Number of Votes',
        font: {
          weight: 'bold'
        }
      },
      beginAtZero: true
    }
  }
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Vote Ranking</CardTitle>
    </CardHeader>
    <CardContent>
      <div v-if="isLoading" class="h-96 flex items-center justify-center">
        <p class="text-muted-foreground">Loading chart data...</p>
      </div>
      <div v-else-if="voteRanking.length === 0" class="h-96 flex items-center justify-center">
        <p class="text-muted-foreground">No vote ranking data available</p>
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
