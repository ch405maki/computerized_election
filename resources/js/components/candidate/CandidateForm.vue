<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'

const props = defineProps<{
  positions: Array<{ id: number; name: string }>;
  elections: Array<{ id: number; name: string }>;
}>();

const emit = defineEmits(['candidateCreated']);

const toast = useToast();
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isLoading = ref(false);
const isOpen = ref(false);

// Form fields
const election_id = ref<string>('');
const position_id = ref<string>('');
const candidate_code = ref<string>('AUTO-GENERATED');
const candidate_name = ref<string>('');
const candidate_party = ref<string>('');
const candidate_picture = ref<File | null>(null);

// Validation errors
const errors = ref({
  election_id: '',
  position_id: '',
  candidate_code: '',
  candidate_name: '',
});

const validateForm = () => {
  errors.value = {
    election_id: election_id.value ? '' : 'Election is required',
    position_id: position_id.value ? '' : 'Position is required',
    candidate_code: '', // Auto-generated
    candidate_name: candidate_name.value.length >= 2 ? '' : 'Name must be at least 2 characters',
  };

  return Object.values(errors.value).every(error => !error);
};

const handleFileChange = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    const file = input.files[0];
    candidate_picture.value = file;

    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const resetForm = () => {
  election_id.value = '';
  position_id.value = '';
  candidate_code.value = '';
  candidate_name.value = '';
  candidate_party.value = '';
  candidate_picture.value = null;
  imagePreview.value = null;
  if (fileInput.value) fileInput.value.value = '';
  errors.value = {
    election_id: '',
    position_id: '',
    candidate_code: '',
    candidate_name: '',
  };
};

const onSubmit = () => {
  if (!validateForm()) {
    toast.error("Please fix the form errors.");
    return;
  }

  isLoading.value = true;
  const formData = new FormData();
  formData.append('election_id', election_id.value);
  formData.append('position_id', position_id.value);
  formData.append('candidate_name', candidate_name.value);
  if (candidate_party.value) formData.append('candidate_party', candidate_party.value);
  if (candidate_picture.value) formData.append('candidate_picture', candidate_picture.value);

  axios.post('/api/candidates', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
  })
  .then(response => {
    toast.success(response.data.message);
    resetForm();
    emit('candidateCreated');
    isOpen.value = false;
  })
  .catch(error => {
    toast.error(error.response?.data?.message || 'Failed to create candidate');
  })
  .finally(() => {
    isLoading.value = false;
  });
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button variant="default">
        Add New Candidate
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[625px]">
      <DialogHeader>
        <DialogTitle>Add New Candidate</DialogTitle>
        <DialogDescription>
          Fill out the form to register a new candidate for the election.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="onSubmit">
        <div class="grid grid-cols-1 gap-2 py-4">
          
          <!-- Election Field -->
          <FormField v-slot="{ componentField }" name="election_id">
            <FormItem>
              <FormLabel>Election</FormLabel>
              <Select v-model="election_id" v-bind="componentField">
                <SelectTrigger>
                  <SelectValue placeholder="Select election" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="election in elections" :key="election.id" :value="String(election.id)">
                    {{ election.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <FormMessage />
            </FormItem>
          </FormField>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Position Field -->
            <FormField v-slot="{ componentField }" name="position_id">
              <FormItem>
                <FormLabel>Position</FormLabel>
                <Select v-model="position_id" v-bind="componentField">
                  <SelectTrigger>
                    <SelectValue placeholder="Select position" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="position in positions" :key="position.id" :value="String(position.id)">
                      {{ position.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            </FormField>
          </div>

          <!-- Candidate Name -->
          <FormField v-slot="{ componentField }" name="candidate_name">
            <FormItem>
              <FormLabel>Full Name</FormLabel>
              <FormControl>
                <Input type="text" v-model="candidate_name" placeholder="Candidate's full name" v-bind="componentField" />
              </FormControl>
              <FormMessage />
            </FormItem>
          </FormField>

          <!-- Candidate Party -->
          <FormField v-slot="{ componentField }" name="candidate_party">
            <FormItem>
              <FormLabel>Political Party (Optional)</FormLabel>
              <FormControl>
                <Input type="text" v-model="candidate_party" placeholder="Party affiliation" v-bind="componentField" />
              </FormControl>
              <FormMessage />
            </FormItem>
          </FormField>

          <!-- Candidate Picture -->
          <FormField v-slot="{ componentField }" name="candidate_picture">
            <FormItem>
              <FormLabel>Profile Picture</FormLabel>
              <div class="flex items-center gap-4">
                <div 
                  @click="triggerFileInput"
                  class="w-20 h-20 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer overflow-hidden"
                >
                  <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                  <span v-else class="text-gray-400 text-xs text-center">Upload Image</span>
                </div>
                <input 
                  ref="fileInput"
                  type="file" 
                  accept="image/*"
                  class="hidden"
                  @change="handleFileChange"
                />
              </div>
              <FormMessage />
            </FormItem>
          </FormField>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="isOpen = false">Cancel</Button>
          <Button type="submit" :disabled="isLoading">
            <span v-if="!isLoading">Create Candidate</span>
            <span v-else>Creating...</span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>