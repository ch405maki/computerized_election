<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';

const toast = useToast();
const isLoading = ref(false);

// Define props
const props = defineProps<{
    positions: Array<{
        id: number;
        name: string;
        created_at: string;
        updated_at: string;
    }>;
}>();

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
        // Refresh the page to get updated positions list
        window.location.reload();
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
        <div class="container mx-auto py-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <!-- Create Position Form -->
                <div class="bg-card rounded-lg shadow-sm border p-6">
                    <h2 class="text-xl font-bold mb-4">Create New Position</h2>
                    <Form :validation-schema="formSchema" @submit="submitPosition">
                        <div class="space-y-4">
                            <FormField v-slot="{ componentField }" name="name">
                                <FormItem>
                                    <FormLabel>Position Name</FormLabel>
                                    <FormControl>
                                        <Input 
                                            type="text" 
                                            placeholder="Enter position name" 
                                            v-bind="componentField"
                                            v-model="formData.name"
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <div class="flex justify-end">
                                <Button type="submit" :disabled="isLoading">
                                    <span v-if="!isLoading">Create Position</span>
                                    <span v-else>Creating...</span>
                                </Button>
                            </div>
                        </div>
                    </Form>
                </div>

                <!-- Positions List -->
                <div class="bg-card rounded-lg shadow-sm border p-6">
                    <h2 class="text-xl font-bold mb-4">Existing Positions</h2>
                    <div class="border rounded-lg overflow-hidden">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Created At</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="positions.length === 0">
                                    <TableCell colspan="3" class="text-center py-8 text-muted-foreground">
                                        No positions found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="position in positions" :key="position.id">
                                    <TableCell>{{ position.id }}</TableCell>
                                    <TableCell>{{ position.name }}</TableCell>
                                    <TableCell>{{ new Date(position.created_at).toLocaleDateString() }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>