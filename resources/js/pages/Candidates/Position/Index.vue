<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import TitleHeader from '@/components/ui/title-header/header.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import axios from 'axios';
import { ArrowDownWideNarrow, ArrowUpDown, ArrowUpWideNarrow, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';
import * as z from 'zod';

const toast = useToast();
const isLoading = ref(false);
const isDialogOpen = ref(false);
const isDeleting = ref(false);
const showDeleteDialog = ref(false);
const showPasswordDialog = ref(false);
const deletePassword = ref('');
const selectedPosition = ref<{ id: number; name: string } | null>(null);

const props = defineProps<{
    positions: Array<{
        id: number;
        name: string;
        created_at: string;
        updated_at: string;
    }>;
}>();

// --- DRY Sorting Logic ---
type SortKey = 'id' | 'name' | 'created_at';

const tableColumns: Array<{ key: SortKey; label: string }> = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Name' },
    { key: 'created_at', label: 'Created At' },
];

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
    if (!sortKey.value) return props.positions;

    return [...props.positions].sort((a, b) => {
        let aValue: string | number = a[sortKey.value as SortKey];
        let bValue: string | number = b[sortKey.value as SortKey];

        if (sortKey.value === 'name') {
            aValue = (aValue as string).toLowerCase();
            bValue = (bValue as string).toLowerCase();
        } else if (sortKey.value === 'created_at') {
            aValue = new Date(aValue as string).getTime();
            bValue = new Date(bValue as string).getTime();
        }

        if (aValue < bValue) return sortOrder.value === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortOrder.value === 'asc' ? 1 : -1;
        return 0;
    });
});

// Form validation schema
const formSchema = toTypedSchema(
    z.object({
        name: z.string().min(2, 'Name must be at least 2 characters').max(255),
    }),
);

const formData = ref({
    name: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Candidates', href: '/candidates' },
    { title: 'Positions', href: '/candidates/positions' },
];

const submitPosition = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post('/api/positions', formData.value, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });

        toast.success(response.data.message);
        formData.value.name = '';
        isDialogOpen.value = false;

        router.reload({ only: ['positions'] });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response) {
            if (error.response.status === 422) {
                const errors = error.response.data.errors;
                Object.values(errors)
                    .flat()
                    .forEach((message) => {
                        toast.error(message as string);
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

const openDeleteDialog = (position: { id: number; name: string }) => {
    selectedPosition.value = position;
    deletePassword.value = '';
    showDeleteDialog.value = true;
};

const proceedToPasswordConfirmation = () => {
    showDeleteDialog.value = false;
    showPasswordDialog.value = true;
};

const deletePosition = async () => {
    if (!selectedPosition.value) return;

    if (!deletePassword.value) {
        toast.error('Password is required to confirm deletion');
        return;
    }

    isDeleting.value = true;

    try {
        await axios.post('/election/verify-password', {
            password: deletePassword.value,
        });

        await axios.delete(`/api/positions/${selectedPosition.value.id}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
        });

        toast.success('Position deleted successfully!');
        showPasswordDialog.value = false;
        router.reload({ only: ['positions'] });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response) {
            if (error.response.status === 422) {
                toast.error('Incorrect admin password.');
            } else {
                toast.error(error.response.data.message || 'Failed to delete position');
            }
        } else {
            toast.error('An unexpected error occurred');
        }
    } finally {
        isDeleting.value = false;
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
                        <Button variant="default">Add New Position</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle>Create New Position</DialogTitle>
                            <DialogDescription> Enter the details for the new position. </DialogDescription>
                        </DialogHeader>

                        <Form :validation-schema="formSchema" @submit="submitPosition">
                            <div class="grid gap-4 py-4">
                                <FormField v-slot="{ componentField }" name="name">
                                    <FormItem>
                                        <FormLabel>Position Name</FormLabel>
                                        <FormControl>
                                            <Input type="text" placeholder="Enter position name" v-bind="componentField" v-model="formData.name" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>
                            </div>
                            <DialogFooter>
                                <Button type="button" variant="outline" @click="isDialogOpen = false">Cancel</Button>
                                <Button type="submit" :disabled="isLoading">
                                    {{ isLoading ? 'Creating...' : 'Create Position' }}
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Positions List -->
            <div class="rounded-lg bg-card [&_td]:text-center [&_th]:text-center">
                <div class="overflow-hidden rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead
                                    v-for="col in tableColumns"
                                    :key="col.key"
                                    class="cursor-pointer select-none transition-colors hover:bg-muted/50"
                                    @click="sortBy(col.key)"
                                >
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{ col.label }}
                                        <ArrowDownWideNarrow v-if="sortKey === col.key && sortOrder === 'asc'" class="h-4 w-4" />
                                        <ArrowUpWideNarrow v-else-if="sortKey === col.key && sortOrder === 'desc'" class="h-4 w-4" />
                                        <ArrowUpDown v-else class="h-4 w-4 text-muted-foreground opacity-50" />
                                    </div>
                                </TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="sortedPositions.length === 0">
                                <TableCell colspan="4" class="py-8 text-muted-foreground"> No positions found </TableCell>
                            </TableRow>

                            <TableRow v-for="position in sortedPositions" :key="position.id">
                                <TableCell>{{ position.id }}</TableCell>
                                <TableCell>{{ position.name }}</TableCell>
                                <TableCell>{{ new Date(position.created_at).toLocaleDateString() }}</TableCell>
                                <TableCell>
                                    <div>
                                        <Button size="sm" variant="destructive" @click="openDeleteDialog(position)" :disabled="isDeleting">
                                            <Trash class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <AlertDialog v-model:open="showDeleteDialog">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Position Confirmation</AlertDialogTitle>
                        <AlertDialogDescription>
                            <p>
                                This action cannot be undone. This will permanently delete
                                <span class="font-semibold">{{ selectedPosition?.name }}</span
                                >.
                            </p>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction @click="proceedToPasswordConfirmation"> Continue </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <AlertDialog v-model:open="showPasswordDialog">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Password Required</AlertDialogTitle>
                        <AlertDialogDescription>
                            <div class="space-y-4 pt-2">
                                <p class="font-medium text-foreground">Confirm your password to delete the position.</p>
                                <Input type="password" v-model="deletePassword" placeholder="Enter admin password" @keyup.enter="deletePosition" />
                            </div>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="deletePassword = ''" :disabled="isDeleting">Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            :disabled="isDeleting || !deletePassword"
                            @click.prevent="deletePosition"
                            class="bg-red-600 hover:bg-red-700 disabled:opacity-50"
                        >
                            <span v-if="!isDeleting">Delete Position</span>
                            <span v-else>Deleting...</span>
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </AppLayout>
</template>
