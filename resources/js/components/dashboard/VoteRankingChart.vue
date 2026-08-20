<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';
import { computed, ref } from 'vue';
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

// State for toggling actual names
const showActualNames = ref(false);

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

const sortedCandidatesByPosition = computed(() => {
    return props.voteRanking.map((position) => {
        return [...position.candidates].sort((a, b) => b.votes - a.votes).slice(0, 3);
    });
});

const chartData = computed(() => {
    const maxCandidates = Math.max(...sortedCandidatesByPosition.value.map((cands) => cands.length), 0);

    // Update X-axis labels to be multi-line when the toggle is pressed
    const positionLabels = props.voteRanking.map((pos, index) => {
        if (showActualNames.value) {
            // Chart.js uses arrays inside labels to create multi-line X-axis text
            const lines = [pos.position];
            sortedCandidatesByPosition.value[index].forEach((c, i) => {
                const rank = i === 0 ? '1st' : i === 1 ? '2nd' : '3rd';
                // Truncate long names to keep the axis tidy
                const shortName = c.name.length > 18 ? c.name.substring(0, 18) + '...' : c.name;
                lines.push(`${rank}: ${shortName}`);
            });
            return lines;
        }
        return pos.position;
    });

    const datasets = Array.from({ length: maxCandidates }, (_, candIndex) => {
        const label = `Rank ${candIndex + 1}`;
        const bgColor = palette[candIndex % palette.length];

        const data = sortedCandidatesByPosition.value.map((candidates) => {
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
        labels: positionLabels, // Injects standard or multi-line labels dynamically
        datasets,
    };
});

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
                    label: (context: any) => {
                        const posIndex = context.dataIndex;
                        const candIndex = context.datasetIndex;
                        const candidate = sortedCandidatesByPosition.value[posIndex]?.[candIndex];

                        const nameLabel = showActualNames.value && candidate 
                            ? candidate.name 
                            : context.dataset.label;
                            
                        return `${nameLabel}: ${context.raw} votes`;
                    },
                },
            },
            title: {
                display: false, 
            },
        },
        scales: {
            x: {
                title: {
                    display: !showActualNames.value, // Hide axis title when names are shown to save vertical space
                    text: 'Positions',
                    font: { weight: 'bold' },
                },
                ticks: {
                    autoSkip: false,
                    font: {
                        size: showActualNames.value ? 11 : 12
                    }
                },
            },
            y: {
                title: {
                    display: true,
                    text: 'Number of Votes',
                    font: { weight: 'bold' },
                },
                beginAtZero: true,
            },
        },
    };

    if (props.voteThreshold && props.voteThreshold.required_votes > 0) {
        options.plugins.annotation = {
            annotations: {
                thresholdLine: {
                    type: 'line',
                    yMin: props.voteThreshold.required_votes,
                    yMax: props.voteThreshold.required_votes,
                    borderColor: 'rgba(239, 68, 68, 0.85)',
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
                        padding: { top: 4, bottom: 4, left: 6, right: 6 },
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
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
            <CardTitle>Vote Ranking</CardTitle>
            
            <button 
                @click="showActualNames = !showActualNames"
                class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium shadow-sm transition-colors hover:bg-muted hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            >
                {{ showActualNames ? 'Hide Names' : 'Reveal Names' }}
            </button>
        </CardHeader>

        <CardContent>
            <div v-if="isLoading" class="flex h-96 items-center justify-center">
                <p class="text-muted-foreground">Loading vote rankings...</p>
            </div>
            <div v-else-if="voteRanking.length === 0" class="flex h-96 items-center justify-center">
                <p class="text-muted-foreground">No vote ranking data available</p>
            </div>
            <div v-else class="h-[500px]">
                <!-- Notice the addition of the :key binding below -->
                <Bar :options="chartOptions" :data="chartData" :key="showActualNames.toString()" />
            </div>
        </CardContent>
    </Card>
</template>