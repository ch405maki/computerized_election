<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { toTypedSchema } from '@vee-validate/zod';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';
import * as z from 'zod';

// Types
interface VotingThreshold {
    required_percentage: number | string | null;
}

interface Election {
    id: number;
    name: string;
    status: 'active' | 'completed' | 'upcoming';
    start_date: string;
    end_date: string;
    voting_threshold?: VotingThreshold;
}

interface ElectionUpdatePayload {
    name?: string;
    status?: 'active' | 'completed' | 'upcoming';
    start_date?: string;
    end_date?: string;
    required_percentage?: number | null;
}

interface ApiResponse<T> {
    message?: string;
    data: T;
    errors?: Record<string, string[]>;
}

interface ElectionResponse extends ApiResponse<Election> {}

// Constants and reactive state
const toast = useToast();
const isLoading = ref(false);

const csrfTokenMeta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
const csrfToken = csrfTokenMeta?.content ?? '';

const props = defineProps<{
    election: Election;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'updated', election: Election): void;
}>();

// Helpers
const emptyToNull = (val: unknown) => (val === '' || val === null ? null : Number(val));

// Formats dates safely to local YYYY-MM-DD
const formatDate = (dateInput?: string | Date | null): string => {
    if (!dateInput) return '';
    const d = new Date(dateInput);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};

const todayDateString = formatDate(new Date());

const getInitialFormData = (election: Election): ElectionUpdatePayload => ({
    name: election.name,
    status: election.status,
    start_date: formatDate(election.start_date),
    end_date: formatDate(election.end_date),
    // Ensure strict number typing for accurate change comparison later
    required_percentage: election.voting_threshold?.required_percentage ? Number(election.voting_threshold.required_percentage) : null,
});

// Computed & Validation
const minEndDate = computed(() => formData.value.start_date || todayDateString);

const dateSchema = z
    .string()
    .min(1, 'Date is required')
    .refine((val) => !isNaN(Date.parse(val)), { message: 'Invalid date format' });

const formSchema = toTypedSchema(
    z.object({
        name: z.string().min(2, 'Name must be at least 2 characters').max(255).optional(),
        status: z.enum(['active', 'completed', 'upcoming']).optional(),
        start_date: dateSchema.optional(),
        end_date: dateSchema.optional(),
        required_percentage: z.preprocess(emptyToNull, z.number().min(0, 'Min is 0').max(100, 'Max is 100').nullable().optional()),
    }).refine(
        (data) => !(data.start_date && data.end_date) || new Date(data.end_date) >= new Date(data.start_date),
        { message: 'End date cannot be earlier than start date', path: ['end_date'] }
    ),
);

// State Initialization & Syncing
const formData = ref<ElectionUpdatePayload>(getInitialFormData(props.election));

watch(
    () => props.election,
    (newElection) => {
        formData.value = getInitialFormData(newElection);
    },
    { deep: true },
);

// Actions
const getChangedFields = (): Partial<ElectionUpdatePayload> => {
    const payload: Partial<ElectionUpdatePayload> = {};
    const originalData = getInitialFormData(props.election);

    // Dynamically check for changes instead of hardcoding each property
    for (const [key, value] of Object.entries(formData.value)) {
        const k = key as keyof ElectionUpdatePayload;
        if (value !== originalData[k]) {
            payload[k] = value as any;
        }
    }

    return payload;
};

const updateElection = async () => {
    const payload = getChangedFields();

    if (Object.keys(payload).length === 0) {
        toast.info('No changes detected');
        emit('close');
        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.patch<ElectionResponse>(`/api/elections/${props.election.id}`, payload, {
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        toast.success(response.data.message || 'Election updated successfully');
        emit('updated', response.data.data);
        emit('close');
    } catch (error: unknown) {
        handleUpdateError(error);
    } finally {
        isLoading.value = false;
    }
};

// Error Handling
const handleUpdateError = (error: unknown) => {
    if (axios.isAxiosError<{ message?: string; errors?: Record<string, string[]> }>(error)) {
        if (error.response?.status === 422 && error.response.data.errors) {
            Object.entries(error.response.data.errors).forEach(([field, messages]) => {
                messages.forEach((msg) => toast.error(`${field}: ${msg}`));
            });
        } else {
            toast.error(error.response?.data?.message || 'Failed to update election');
        }
    } else {
        toast.error((error as Error).message || 'An unexpected error occurred');
        console.error('Update error:', error);
    }
};
</script>

<template>
    <Sheet defaultOpen @update:open="(val) => !val && $emit('close')">
        <SheetContent side="right" class="w-full sm:max-w-md overflow-y-auto">
            <SheetHeader>
                <SheetTitle>Edit Election</SheetTitle>
                <SheetDescription> Make changes to the election here. Click save when you're done. </SheetDescription>
            </SheetHeader>

            <Form :validation-schema="formSchema" @submit="updateElection" class="mt-4 space-y-6">
                
                <!-- Main fields -->
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

                <div class="grid grid-cols-2 gap-4">
                    <FormField v-slot="{ componentField }" name="start_date">
                        <FormItem>
                            <FormLabel>Start Date</FormLabel>
                            <FormControl>
                                <Input type="date" v-bind="componentField" v-model="formData.start_date" :min="todayDateString" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="end_date">
                        <FormItem>
                            <FormLabel>End Date</FormLabel>
                            <FormControl>
                                <Input type="date" v-bind="componentField" v-model="formData.end_date" :min="minEndDate" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <!-- Threshold fields -->
                <div class="border-t pt-4 space-y-4">
                    <h3 class="font-medium text-sm text-muted-foreground">Winning Thresholds (Optional)</h3>
                    
                    <FormField v-slot="{ componentField }" name="required_percentage">
                        <FormItem>
                            <FormLabel>Required Percentage (%)</FormLabel>
                            <FormControl>
                                <Input
                                    type="number"
                                    step="0.01"
                                    placeholder="e.g., 50.01"
                                    v-bind="componentField"
                                    :model-value="formData.required_percentage ?? undefined"
                                    @update:model-value="formData.required_percentage = $event === '' ? null : Number($event)"
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <div class="flex justify-end gap-4 pt-4 pb-8">
                    <Button type="button" variant="outline" @click="$emit('close')"> Cancel </Button>
                    <Button type="submit" :disabled="isLoading">
                        <span v-if="!isLoading">Save Changes</span>
                        <span v-else>
                            <svg class="-ml-1 mr-2 inline h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                    </Button>
                </div>
            </Form>
        </SheetContent>
    </Sheet>
</template>