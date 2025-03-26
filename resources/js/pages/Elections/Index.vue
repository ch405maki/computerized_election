<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';
import { format } from 'date-fns';

const toast = useToast();
const isLoading = ref(false);
const startDate = ref<Date>();
const endDate = ref<Date>();

const props = defineProps<{
    elections: Array<{
        id: number;
        name: string;
        status: string;
        start_date: string;
        end_date: string;
    }>;
}>();

const formSchema = toTypedSchema(z.object({
    name: z.string().min(2, "Name must be at least 2 characters").max(255),
    status: z.enum(['active', 'completed', 'upcoming']),
}));

const formData = ref({
    name: '',
    status: 'upcoming' as 'active' | 'completed' | 'upcoming',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Elections', href: '/elections' },
];

const submitElection = async () => {
    if (!startDate.value || !endDate.value) {
        toast.error('Please select both start and end dates');
        return;
    }

    isLoading.value = true;
    try {
        const payload = {
            ...formData.value,
            start_date: format(startDate.value, 'yyyy-MM-dd'),
            end_date: format(endDate.value, 'yyyy-MM-dd'),
        };

        const response = await axios.post('/api/elections', payload, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        toast.success(response.data.message);
        // Reset form
        formData.value = {
            name: '',
            status: 'upcoming'
        };
        startDate.value = undefined;
        endDate.value = undefined;
        // Refresh the page to show new election
        window.location.reload();
    } catch (error) {
        if (axios.isAxiosError(error) && error.response) {
            if (error.response.status === 422) {
                const errors = error.response.data.errors;
                Object.values(errors).flat().forEach(message => {
                    toast.error(message);
                });
            } else {
                toast.error(error.response.data.message || 'Failed to create election');
            }
        } else {
            toast.error('An unexpected error occurred');
        }
    } finally {
        isLoading.value = false;
    }
};

const formatDateDisplay = (date?: Date) => {
    return date ? format(date, 'PPP') : 'Pick a date';
};

const isDateDisabled = (date: Date) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return date < today;
};

const isEndDateDisabled = (date: Date) => {
    if (!startDate.value) return isDateDisabled(date);
    return date < startDate.value;
};
</script>

<template>
    <Head title="Elections" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-8">
            <div class="grid grid-cols-1 gap-8">
                <!-- Create Election Form -->
                <div class="bg-card rounded-lg shadow-sm border p-6">
                    <h2 class="text-2xl font-bold mb-6">Create New Election</h2>
                    <Form :validation-schema="formSchema" @submit="submitElection">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField v-slot="{ componentField }" name="name">
                                <FormItem>
                                    <FormLabel>Election Name</FormLabel>
                                    <FormControl>
                                        <Input 
                                            type="text" 
                                            placeholder="Enter election name" 
                                            v-bind="componentField" 
                                            v-model="formData.name" 
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ componentField }" name="status">
                                <FormItem>
                                    <FormLabel>Status</FormLabel>
                                    <Select v-bind="componentField" v-model="formData.status">
                                        <FormControl>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                        </FormControl>
                                        <SelectContent>
                                            <SelectItem value="upcoming">Upcoming</SelectItem>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="completed">Completed</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ componentField }" name="start_date">
    <FormItem>
        <FormLabel>Start Date</FormLabel>
        <FormControl>
            <Input
                type="date"
                v-bind="componentField"
                v-model="startDate"
            />
        </FormControl>
        <FormMessage />
    </FormItem>
</FormField>

<FormField v-slot="{ componentField }" name="end_date">
    <FormItem>
        <FormLabel>End Date</FormLabel>
        <FormControl>
            <Input
                type="date"
                v-bind="componentField"
                v-model="endDate"
            />
        </FormControl>
        <FormMessage />
    </FormItem>
</FormField>


                        </div>

                        <div class="flex justify-end mt-6">
                            <Button type="submit" :disabled="isLoading">
                                <span v-if="!isLoading">Create Election</span>
                                <span v-else>Creating...</span>
                            </Button>
                        </div>
                    </Form>
                </div>

                <!-- Elections List -->
                <div class="bg-card rounded-lg shadow-sm border p-6">
                    <h2 class="text-2xl font-bold mb-6">Current Elections</h2>
                    <div class="border rounded-lg overflow-hidden">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Start Date</TableHead>
                                    <TableHead>End Date</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="props.elections.length === 0">
                                    <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                        No elections found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="election in props.elections" :key="election.id">
                                    <TableCell>{{ election.name }}</TableCell>
                                    <TableCell>
                                        <span :class="{
                                            'text-green-600': election.status === 'active',
                                            'text-blue-600': election.status === 'upcoming',
                                            'text-gray-600': election.status === 'completed'
                                        }">
                                            {{ election.status }}
                                        </span>
                                    </TableCell>
                                    <TableCell>{{ new Date(election.start_date).toLocaleDateString() }}</TableCell>
                                    <TableCell>{{ new Date(election.end_date).toLocaleDateString() }}</TableCell>
                                    <TableCell>
                                        <Button variant="outline" size="sm" class="mr-2">Edit</Button>
                                        <Button variant="destructive" size="sm">Delete</Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>