<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

// Register ChartJS components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

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
    'rgba(54, 162, 235, 0.7)', // Blue
    'rgba(255, 99, 132, 0.7)', // Red
    'rgba(16, 185, 129, 0.7)', // Emerald Green
    'rgba(255, 206, 86, 0.7)', // Yellow
    'rgba(153, 102, 255, 0.7)', // Purple
    'rgba(255, 159, 64, 0.7)', // Orange
    'rgba(6, 182, 212, 0.7)', // Cyan
    'rgba(244, 63, 94, 0.7)', // Rose
    'rgba(139, 92, 246, 0.7)', // Violet
];

// Prepare chart data (Top 3 candidates per position)
const chartData = computed(() => {
    const positionLabels = props.voteRanking.map((pos) => pos.position);

    // For each position, sort candidates descending and slice top 3
    const sortedPositionCandidates = props.voteRanking.map((position) => {
        return [...position.candidates].sort((a, b) => b.votes - a.votes).slice(0, 3);
    });

    // Find the maximum number of candidates across any position (up to 3)
    const maxCandidates = Math.max(...sortedPositionCandidates.map((cands) => cands.length), 0);

    // Create datasets where each dataset represents a candidate rank (Candidate 1, Candidate 2, Candidate 3)
    const datasets = Array.from({ length: maxCandidates }, (_, candIndex) => {
        const label = `Candidate ${candIndex + 1}`;
        const bgColor = palette[candIndex % palette.length];

        const data = sortedPositionCandidates.map((candidates) => {
            if (candIndex < candidates.length) {
                return candidates[candIndex].votes;
            }
            return null;
        });

        return {
            label,
            data,
            backgroundColor: bgColor,
            borderColor: bgColor.replace('0.7', '1'),
            borderWidth: 1,
            skipNull: true,
        };
    });

    return {
        labels: positionLabels,
        datasets,
    };
});

// Chart configuration (VERTICAL BAR CHART)
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
        },
        tooltip: {
            callbacks: {
                label: (context: any) => `${context.dataset.label}: ${context.raw} votes`,
            },
        },
        title: {
            display: true,
            text: 'Vote Ranking',
            font: {
                size: 16,
            },
        },
    },
    scales: {
        x: {
            title: {
                display: true,
                text: 'Positions',
                font: {
                    weight: 'bold',
                },
            },
            ticks: {
                autoSkip: false,
            },
        },
        y: {
            title: {
                display: true,
                text: 'Number of Votes',
                font: {
                    weight: 'bold',
                },
            },
            beginAtZero: true,
        },
    },
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Vote Ranking</CardTitle>
        </CardHeader>
        <CardContent>
            <div v-if="isLoading" class="flex h-96 items-center justify-center">
                <p class="text-muted-foreground">Loading vote rankings...</p>
            </div>
            <div v-else-if="voteRanking.length === 0" class="flex h-96 items-center justify-center">
                <p class="text-muted-foreground">No vote ranking data available</p>
            </div>
            <div v-else class="h-[500px]">
                <Bar :options="chartOptions" :data="chartData" />
            </div>
        </CardContent>
    </Card>
</template>
