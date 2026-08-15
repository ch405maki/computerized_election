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
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import TitleHeader from '@/components/ui/title-header/header.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import axios from 'axios';
import { ArrowDown, ArrowUpDown, ArrowUp, Trash, FilePenLine } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';
import * as z from 'zod';

// --- Types & Interfaces ---
type SortKey = 'id' | 'name' | 'created_at';
type ActionType = 'edit' | 'delete' | null;

interface Position {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    positions: Position[];
}>();

// --- Initialization ---
const toast = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Candidates', href: '/candidates' },
    { title: 'Positions', href: '/candidates/positions' },
];

// --- Sorting Logic ---
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

// --- DRY State Management ---
const selectedPosition = ref<Position | null>(null);
const pendingAction = ref<ActionType>(null);

// Form Dialog State
const isFormDialogOpen = ref(false);
const isSubmitting = ref(false);
const isEditing = computed(() => !!selectedPosition.value);

const formSchema = toTypedSchema(
    z.object({
        name: z.string().min(2, 'Name must be at least 2 characters').max(255),
    }),
);

const formData = ref({ name: '' });

// Password Dialog State
const isPasswordDialogOpen = ref(false);
const isProcessingAuth = ref(false);
const adminPassword = ref('');

// Delete Confirmation State
const isDeleteDialogOpen = ref(false);

// --- DRY Helpers ---
const getApiConfig = () => ({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
});

const handleApiError = (error: unknown, defaultMessage: string) => {
    if (axios.isAxiosError(error) && error.response) {
        if (error.response.status === 422) {
            const errors = error.response.data.errors;
            if (errors) {
                Object.values(errors).flat().forEach((message) => toast.error(message as string));
            } else {
                toast.error('Incorrect admin password.');
            }
        } else {
            toast.error(error.response.data.message || defaultMessage);
        }
    } else {
        toast.error('An unexpected error occurred');
    }
};

// --- Action Triggers ---
const openCreate = () => {
    selectedPosition.value = null;
    formData.value.name = '';
    isFormDialogOpen.value = true;
};

const openEdit = (position: Position) => {
    selectedPosition.value = position;
    pendingAction.value = 'edit';
    adminPassword.value = '';
    isPasswordDialogOpen.value = true;
};

const openDelete = (position: Position) => {
    selectedPosition.value = position;
    isDeleteDialogOpen.value = true;
};

const confirmDeleteAuth = () => {
    isDeleteDialogOpen.value = false;
    pendingAction.value = 'delete';
    adminPassword.value = '';
    isPasswordDialogOpen.value = true;
};

// --- Execution Handlers ---
const verifyPasswordAndProceed = async () => {
    if (!adminPassword.value) return toast.error('Password is required');

    isProcessingAuth.value = true;
    try {
        // 1. Verify Password
        await axios.post('/elections/verify-password', { password: adminPassword.value });

        // 2. Proceed based on action type
        if (pendingAction.value === 'edit') {
            formData.value.name = selectedPosition.value!.name;
            isPasswordDialogOpen.value = false;
            isFormDialogOpen.value = true;
        } else if (pendingAction.value === 'delete') {
            await axios.delete(`/api/positions/${selectedPosition.value!.id}`, getApiConfig());
            toast.success('Position deleted successfully!');
            isPasswordDialogOpen.value = false;
            router.reload({ only: ['positions'] });
        }
    } catch (error) {
        handleApiError(error, 'An error occurred during verification.');
    } finally {
        isProcessingAuth.value = false;
    }
};

const submitForm = async () => {
    if (isEditing.value && formData.value.name === selectedPosition.value?.name) {
        toast.info('No changes detected');
        isFormDialogOpen.value = false;
        return;
    }

    isSubmitting.value = true;
    try {
        const method = isEditing.value ? 'patch' : 'post';
        const url = isEditing.value ? `/api/positions/${selectedPosition.value!.id}` : '/api/positions';

        const response = await axios({ method, url, data: formData.value, ...getApiConfig() });

        toast.success(response.data.message || `Position ${isEditing.value ? 'updated' : 'created'} successfully`);
        isFormDialogOpen.value = false;
        router.reload({ only: ['positions'] });
    } catch (error) {
        handleApiError(error, `Failed to ${isEditing.value ? 'update' : 'create'} position`);
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <Head title="Positions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex justify-between gap-2">
                <TitleHeader title="Positions" description="List of Candidate Positions during Election" />
                <Button variant="default" @click="openCreate">Add New Position</Button>
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
                                    class="cursor-pointer select-none transition-colors"
                                    @click="sortBy(col.key)"
                                >
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{ col.label }}
                                        <ArrowDown v-if="sortKey === col.key && sortOrder === 'asc'" class="h-4 w-4 text-white" />
                                        <ArrowUp v-else-if="sortKey === col.key && sortOrder === 'desc'" class="h-4 w-4 text-white" />
                                        <ArrowUpDown v-else class="h-4 w-4 text-white" />
                                    </div>
                                </TableHead>
                                <TableHead class="text-right pr-6">Actions</TableHead>
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
                                        <Button size="sm" variant="outline" class="mr-2" @click="openEdit(position)">
                                            <FilePenLine class="h-4 w-4" />
                                        </Button>
                                        <Button size="sm" variant="destructive" @click="openDelete(position)">
                                            <Trash class="h-4 w-4" />
                                        </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <!-- Unified Create/Edit Form Dialog -->
            <Dialog v-model:open="isFormDialogOpen">
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>{{ isEditing ? 'Edit Position' : 'Create New Position' }}</DialogTitle>
                        <DialogDescription>
                            {{ isEditing ? 'Make changes to the position name here. Click save when you\'re done.' : 'Enter the details for the new position.' }}
                        </DialogDescription>
                    </DialogHeader>

                    <Form :validation-schema="formSchema" @submit="submitForm">
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
                            <Button type="button" variant="outline" @click="isFormDialogOpen = false">Cancel</Button>
                            <Button type="submit" :disabled="isSubmitting">
                                {{ isSubmitting ? 'Saving...' : (isEditing ? 'Save Changes' : 'Create Position') }}
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>

            <!-- Unified Password Verification Dialog -->
            <AlertDialog v-model:open="isPasswordDialogOpen">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Password Required</AlertDialogTitle>
                        <AlertDialogDescription>
                            <div class="space-y-4 pt-2">
                                <p class="font-medium text-foreground">
                                    Confirm your password to {{ pendingAction }} the position.
                                </p>
                                <Input type="password" v-model="adminPassword" placeholder="Enter admin password" @keyup.enter="verifyPasswordAndProceed" />
                            </div>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="adminPassword = ''" :disabled="isProcessingAuth">Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            :disabled="isProcessingAuth || !adminPassword"
                            @click.prevent="verifyPasswordAndProceed"
                            :class="{ 'bg-red-600 hover:bg-red-700': pendingAction === 'delete' }"
                        >
                            <span v-if="!isProcessingAuth">
                                {{ pendingAction === 'delete' ? 'Delete Position' : 'Continue' }}
                            </span>
                            <span v-else>
                                {{ pendingAction === 'delete' ? 'Deleting...' : 'Verifying...' }}
                            </span>
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <!-- Delete Warning Dialog -->
            <AlertDialog v-model:open="isDeleteDialogOpen">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Position Confirmation</AlertDialogTitle>
                        <AlertDialogDescription>
                            <p>
                                This action cannot be undone. This will permanently delete
                                <span class="font-semibold">{{ selectedPosition?.name }}</span>.
                            </p>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction @click="confirmDeleteAuth"> Continue </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </AppLayout>
</template>