<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Settings, Users } from 'lucide-vue-next';

const quickActions = [
    { icon: Plus, title: 'New Election', link: '/elections/create' },
    { icon: Users, title: 'Manage Voters', link: '/voters' },
    { icon: Settings, title: 'Election Settings', link: '/settings' },
];
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Button
                v-for="action in quickActions"
                :key="action.title"
                variant="outline"
                class="flex h-32 flex-col items-center justify-center gap-2"
                as-child
            >
                <router-link :to="action.link">
                    <component :is="action.icon" class="h-6 w-6" />
                    <span>{{ action.title }}</span>
                </router-link>
            </Button>
        </CardContent>
    </Card>
</template>
