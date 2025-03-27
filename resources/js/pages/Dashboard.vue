<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { ref } from 'vue';
import { RefreshCw } from "lucide-vue-next";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '../components/ui/badge'

// Sample data - replace with your actual data
const stats = ref({
  totalElections: 4,
  activeElections: 2,
  totalVoters: 1245,
  votesToday: 342,
})

const recentElections = ref([
  { id: 1, name: 'Student Council 2024', status: 'active', start: '2024-06-01', end: '2024-06-30' },
  { id: 2, name: 'Faculty Board', status: 'upcoming', start: '2024-07-15', end: '2024-07-30' },
  { id: 3, name: 'Alumni Association', status: 'completed', start: '2024-03-01', end: '2024-03-15' },
])

const quickActions = [
  { icon: 'plus', title: 'New Election', link: '/elections/create' },
  { icon: 'users', title: 'Manage Voters', link: '/voters' },
  { icon: 'settings', title: 'Election Settings', link: '/settings' },
]

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">Election Dashboard</h1>
                <div class="flex gap-2">
                    <Button variant="outline">
                    <RefreshCw />
                        Refresh
                    </Button>
                </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Total Elections</CardTitle>
                    <Icon name="layers" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                    <div class="text-2xl font-bold">{{ stats.totalElections }}</div>
                    <p class="text-xs text-muted-foreground">+2 from last month</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Active Elections</CardTitle>
                    <Icon name="activity" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                    <div class="text-2xl font-bold">{{ stats.activeElections }}</div>
                    <p class="text-xs text-muted-foreground">Currently running</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Registered Voters</CardTitle>
                    <Icon name="users" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                    <div class="text-2xl font-bold">{{ stats.totalVoters }}</div>
                    <p class="text-xs text-muted-foreground">+120 this week</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Votes Today</CardTitle>
                    <Icon name="bar-chart-2" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                    <div class="text-2xl font-bold">{{ stats.votesToday }}</div>
                    <p class="text-xs text-muted-foreground">+24% from yesterday</p>
                    </CardContent>
                </Card>
                </div>

                <!-- Recent Elections Table -->
                <Card>
                <CardHeader>
                    <CardTitle>Recent Elections</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                    <TableHeader>
                        <TableRow>
                        <TableHead>Election</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Period</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="election in recentElections" :key="election.id">
                        <TableCell class="font-medium">{{ election.name }}</TableCell>
                        <TableCell>
                            <Badge 
                            :variant="election.status === 'active' ? 'default' : 
                                    election.status === 'upcoming' ? 'secondary' : 'outline'"
                            >
                            {{ election.status }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{ election.start }} to {{ election.end }}</TableCell>
                        <TableCell class="text-right">
                            <Button variant="ghost" size="sm">
                            <Icon name="eye" class="h-4 w-4 mr-1" />
                            View
                            </Button>
                        </TableCell>
                        </TableRow>
                    </TableBody>
                    </Table>
                </CardContent>
                </Card>

                <!-- Voter Participation Chart (Placeholder) -->
                <Card>
                <CardHeader>
                    <CardTitle>Voter Participation</CardTitle>
                </CardHeader>
                <CardContent class="h-[300px] flex items-center justify-center">
                    <p class="text-muted-foreground">Participation chart will appear here</p>
                </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
