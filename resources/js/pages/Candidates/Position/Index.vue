<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref, computed } from 'vue';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import TitleHeader from '@/components/ui/title-header/header.vue'
import { ArrowUpDown, ArrowUpWideNarrow, ArrowDownWideNarrow } from 'lucide-vue-next';


const toast = useToast();
const isLoading = ref(false);
const isDialogOpen = ref(false);

// Define props
const props = defineProps<{
    positions: Array<{
        id: number;
        name: string;
        created_at: string;
        updated_at: string;
    }>;
}>();

type SortKey = 'id' | 'name' | 'created_at';
const sortKey = ref<SortKey | ''>('');
const sortOrder = ref<'asc' | 'desc'>('asc');

const sortBy = (key: SortKey) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

const sortedPositions = computed(() => {
    // If no sort key is selected, return the original array
    if (!sortKey.value) return props.positions;

    // Create a shallow copy so we don't mutate the original props
    return [...props.positions].sort((a, b) => {
        let aValue = a[sortKey.value as SortKey];
        let bValue = b[sortKey.value as SortKey];

        // Handle string comparison (case-insensitive) for names
        if (typeof aValue === 'string' && typeof bValue === 'string' && sortKey.value !== 'created_at') {
            aValue = aValue.toLowerCase();
            bValue = bValue.toLowerCase();
        }

        // Handle date comparison for created_at
        if (sortKey.value === 'created_at') {
            aValue = new Date(aValue).getTime();
            bValue = new Date(bValue).getTime();
        }

        if (aValue < bValue) return sortOrder.value === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortOrder.value === 'asc' ? 1 : -1;
        return 0;
    });
});

// Form validation schema
const formSchema = toTypedSchema(z.object({
    name: z.string().min(2, "Name must be at least 2 characters").max(255),
}));

const formData = ref({
    name: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Candidates',
        href: '/candidates',
    },
    {
        title: 'Positions',
        href: '/candidates/positions',
    },
];

const submitPosition = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post('/api/positions', formData.value, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        toast.success(response.data.message);
        formData.value.name = '';
        isDialogOpen.value = false;

        // Refresh only the positions data
        router.reload({
            only: ['positions']
        });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response) {
            if (error.response.status === 422) {
                // Validation errors
                const errors = error.response.data.errors;
                Object.values(errors).flat().forEach(message => {
                    toast.error(message);
                });
            } else {
                toast.error(error.response.data.message || 'Failed to create position');
            }
        } else {
            toast.error('An unexpected error occurred');
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>

    <Head title="Positions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex justify-between gap-2">
                <TitleHeader title="Positions" description="List of Candidate Positions during Election" />
                <Dialog v-model:open="isDialogOpen">
                    <DialogTrigger as-child>
                        <Button variant="default">
                            Add New Position
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle>Create New Position</DialogTitle>
                            <DialogDescription>
                                Enter the details for the new position.
                            </DialogDescription>
                        </DialogHeader>

                        <Form :validation-schema="formSchema" @submit="submitPosition">
                            <div class="grid gap-4 py-4">
                                <FormField v-slot="{ componentField }" name="name">
                                    <FormItem>
                                        <FormLabel>Position Name</FormLabel>
                                        <FormControl>
                                            <Input type="text" placeholder="Enter position name" v-bind="componentField"
                                                v-model="formData.name" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>
                            </div>
                            <DialogFooter>
                                <Button type="button" variant="outline" @click="isDialogOpen = false">Cancel</Button>
                                <Button type="submit" :disabled="isLoading">
                                    <span v-if="!isLoading">Create Position</span>
                                    <span v-else>Creating...</span>
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Positions List -->
            <div class="bg-card rounded-lg [&_th]:text-center [&_td]:text-center">
                <div class="border rounded-lg overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="cursor-pointer select-none hover:bg-muted/50 transition-colors"
                                    @click="sortBy('id')">
                                    <div class="flex items-center justify-center gap-1.5">
                                        ID
                                        <ArrowUpWideNarrow v-if="sortKey === 'id' && sortOrder === 'asc'"
                                            class="w-4 h-4" />
                                        <ArrowDownWideNarrow v-else-if="sortKey === 'id' && sortOrder === 'desc'"
                                            class="w-4 h-4" />
                                        <ArrowUpDown v-else class="w-4 h-4 text-muted-foreground opacity-50" />
                                    </div>
                                </TableHead>

                                <TableHead class="cursor-pointer select-none hover:bg-muted/50 transition-colors"
                                    @click="sortBy('name')">
                                    <div class="flex items-center justify-center gap-1.5">
                                        Name
                                        <ArrowDownWideNarrow v-if="sortKey === 'name' && sortOrder === 'asc'"
                                            class="w-4 h-4" />
                                        <ArrowUpWideNarrow v-else-if="sortKey === 'name' && sortOrder === 'desc'"
                                            class="w-4 h-4" />
                                        <ArrowUpDown v-else class="w-4 h-4 text-muted-foreground opacity-50" />
                                    </div>
                                </TableHead>

                                <TableHead class="cursor-pointer select-none hover:bg-muted/50 transition-colors"
                                    @click="sortBy('created_at')">
                                    <div class="flex items-center justify-center gap-1.5">
                                        Created At
                                        <ArrowDownWideNarrow v-if="sortKey === 'created_at' && sortOrder === 'asc'"
                                            class="w-4 h-4" />
                                        <ArrowUpWideNarrow v-else-if="sortKey === 'created_at' && sortOrder === 'desc'"
                                            class="w-4 h-4" />
                                        <ArrowUpDown v-else class="w-4 h-4 text-muted-foreground opacity-50" />
                                    </div>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="sortedPositions.length === 0">
                                <TableCell colspan="4" class="py-8 text-muted-foreground">
                                    No positions found
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="position in sortedPositions" :key="position.id">
                                <TableCell>{{ position.id }}</TableCell>
                                <TableCell>{{ position.name }}</TableCell>
                                <TableCell>{{ new Date(position.created_at).toLocaleDateString() }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>