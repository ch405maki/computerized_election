<script setup lang="ts">
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { useToast } from "vue-toastification";
import { Loader2 } from "lucide-vue-next";
import axios from 'axios';
import { ref } from 'vue';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';


interface Voter {
  id: number;
  student_number: string;
  full_name: string;
  student_year: string;
  class_type: string;
  sex: string;
  password: string;
}

const props = defineProps<{ voter: Voter }>();
const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'updated', voter: Voter): void;
}>();

const toast = useToast();
const isLoading = ref(false);
const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

// Schema matching your Voter fields
const formSchema = toTypedSchema(z.object({
  student_number: z.string().min(1),
  full_name: z.string().min(2, "Full name is too short"),
  student_year: z.string().min(1, "Year is required"),
  class_type: z.string().min(1, "Class type is required"),
  sex: z.enum(['Male', 'Female', 'Other']),
  password: z.string().min(8, "Password must be at least 8 characters").optional().or(z.literal(''))
}));

const formData = ref<Partial<Voter>>({ ...props.voter,
    password: ''
});

// The main update function
const updateVoter = async () => {
  isLoading.value = true;
  
  try {
    const response = await axios.patch(`/api/voters/${props.voter.id}`, formData.value, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    });
    
    toast.success(response.data.message || 'Voter updated successfully');
    emit('updated', response.data.data);

    // Refresh the page to show updated data across the UI
    setTimeout(() => {
      window.location.reload();
    }, 1000);

    emit('close');
    
  } catch (error: unknown) {
    handleUpdateError(error);
  } finally {
    isLoading.value = false;
  }
};

// Comprehensive error handling router
const handleUpdateError = (error: unknown) => {
  if (axios.isAxiosError<{ message?: string; errors?: Record<string, string[]> }>(error)) {
    // Handle Laravel/Backend Validation Errors (422 Unprocessable Entity)
    if (error.response?.status === 422 && error.response.data.errors) {
      handleValidationErrors(error.response.data.errors);
    } else {
      // Handle other Axios errors (404, 500, etc.)
      toast.error(error.response?.data?.message || 'Failed to update voter');
    }
  } else if (error instanceof Error) {
    // Handle generic JS errors
    toast.error(error.message || 'An unexpected error occurred');
    console.error('Update error:', error);
  } else {
    // Fallback for unknown edge cases
    toast.error('An unexpected error occurred');
    console.error('Unknown error:', error);
  }
};

// Specific handler for 422 validation errors
const handleValidationErrors = (errors: Record<string, string[]>) => {
  Object.entries(errors).forEach(([field, messages]) => {
    messages.forEach(message => {
      // Format field name (e.g., "student_number" to "Student Number") for better UX
      const friendlyField = field.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
      toast.error(`${friendlyField}: ${message}`);
    });
  });
};


</script>

<template>
  <Sheet defaultOpen @update:open="(val) => !val && $emit('close')">
    <SheetContent side="right" class="w-full sm:max-w-md">
      <Form 
        :validation-schema="formSchema" 
        :initial-values="formData"
        @submit="updateVoter" 
        v-slot="{ meta }"
        class="space-y-4 mt-4"
      >
        <SheetHeader>
          <SheetTitle>Edit Voter</SheetTitle>
          <SheetDescription>Update the information for {{ voter.full_name }}</SheetDescription>
        </SheetHeader>

        <FormField v-slot="{ componentField, meta }" name="full_name">
          <FormItem>
            <FormLabel>Full Name</FormLabel>
            <FormControl>
              <Input v-bind="componentField" v-model="formData.full_name" />
            </FormControl>
            <FormMessage v-if="meta.touched" />
          </FormItem>
        </FormField>

        <div class="grid grid-cols-2 gap-4">
          <FormField v-slot="{ componentField, meta }" name="student_number">
            <FormItem>
              <FormLabel>Student ID</FormLabel>
              <FormControl>
                <Input v-bind="componentField" v-model="formData.student_number" />
              </FormControl>
              <FormMessage v-if="meta.touched" />
            </FormItem>
          </FormField>

          <FormField v-slot="{ componentField, meta }" name="sex">
            <FormItem>
              <FormLabel>Sex</FormLabel>
              <Select v-bind="componentField" v-model="formData.sex">
                <FormControl>
                  <SelectTrigger><SelectValue placeholder="Select sex" /></SelectTrigger>
                </FormControl>
                <SelectContent>
                  <SelectItem value="Male">Male</SelectItem>
                  <SelectItem value="Female">Female</SelectItem>
                  <SelectItem value="Other">Other</SelectItem>
                </SelectContent>
              </Select>
              <FormMessage v-if="meta.touched" />
            </FormItem>
          </FormField>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <FormField v-slot="{ componentField, meta }" name="student_year">
            <FormItem>
              <FormLabel>Student Year</FormLabel>
              <Select v-bind="componentField" v-model="formData.student_year">
                <FormControl>
                  <SelectTrigger><SelectValue placeholder="Select Student Year" /></SelectTrigger>
                </FormControl>
                <SelectContent>
                  <SelectItem value="1">1</SelectItem>
                  <SelectItem value="2">2</SelectItem>
                  <SelectItem value="3">3</SelectItem>
                </SelectContent>
              </Select>
              <FormMessage v-if="meta.touched" />
            </FormItem>
          </FormField>

          <FormField v-slot="{ componentField, meta }" name="class_type">
            <FormItem>
              <FormLabel>Class Type</FormLabel>
              <Select v-bind="componentField" v-model="formData.class_type">
                <FormControl>
                  <SelectTrigger><SelectValue placeholder="Select class type" /></SelectTrigger>
                </FormControl>
                <SelectContent>
                  <SelectItem value="A">A</SelectItem>
                  <SelectItem value="B">B</SelectItem>
                  <SelectItem value="C">C</SelectItem>
                </SelectContent>
              </Select>
              <FormMessage v-if="meta.touched" />
            </FormItem>
          </FormField>
        </div>
        <div>
            <FormField v-slot="{ componentField, meta }" name="password">
  <FormItem>
    <FormLabel>New Password</FormLabel>
    <FormControl>
      <Input 
        type="password" 
        v-bind="componentField" 
        v-model="formData.password" 
        placeholder="Leave blank to keep current password"
      />
    </FormControl>
    <FormMessage v-if="meta.touched" />
  </FormItem>
</FormField>
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <Button 
          type="button" 
          variant="outline" 
          @click="$emit('close')"
          :disabled="isLoading"
        >
          Cancel
        </Button>
        
        <Button type="submit" :disabled="isLoading || !meta.dirty">
          <template v-if="isLoading">
            <Loader2 class="mr-2 h-4 w-4 animate-spin" />
            Updating...
          </template>
          <template v-else>
            Save Changes
          </template>
        </Button>
        </div>
      </Form>
    </SheetContent>
  </Sheet>
</template>