<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
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
        start_date: z.string().min(1, 'Start date and time are required'),
        end_date: z.string().min(1, 'End date and time are required'),
        voting_start_time: z.string().optional(),
        voting_end_time: z.string().optional(),
        // Return undefined for empty values
        required_percentage: z.preprocess(
            (val) => (val === '' || val === null || val === undefined ? undefined : Number(val)),
            z.number()
                .min(0, 'Percentage cannot be less than 0')
                .max(100, 'Percentage cannot exceed 100')
                .optional() // Use optional instead of nullable
        ),
    }).refine((data) => {
        if (data.voting_start_time && data.voting_end_time) {
            return data.voting_end_time > data.voting_start_time;
        }
        return true;
    }, {
        message: "Closing time must be after opening time",
        path: ["voting_end_time"],
    }),
);

const formData = ref({
    name: '',
    required_percentage: undefined as number | undefined, 
    voting_start_time: '',
    voting_end_time: '',
});

// Create the YYYY-MM-DDThh:mm string for datetime-local min attributes
const date = new Date();
const year = date.getFullYear();
const month = String(date.getMonth() + 1).padStart(2, '0');
const day = String(date.getDate()).padStart(2, '0');
const hours = String(date.getHours()).padStart(2, '0');
const minutes = String(date.getMinutes()).padStart(2, '0');
const todayDateTimeString = `${year}-${month}-${day}T${hours}:${minutes}`;

// Ensure the end date cannot be earlier than the chosen start date (or today)
const minEndDate = computed(() => {
    return startDate.value ? startDate.value : todayDateTimeString;
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
            start_date: startDate.value,
            end_date: endDate.value,
            voting_start_time: formData.value.voting_start_time || null,
            voting_end_time: formData.value.voting_end_time || null,
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
        required_percentage: undefined, 
        voting_start_time: '',
        voting_end_time: '',
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
                    <!-- Election Name -->
                    <FormField v-slot="{ componentField }" name="name">
                        <FormItem>
                            <FormLabel>Election Name</FormLabel>
                            <FormControl>
                                <Input type="text" placeholder="Enter election name" v-bind="componentField" v-model="formData.name" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <!-- Voting Threshold -->
                    <FormField v-slot="{ componentField }" name="required_percentage">
                        <FormItem>
                            <FormLabel>Voting Threshold % (Optional)</FormLabel>
                            <FormControl>
                                <Input type="number" step="0.01" min="0" max="100" placeholder="e.g., 50.00" v-bind="componentField" v-model="formData.required_percentage" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <!-- Start Date & Time -->
                    <FormField v-slot="{ componentField }" name="start_date">
                        <FormItem>
                            <FormLabel>Start Date & Time</FormLabel>
                            <FormControl>
                                <Input type="datetime-local" v-bind="componentField" v-model="startDate" :min="todayDateTimeString" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <!-- End Date & Time -->
                    <FormField v-slot="{ componentField }" name="end_date">
                        <FormItem>
                            <FormLabel>End Date & Time</FormLabel>
                            <FormControl>
                                <Input type="datetime-local" v-bind="componentField" v-model="endDate" :min="minEndDate" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                    
                    <!-- Daily Opening Time -->
                    <FormField v-slot="{ componentField }" name="voting_start_time">
                        <FormItem>
                            <FormLabel>Daily Opening Time </FormLabel>
                            <FormControl>
                                <Input type="time" v-bind="componentField" v-model="formData.voting_start_time" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <!-- Daily Closing Time -->
                    <FormField v-slot="{ componentField }" name="voting_end_time">
                        <FormItem>
                            <FormLabel>Daily Closing Time</FormLabel>
                            <FormControl>
                                <Input type="time" v-bind="componentField" v-model="formData.voting_end_time" />
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