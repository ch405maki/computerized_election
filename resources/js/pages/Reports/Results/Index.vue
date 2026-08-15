<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import TitleHeader from '@/components/ui/title-header/header.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ScrollText, Loader2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Election {
    id: number;
    name: string;
    status: 'active' | 'upcoming' | 'completed';
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    elections: Election[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: '/reports/results' },
    { title: 'Election Results', href: '#' },
];

const formattedDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

// --- Check User Permission ---
const page = usePage<any>();
const hasElectionResultsPermission = computed(() => {
    // Adjust this path if your Inertia auth object is structured differently
    return page.props.auth?.user?.permissions?.showElectionResults === true;
});

// --- Dialog & Password Verification State ---
const isDialogOpen = ref(false);
const selectedElectionId = ref<number | string | null>(null);

const form = useForm({
    password: '',
});

function openPasswordDialog(id: number | string) {
    selectedElectionId.value = id;
    form.reset();
    form.clearErrors();
    isDialogOpen.value = true;
}

function submitPassword() {
    if (!selectedElectionId.value) return;

    // Post to a new verification route
    form.post(route('results.verify', selectedElectionId.value), {
        preserveScroll: true,
        onSuccess: () => {
            isDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <Head title="Election Results" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <TitleHeader title="Election Results" description="Breakdown of vote counts and winning statistics." />
            <div class="overflow-hidden rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Election Name</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Election Period</TableHead>
                            <!-- Conditionally render Action header -->
                            <TableHead v-if="hasElectionResultsPermission" class="text-right">Action</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="election in elections" :key="election.id">
                            <TableCell class="font-medium">{{ election.name }}</TableCell>
                            <TableCell>
                                <span
                                    :class="{
                                        'text-green-600': election.status === 'completed',
                                        'text-blue-600': election.status === 'active',
                                        'text-gray-600': election.status === 'upcoming',
                                    }"
                                >
                                    {{ election.status.charAt(0).toUpperCase() + election.status.slice(1) }}
                                </span>
                            </TableCell>
                            <TableCell>
                                {{ formattedDate(election.start_date) }} -
                                {{ formattedDate(election.end_date) }}
                            </TableCell>
                            <!-- Conditionally render Action cell -->
                            <TableCell v-if="hasElectionResultsPermission" class="text-right">
                                <Button
                                    v-if="election.status === 'completed'"
                                    @click="openPasswordDialog(election.id)"
                                    class="inline-flex items-center rounded px-4 py-2"
                                >
                                    <ScrollText class="mr-2 h-4 w-4" />
                                    View Results
                                </Button>
                                <span v-else class="text-gray-500"> Results unavailable </span>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="elections.length === 0">
                            <!-- Dynamically adjust colspan based on visibility of Action column -->
                            <TableCell :colspan="hasElectionResultsPermission ? 4 : 3" class="py-4 text-center text-muted-foreground">
                                No elections found
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Password Required</DialogTitle>
                    <DialogDescription> Please enter your account password. </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitPassword">
                    <div class="grid gap-4 py-4">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="password" class="text-right"> Password </Label>
                            <Input
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="col-span-3"
                                autocomplete="current-password"
                                autofocus
                            />
                        </div>
                        <div v-if="form.errors.password" class="pr-2 text-right text-sm text-red-500">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isDialogOpen = false"> Cancel </Button>
                        <Button type="submit" :disabled="form.processing"> 
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                {{ form.processing ? 'Confirming...' : 'Confirm' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
