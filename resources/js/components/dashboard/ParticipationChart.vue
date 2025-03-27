<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
  participationData: Array<{date: string; votes: number}>;
  maxVotes: number;
  formatDate: (dateString: string) => string;
}>();
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Voter Participation</CardTitle>
    </CardHeader>
    <CardContent class="h-[300px]">
      <div v-if="participationData.length" class="h-full flex items-end gap-2">
        <div 
          v-for="day in participationData" 
          :key="day.date"
          class="flex-1 flex flex-col items-center"
        >
          <div 
            class="w-full bg-primary rounded-t-sm transition-all"
            :style="{ height: `${(day.votes / maxVotes) * 100}%` }"
          />
          <span class="text-xs mt-1">{{ formatDate(day.date) }}</span>
          <span class="text-xs font-medium mt-1">{{ day.votes }}</span>
        </div>
      </div>
      <div v-else class="h-full flex items-center justify-center">
        <p class="text-muted-foreground">No participation data available</p>
      </div>
    </CardContent>
  </Card>
</template>