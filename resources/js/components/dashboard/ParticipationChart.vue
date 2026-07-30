<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';


ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps<{
    participationData: Array<{ date: string; votes: number }>;
    maxVotes: number;
    formatDate: (dateString: string) => string;
}>();

// Prepare chart data
const chartData = computed(() => {
    const labels = props.participationData.map((day) => props.formatDate(day.date));
    const data = props.participationData.map((day) => day.votes);

    return {
        labels,
        datasets: [
            {
                label: 'Votes',
                data,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                
                maxBarThickness: 100,
                barPercentage: 0.8,
                categoryPercentage: 0.9,
            },
        ],
    };
});

// Chart configuration (VERTICAL BAR CHART)
const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false, // Set to false since we only have one dataset (Votes)
        },
        tooltip: {
            callbacks: {
                label: (context: any) => `${context.raw} votes`,
            },
        },
    },
    scales: {
        x: {
            title: {
                display: true,
                text: 'Date',
                font: {
                    weight: 'bold' as const,
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
                    weight: 'bold' as const,
                },
            },
            beginAtZero: true,
            suggestedMax: props.maxVotes,
        },
    },
}));
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Voter Participation</CardTitle>
        </CardHeader>
        <CardContent>
            <div v-if="!participationData || participationData.length === 0" class="flex h-[300px] items-center justify-center">
                <p class="text-muted-foreground">No participation data available</p>
            </div>
            <div v-else class="h-[300px]">
                <Bar :options="chartOptions" :data="chartData" />
            </div>
        </CardContent>
    </Card>
</template>