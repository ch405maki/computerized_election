<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Button } from '@/components/ui/button';

const props = defineProps<{
  positions: Array<{ id: number; name: string }>;
  elections: Array<{ id: number; name: string }>;
}>();

const toast = useToast();
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isLoading = ref(false);

// Form fields
const election_id = ref<string>('');
const position_id = ref<string>('');
const candidate_code = ref<string>('');
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
    candidate_code: candidate_code.value.length >= 2 ? '' : 'Code must be at least 2 characters',
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

const onSubmit = () => {
  if (!validateForm()) {
    toast.error("Please fix the form errors.");
    return;
  }

  isLoading.value = true;
  const formData = new FormData();
  formData.append('election_id', election_id.value);
  formData.append('position_id', position_id.value);
  formData.append('candidate_code', candidate_code.value);
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
    // Reset form
    election_id.value = '';
    position_id.value = '';
    candidate_code.value = '';
    candidate_name.value = '';
    candidate_party.value = '';
    candidate_picture.value = null;
    imagePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';

    setTimeout(() => {
      window.location.reload();
    }, 2000);

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
  <div class="bg-card rounded-lg shadow-sm border p-6">
    <h2 class="text-2xl font-bold mb-6">Add New Candidate</h2>
    <form @submit.prevent="onSubmit">
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        
        <!-- Election Field -->
        <div>
          <label class="font-medium">Election</label>
          <Select v-model="election_id">
            <SelectTrigger>
              <SelectValue placeholder="Select election" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="election in elections" :key="election.id" :value="String(election.id)">
                {{ election.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="errors.election_id" class="text-red-500 text-sm">{{ errors.election_id }}</p>
        </div>

        <!-- Position Field -->
        <div>
          <label class="font-medium">Position</label>
          <Select v-model="position_id">
            <SelectTrigger>
              <SelectValue placeholder="Select position" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="position in positions" :key="position.id" :value="String(position.id)">
                {{ position.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="errors.position_id" class="text-red-500 text-sm">{{ errors.position_id }}</p>
        </div>

        <!-- Candidate Code -->
        <div>
          <label class="font-medium">Candidate Code</label>
          <Input type="text" v-model="candidate_code" placeholder="e.g. CAN-001" />
          <p v-if="errors.candidate_code" class="text-red-500 text-sm">{{ errors.candidate_code }}</p>
        </div>

        <!-- Candidate Name -->
        <div>
          <label class="font-medium">Full Name</label>
          <Input type="text" v-model="candidate_name" placeholder="Candidate's full name" />
          <p v-if="errors.candidate_name" class="text-red-500 text-sm">{{ errors.candidate_name }}</p>
        </div>

        <!-- Candidate Party -->
        <div>
          <label class="font-medium">Political Party</label>
          <Input type="text" v-model="candidate_party" placeholder="Party affiliation (optional)" />
        </div>

        <!-- Candidate Picture -->
        <div>
          <label class="font-medium">Profile Picture</label>
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
        </div>

      </div>

      <div class="flex justify-end mt-6">
        <Button type="submit" :disabled="isLoading">
          <span v-if="!isLoading">Create Candidate</span>
          <span v-else>Creating...</span>
        </Button>
      </div>
    </form>
  </div>
</template> 