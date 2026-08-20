<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toTypedSchema } from '@vee-validate/zod';
import axios from 'axios';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';
import * as z from 'zod';

// Define the prop to accept the permission flag from the parent component/page
const props = withDefaults(
    defineProps<{
        canCreate?: boolean;
    }>(),
    {
        canCreate: false, // Defaults to false to ensure strict access control
    },
);

const toast = useToast();
const isLoading = ref(false);
const isDialogOpen = ref(false);
const startDate = ref<string>('');
const endDate = ref<string>('');

const formSchema = toTypedSchema(
    z.object({
        name: z.string().min(2, 'Name must be at least 2 characters').max(255),
        status: z.enum(['active', 'completed', 'upcoming']),
        start_date: z.string().min(1, 'Start date is required'),
        end_date: z.string().min(1, 'End date is required'),
        // Return undefined for empty values
        required_percentage: z.preprocess(
            (val) => (val === '' || val === null || val === undefined ? undefined : Number(val)),
            z.number()
                .min(0, 'Percentage cannot be less than 0')
                .max(100, 'Percentage cannot exceed 100')
                .optional() // Use optional instead of nullable
        ),
    }),
);

const formData = ref({
    name: '',
    status: 'upcoming' as 'active' | 'completed' | 'upcoming',
    start_date: '',
    end_date: '',
    // Change from null to undefined
    required_percentage: undefined as number | undefined, 
});

const date = new Date();
const todayDateString = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

// Ensure the end date cannot be earlier than the chosen start date (or today)
const minEndDate = computed(() => {
    return startDate.value ? startDate.value : todayDateString;
});

const submitElection = async () => {
    // Extra guard to prevent submission if permission is missing
    if (!props.canCreate) {
        toast.error('You do not have permission to create an election.');
        return;
    }

    isLoading.value = true;
    try {
        const payload = {
            name: formData.value.name,
            status: formData.value.status,
            start_date: startDate.value,
            end_date: endDate.value,
            required_percentage: formData.value.required_percentage,
        };

        const response = await axios.post('/api/elections', payload, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });

        toast.success(response.data.message);
        resetForm();
        isDialogOpen.value = false;
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    } catch (error) {
        handleError(error);
    } finally {
        isLoading.value = false;
    }
};

const resetForm = () => {
    formData.value = {
        name: '',
        status: 'upcoming',
        start_date: '',
        end_date: '',
        required_percentage: undefined, 
    };
    startDate.value = '';
    endDate.value = '';
};

const handleError = (error: unknown) => {
    if (axios.isAxiosError(error)) {
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            Object.values(errors)
                .flat()
                .forEach((message) => {
                    toast.error(message as string);
                });
        } else {
            toast.error(error.response?.data?.message || 'Failed to create election');
        }
    } else {
        toast.error('An unexpected error occurred');
    }
};
</script>

<template>
    <!-- Conditionally render the entire Dialog based on permissions -->
    <Dialog v-model:open="isDialogOpen" v-if="canCreate">
        <DialogTrigger as-child>
            <Button variant="default" class="ml-auto"> Create New Election </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[625px]">
            <DialogHeader>
                <DialogTitle>Create New Election</DialogTitle>
                <DialogDescription> Fill out the form to create a new election. Click save when you're done. </DialogDescription>
            </DialogHeader>

            <Form :validation-schema="formSchema" @submit="submitElection" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <FormField v-slot="{ componentField }" name="name">
                        <FormItem>
                            <FormLabel>Election Name</FormLabel>
                            <FormControl>
                                <Input type="text" placeholder="Enter election name" v-bind="componentField" v-model="formData.name" />
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
                                <Input type="date" v-bind="componentField" v-model="startDate" :min="todayDateString" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="end_date">
                        <FormItem>
                            <FormLabel>End Date</FormLabel>
                            <FormControl>
                                <Input type="date" v-bind="componentField" v-model="endDate" :min="minEndDate" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                    
                    <FormField v-slot="{ componentField }" name="required_percentage">
                        <FormItem>
                            <FormLabel>Voting Threshold % (Optional)</FormLabel>
                            <FormControl>
                                <Input type="number" step="0.01" min="0" max="100" placeholder="e.g., 50.00" v-bind="componentField" v-model="formData.required_percentage" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <div class="flex justify-end gap-4">
                    <Button
                        type="button"
                        variant="outline"
                        @click="
                            () => {
                                resetForm();
                                isDialogOpen = false;
                            }
                        "
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="isLoading">
                        <span v-if="!isLoading">Create Election</span>
                        <span v-else>
                            <svg
                                class="-ml-1 mr-2 inline h-4 w-4 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Creating...
                        </span>
                    </Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>
</template>