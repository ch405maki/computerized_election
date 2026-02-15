<script setup lang="ts">
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { useToast } from "vue-toastification";
import { Loader2 } from "lucide-vue-next";
import axios from 'axios';
import { ref, watch } from 'vue';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';

interface Voter {
  id: number;
  student_number: string;
  first_name: string;
  last_name: string;
  middle_name?: string | null;
  student_year: string;
  sex: string;
}

const props = defineProps<{ voter: Voter }>();
const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'updated', voter: Voter): void;
}>();

const toast = useToast();
const isLoading = ref(false);
const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

// Schema updated for split names
const formSchema = toTypedSchema(z.object({
  student_number: z.string().min(1, "Student ID is required"),
  first_name: z.string().min(2, "First name is too short"),
  last_name: z.string().min(2, "Last name is too short"),
  middle_name: z.string().optional().nullable(),
  student_year: z.string().min(1, "Year is required"),
  sex: z.string().min(1, "Sex is required"), 
  password: z.string().min(6, "Password must be at least 6 characters").optional().or(z.literal(''))
}));

// Initialize form data
const formData = ref({
  ...props.voter,
  middle_name: props.voter.middle_name || '', // Ensure null becomes empty string for input
  password: ''
});

// Watch for prop changes
watch(() => props.voter, (newVal) => {
  formData.value = {
    ...newVal,
    middle_name: newVal.middle_name || '',
    password: ''
  };
});


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

    // Refresh page
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

const handleUpdateError = (error: unknown) => {
  if (axios.isAxiosError<{ message?: string; errors?: Record<string, string[]> }>(error)) {
    if (error.response?.status === 422 && error.response.data.errors) {
      handleValidationErrors(error.response.data.errors);
    } else {
      toast.error(error.response?.data?.message || 'Failed to update voter');
    }
  } else if (error instanceof Error) {
    toast.error(error.message || 'An unexpected error occurred');
    console.error('Update error:', error);
  } else {
    toast.error('An unexpected error occurred');
  }
};

const handleValidationErrors = (errors: Record<string, string[]>) => {
  Object.entries(errors).forEach(([field, messages]) => {
    messages.forEach(message => {
      const friendlyField = field.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
      toast.error(`${friendlyField}: ${message}`);
    });
  });
};
</script>

<template>
  <Sheet defaultOpen @update:open="(val) => !val && $emit('close')">
    <SheetContent side="right" class="w-full sm:max-w-md overflow-y-auto">
      <Form 
        :validation-schema="formSchema" 
        :initial-values="formData"
        @submit="updateVoter" 
        v-slot="{ meta }"
        class="space-y-4 mt-4"
      >
        <SheetHeader>
          <SheetTitle>Edit Voter</SheetTitle>
          <SheetDescription>Update the information for {{ voter.student_number }}</SheetDescription>
        </SheetHeader>

        <FormField v-slot="{ componentField, meta }" name="last_name">
          <FormItem>
            <FormLabel>Last Name</FormLabel>
            <FormControl>
              <Input v-bind="componentField" v-model="formData.last_name" placeholder="Surname" />
            </FormControl>
            <FormMessage v-if="meta.touched" />
          </FormItem>
        </FormField>

        <div class="grid grid-cols-2 gap-4">
            <FormField v-slot="{ componentField, meta }" name="first_name">
            <FormItem>
                <FormLabel>First Name</FormLabel>
                <FormControl>
                <Input v-bind="componentField" v-model="formData.first_name" placeholder="First Name" />
                </FormControl>
                <FormMessage v-if="meta.touched" />
            </FormItem>
            </FormField>

            <FormField v-slot="{ componentField, meta }" name="middle_name">
            <FormItem>
                <FormLabel>Middle Name</FormLabel>
                <FormControl>
                <Input v-bind="componentField" v-model="formData.middle_name" placeholder="Optional" />
                </FormControl>
                <FormMessage v-if="meta.touched" />
            </FormItem>
            </FormField>
        </div>

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
                  <SelectItem value="male">Male</SelectItem>
                  <SelectItem value="female">Female</SelectItem>
                  <SelectItem value="other">Other</SelectItem>
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
                  <SelectTrigger><SelectValue placeholder="Select Year" /></SelectTrigger>
                </FormControl>
                <SelectContent>
                  <SelectItem value="1">1</SelectItem>
                  <SelectItem value="2">2</SelectItem>
                  <SelectItem value="3">3</SelectItem>
                  <SelectItem value="4">4</SelectItem>
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
                    placeholder="Leave blank to keep current"
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