<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import axios from 'axios';
import { computed, reactive, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps<{
    positions: Array<{ id: number; name: string }>;
    elections: Array<{ id: number; name: string; status: string }>;
    userPermissions?: Record<string, boolean>;
}>();

const emit = defineEmits(['candidateCreated']);

const toast = useToast();
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isLoading = ref(false);
const isOpen = ref(false);

// Filter out active elections
const availableElections = computed(() => props.elections.filter((e) => e.status !== 'active'));

// Check if user has addCandidate permission
const canAddCandidate = computed(() => !!props.userPermissions?.addCandidate);

const initialFormState = {
    election_id: '',
    position_id: '',
    candidate_code: 'AUTO-GENERATED',
    candidate_name: '',
    candidate_party: '',
    candidate_picture: null as File | null,
};

const initialErrors = {
    election_id: '',
    position_id: '',
    candidate_name: '',
};

const form = reactive({ ...initialFormState });
const errors = ref({ ...initialErrors });

const validateForm = () => {
    errors.value = {
        election_id: form.election_id ? '' : 'Election is required',
        position_id: form.position_id ? '' : 'Position is required',
        candidate_name: form.candidate_name.length >= 2 ? '' : 'Name must be at least 2 characters',
    };

    return Object.values(errors.value).every((error) => !error);
};

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (file) {
        form.candidate_picture = file;
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
    Object.assign(form, initialFormState);
    errors.value = { ...initialErrors };
    imagePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

watch(isOpen, (newVal) => {
    if (!newVal) resetForm();
});

const onSubmit = () => {
    if (!validateForm()) {
        toast.error('Please fix the form errors.');
        return;
    }

    isLoading.value = true;
    const formData = new FormData();

    Object.entries(form).forEach(([key, value]) => {
        if (value && key !== 'candidate_code') {
            formData.append(key, value as string | Blob);
        }
    });

    axios
        .post('/api/candidates', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
        })
        .then((response) => {
            toast.success(response.data.message);
            resetForm();
            emit('candidateCreated');
            isOpen.value = false;
        })
        .catch((error) => {
            toast.error(error.response?.data?.message || 'Failed to create candidate');
        })
        .finally(() => {
            isLoading.value = false;
        });
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child v-if="canAddCandidate">
            <Button variant="default">Add New Candidate</Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-[625px]">
            <DialogHeader>
                <DialogTitle>Add New Candidate</DialogTitle>
                <DialogDescription> Fill out the form to register a new candidate for the election. </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="onSubmit">
                <div class="grid grid-cols-1 gap-2 py-4">
                    <FormField v-slot="{ componentField }" name="election_id">
                        <FormItem>
                            <FormLabel>Election</FormLabel>
                            <Select v-model="form.election_id" v-bind="componentField">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select election" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="election in availableElections" :key="election.id" :value="String(election.id)">
                                        {{ election.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <FormMessage>{{ errors.election_id }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <FormField v-slot="{ componentField }" name="position_id">
                            <FormItem>
                                <FormLabel>Position</FormLabel>
                                <Select v-model="form.position_id" v-bind="componentField">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select position" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="position in positions" :key="position.id" :value="String(position.id)">
                                            {{ position.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FormMessage>{{ errors.position_id }}</FormMessage>
                            </FormItem>
                        </FormField>
                    </div>

                    <FormField v-slot="{ componentField }" name="candidate_name">
                        <FormItem>
                            <FormLabel>Full Name</FormLabel>
                            <FormControl>
                                <Input type="text" v-model="form.candidate_name" placeholder="Candidate's full name" v-bind="componentField" />
                            </FormControl>
                            <FormMessage>{{ errors.candidate_name }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="candidate_party">
                        <FormItem>
                            <FormLabel>Political Party (Optional)</FormLabel>
                            <FormControl>
                                <Input type="text" v-model="form.candidate_party" placeholder="Party affiliation" v-bind="componentField" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="candidate_picture">
                        <FormItem>
                            <FormLabel>Profile Picture</FormLabel>
                            <div class="flex items-center gap-4">
                                <div
                                    @click="triggerFileInput"
                                    class="flex h-20 w-20 cursor-pointer items-center justify-center overflow-hidden rounded-full border-2 border-dashed border-gray-300"
                                >
                                    <img v-if="imagePreview" :src="imagePreview" class="h-full w-full object-cover" />
                                    <span v-else class="text-center text-xs text-gray-400">Upload Image</span>
                                </div>
                                <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileChange" />
                            </div>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="isOpen = false">Cancel</Button>
                    <Button type="submit" :disabled="isLoading">
                        {{ isLoading ? 'Creating...' : 'Create Candidate' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
