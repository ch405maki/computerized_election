<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { BarChart2, RefreshCw, Table } from 'lucide-vue-next';

defineProps<{
    isLoading: boolean;
    showRanking: boolean;
    showChart: boolean;
    canShowRanking?: boolean;
    canShowChart?: boolean;
}>();

const emit = defineEmits(['refresh', 'toggleRanking', 'toggleChart']);
</script>

<template>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">Election Dashboard</h1>
        <div class="flex gap-2">
            <!-- Show Ranking Button -->
            <Button v-if="canShowRanking" variant="outline" @click="emit('toggleRanking')">
                <Table class="mr-2 h-4 w-4" />
                {{ showRanking ? 'Hide Ranking' : 'Show Ranking' }}
            </Button>

            <!-- Show Chart Button -->
            <Button v-if="canShowChart" variant="outline" @click="emit('toggleChart')">
                <BarChart2 class="mr-2 h-4 w-4" />
                {{ showChart ? 'Hide Chart' : 'Show Chart' }}
            </Button>

            <!-- Refresh Button -->
            <Button variant="outline" @click="emit('refresh')" :disabled="isLoading">
                <RefreshCw class="mr-2 h-4 w-4" :class="{ 'animate-spin': isLoading }" />
                Refresh
            </Button>
        </div>
    </div>
</template>
