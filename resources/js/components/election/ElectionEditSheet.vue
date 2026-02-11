<script setup lang="ts">
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';

// Types
interface Election {
  id: number;
  name: string;
  status: 'active' | 'completed' | 'upcoming';
  start_date: string;
  end_date: string;
}

interface ElectionUpdatePayload {
  name?: string;
  status?: 'active' | 'completed' | 'upcoming';
  start_date?: string;
  end_date?: string;
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

// Get CSRF token safely with type assertion
const csrfTokenMeta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
const csrfToken = csrfTokenMeta?.content ?? '';

// Props with explicit type
const props = defineProps<{
  election: Election;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'updated', election: Election): void;
}>();

// Enhanced validation schema with date validation
const dateSchema = z.string()
  .min(1, "Date is required")
  .refine(val => !isNaN(Date.parse(val)), {
    message: "Invalid date format"
  });

const formSchema = toTypedSchema(z.object({
  name: z.string()
    .min(2, "Name must be at least 2 characters")
    .max(255)
    .optional(),
  status: z.enum(['active', 'completed', 'upcoming']).optional(),
  start_date: dateSchema.optional(),
  end_date: dateSchema.optional()
}));

const currentDate = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString).toISOString().split('T')[0];
};


// Form data with explicit type and safe initialization
const formData = ref<ElectionUpdatePayload>({
  name: props.election.name,
  status: props.election.status,
  start_date: currentDate(props.election.start_date),
  end_date: currentDate(props.election.end_date)
});

// Improved update function with proper typing
const updateElection = async () => {
  const payload = getChangedFields();
  
  if (Object.keys(payload).length === 0) {
    toast.info('No changes detected');
    emit('close');
    return;
  }

  isLoading.value = true;
  
  try {
    const response = await axios.patch<ElectionResponse>(
      `/api/elections/${props.election.id}`,
      payload,
      {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      }
    );
    
    toast.success(response.data.message || 'Election updated successfully');
    emit('updated', response.data.data);
    emit('close');
    
  } catch (error: unknown) {
    handleUpdateError(error);
  } finally {
    isLoading.value = false;
  }
};

// Type-safe changed fields detection
const getChangedFields = (): ElectionUpdatePayload => {
  return (Object.keys(formData.value) as Array<keyof ElectionUpdatePayload>).reduce((acc, key) => {
    const formValue = formData.value[key];
    const propValue = props.election[key];

    if (JSON.stringify(formValue) !== JSON.stringify(propValue)) {
      // For dates, ensure consistent formatting
      if ((key === 'start_date' || key === 'end_date') && formValue) {
        acc[key] = new Date(formValue).toISOString().split('T')[0];
      } else {
        acc[key] = formValue as 'active' | 'completed' | 'upcoming';
      }
    }
    return acc;
  }, {} as ElectionUpdatePayload);
};

// Comprehensive error handling
const handleUpdateError = (error: unknown) => {
  if (axios.isAxiosError<{ message?: string; errors?: Record<string, string[]> }>(error)) {
    if (error.response?.status === 422 && error.response.data.errors) {
      handleValidationErrors(error.response.data.errors);
    } else {
      toast.error(error.response?.data?.message || 'Failed to update election');
    }
  } else if (error instanceof Error) {
    toast.error(error.message || 'An unexpected error occurred');
    console.error('Update error:', error);
  } else {
    toast.error('An unexpected error occurred');
    console.error('Unknown error:', error);
  }
};

// Validation error handler
const handleValidationErrors = (errors: Record<string, string[]>) => {
  Object.entries(errors).forEach(([field, messages]) => {
    messages.forEach(message => toast.error(`${field}: ${message}`));
  });
};
</script>

<template>
  <Sheet defaultOpen @update:open="(val) => !val && $emit('close')">
    <SheetContent side="right" class="w-full sm:max-w-md">
      <SheetHeader>
        <SheetTitle>Edit Election</SheetTitle>
        <SheetDescription>
          Make changes to the election here. Click save when you're done.
        </SheetDescription>
      </SheetHeader>
      
      <Form :validation-schema="formSchema" @submit="updateElection" class="space-y-6 mt-4">
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
                v-model="formData.start_date"
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
                v-model="formData.end_date"
              />
            </FormControl>
            <FormMessage />
          </FormItem>
        </FormField>

        <div class="flex justify-end gap-4 pt-4">
          <Button 
            type="button" 
            variant="outline" 
            @click="$emit('close')"
          >
            Cancel
          </Button>
          <Button type="submit" :disabled="isLoading">
            <span v-if="!isLoading">Save Changes</span>
            <span v-else>
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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