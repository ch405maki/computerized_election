<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    participationData: Array<{ date: string; votes: number }>;
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
            <div v-if="participationData.length" class="flex h-full items-end gap-2">
                <div v-for="day in participationData" :key="day.date" class="flex flex-1 flex-col items-center">
                    <div class="w-full rounded-t-sm bg-primary transition-all" :style="{ height: `${(day.votes / maxVotes) * 100}%` }" />
                    <span class="mt-1 text-xs">{{ formatDate(day.date) }}</span>
                    <span class="mt-1 text-xs font-medium">{{ day.votes }}</span>
                </div>
            </div>
            <div v-else class="flex h-full items-center justify-center">
                <p class="text-muted-foreground">No participation data available</p>
            </div>
        </CardContent>
    </Card>
</template>
