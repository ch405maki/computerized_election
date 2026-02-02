<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';
import VoterRegistrationForm from './VoterRegistrationForm.vue';

const toast = useToast();
const isDialogOpen = ref(false);

const handleSubmit = async (formData: any) => {
    try {
        const response = await axios.post('/api/voters', formData);
        
        toast.success('Voter registered successfully! Waiting for activation.');
        isDialogOpen.value = false;
        // Reload the page after a short delay
        setTimeout(() => {
            location.reload();
        }, 2000);

    } catch (error) {
        if (axios.isAxiosError(error) && error.response) {
            if (error.response.status === 422) {
                const errors = error.response.data.errors;
                Object.values(errors).flat().forEach(message => {
                    toast.error(message);
                });
            } else {
                toast.error(error.response.data.message || 'An error occurred');
            }
        } else {
            toast.error('An unexpected error occurred');
        }
    }
};
</script>

<template>
    <Dialog v-model:open="isDialogOpen">
        <DialogTrigger as-child>
            <Button
                size="sm"
                variant="default">
                <slot name="trigger">
                    New Voter
                </slot>
            </Button>
        </DialogTrigger>
        
        <DialogContent class="sm:max-w-[625px]">
            <DialogHeader>
                <DialogTitle>
                    <slot name="title">Register New Voter</slot>
                </DialogTitle>
                <DialogDescription>
                    <slot name="description">
                        Fill out the form to register a new voter.
                    </slot>
                </DialogDescription>
            </DialogHeader>
            
            <VoterRegistrationForm @submit="handleSubmit">
                <template #actions="{ isLoading }">
                    <Button 
                        type="button" 
                        variant="outline" 
                        @click="isDialogOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="isLoading">
                        <span v-if="!isLoading">Register Voter</span>
                        <span v-else>Processing...</span>
                    </Button>
                </template>
            </VoterRegistrationForm>
        </DialogContent>
    </Dialog>
</template>