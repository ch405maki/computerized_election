<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

// Register ChartJS components including the annotation plugin
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, annotationPlugin);

interface CandidateVote {
    name: string;
    votes: number;
    image?: string;
}

interface PositionVotes {
    position: string;
    candidates: CandidateVote[];
}

interface VoteThreshold {
    percentage: number;
    required_votes: number;
}

const props = defineProps<{
    voteRanking: PositionVotes[];
    isLoading: boolean;
    voteThreshold?: VoteThreshold | null;
}>();

// Palette for candidate bars
const palette = [
    'rgba(54, 162, 235, 0.7)',
    'rgba(255, 99, 132, 0.7)',
    'rgba(16, 185, 129, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)',
    'rgba(6, 182, 212, 0.7)',
    'rgba(244, 63, 94, 0.7)',
    'rgba(139, 92, 246, 0.7)',
];

const chartData = computed(() => {
    const positionLabels = props.voteRanking.map((pos) => pos.position);

    const sortedPositionCandidates = props.voteRanking.map((position) => {
        return [...position.candidates].sort((a, b) => b.votes - a.votes).slice(0, 3);
    });

    const maxCandidates = Math.max(...sortedPositionCandidates.map((cands) => cands.length), 0);

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

// Chart options with threshold line configuration
const chartOptions = computed(() => {
    const options: any = {
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

    // If threshold data is provided, draw the horizontal threshold line
    if (props.voteThreshold && props.voteThreshold.required_votes > 0) {
        options.plugins.annotation = {
            annotations: {
                thresholdLine: {
                    type: 'line',
                    yMin: props.voteThreshold.required_votes,
                    yMax: props.voteThreshold.required_votes,
                    borderColor: 'rgba(239, 68, 68, 0.85)', // Red accent
                    borderWidth: 2,
                    borderDash: [6, 6],
                    label: {
                        display: true,
                        content: `Threshold: ${props.voteThreshold.percentage}% (${props.voteThreshold.required_votes} votes)`,
                        position: 'end',
                        backgroundColor: 'rgba(239, 68, 68, 0.9)',
                        color: '#ffffff',
                        font: {
                            weight: 'bold',
                            size: 11,
                        },
                        padding: {
                            top: 4,
                            bottom: 4,
                            left: 6,
                            right: 6,
                        },
                        borderRadius: 4,
                    },
                },
            },
        };
    }

    return options;
});
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