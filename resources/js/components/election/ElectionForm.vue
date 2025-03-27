<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';
import { format } from 'date-fns';

const toast = useToast();
const isLoading = ref(false);
const startDate = ref<string>('');
const endDate = ref<string>('');

const formSchema = toTypedSchema(z.object({
    name: z.string().min(2, "Name must be at least 2 characters").max(255),
    status: z.enum(['active', 'completed', 'upcoming']),
}));

const formData = ref({
    name: '',
    status: 'upcoming' as 'active' | 'completed' | 'upcoming',
});

const submitElection = async () => {
    if (!startDate.value || !endDate.value) {
        toast.error('Please select both start and end dates');
        return;
    }

    isLoading.value = true;
    try {
        const payload = {
            ...formData.value,
            start_date: startDate.value,
            end_date: endDate.value,
        };

        const response = await axios.post('/api/elections', payload, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        toast.success(response.data.message);
        resetForm();
        setTimeout(() => {
            window.location.reload();
        }, 3000);
        
    } catch (error) {
        handleError(error);
    } finally {
        isLoading.value = false;
    }
};

const resetForm = () => {
    formData.value = {
        name: '',
        status: 'upcoming'
    };
    startDate.value = '';
    endDate.value = '';
};

const handleError = (error: unknown) => {
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
};
</script>

<template>
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
</template>