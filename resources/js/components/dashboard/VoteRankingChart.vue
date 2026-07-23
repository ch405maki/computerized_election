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

// A vibrant palette for candidates
const palette = [
  'rgba(54, 162, 235, 0.7)',   // Blue
  'rgba(255, 99, 132, 0.7)',   // Red
  'rgba(16, 185, 129, 0.7)',   // Emerald Green
  'rgba(255, 206, 86, 0.7)',   // Yellow
  'rgba(153, 102, 255, 0.7)',  // Purple
  'rgba(255, 159, 64, 0.7)',   // Orange
  'rgba(6, 182, 212, 0.7)',    // Cyan
  'rgba(244, 63, 94, 0.7)',    // Rose
  'rgba(139, 92, 246, 0.7)'    // Violet
];

// Prepare chart data (Top 3 candidates per position)
const chartData = computed(() => {
  const positionLabels = props.voteRanking.map(pos => pos.position);
  
  let colorIndex = 0;
  const candidateColors: Record<string, string> = {};

  const datasets = props.voteRanking.flatMap((position, posIndex) => {
    // Get only the top 3 candidates per position
    const topCandidates = position.candidates
      .sort((a, b) => b.votes - a.votes) // Sort by votes (desc)
      .slice(0, 3); // Take top 3

    return topCandidates.map((candidate) => {
      // Assign and store a unique color per candidate name
      if (!candidateColors[candidate.name]) {
        candidateColors[candidate.name] = palette[colorIndex % palette.length];
        colorIndex++;
      }
      
      const bgColor = candidateColors[candidate.name];

      return {
        label: candidate.name,
        // Use `null` instead of `0` to prevent ChartJS from reserving blank space on the X-axis
        data: props.voteRanking.map((_, i) => (i === posIndex ? candidate.votes : null)), 
        backgroundColor: bgColor,
        borderColor: bgColor.replace('0.7', '1'),
        borderWidth: 1,
        skipNull: true // Tells ChartJS to ignore the null values in rendering layouts
      };
    });
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