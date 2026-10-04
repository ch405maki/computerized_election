<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    title: string;
    value: number;
    description: string;
    icon: any;
}>();
</script>

<template>
    <Card class="border-0 bg-primary text-primary-foreground shadow-md">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-white/90">{{ title }}</CardTitle>
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                <component :is="icon" class="h-4 w-4 text-white" />
            </span>
        </CardHeader>
        <CardContent>
            <div class="text-2xl font-bold text-white">{{ value }}</div>
            <p class="text-xs text-white/70">
                {{ description }}
            </p>
        </CardContent>
    </Card>
</template>
