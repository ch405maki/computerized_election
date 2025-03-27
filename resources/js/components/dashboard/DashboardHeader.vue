<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { RefreshCw, BarChart2, Table } from 'lucide-vue-next';

defineProps<{
  isLoading: boolean;
  isChartView: boolean; // Receive toggle state
}>();

const emit = defineEmits(['refresh', 'toggleView']);
</script>

<template>
  <div class="flex justify-between items-center">
    <h1 class="text-3xl font-bold">Election Dashboard</h1>
    <div class="flex gap-2">
      <!-- Toggle Button -->
      <Button variant="outline" @click="emit('toggleView')">
        <component
          :is="isChartView ? Table : BarChart2"
          class="h-4 w-4 mr-2"
        />
        {{ isChartView ? 'Show Table' : 'Show Chart' }}
      </Button>

      <!-- Refresh Button -->
      <Button variant="outline" @click="emit('refresh')" :disabled="isLoading">
        <RefreshCw class="h-4 w-4 mr-2" :class="{ 'animate-spin': isLoading }" />
        Refresh
      </Button>
    </div>
  </div>
</template>
