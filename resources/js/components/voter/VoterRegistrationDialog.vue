<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "vue-toastification";
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import VoterRegistrationForm from './VoterRegistrationForm.vue';

const toast = useToast();
const isDialogOpen = ref(false);

const handleSubmit = async (formData: any) => {
    try {
        await axios.post('/api/voters', formData);
        
        toast.success('Voter registered successfully! Waiting for activation.');
        isDialogOpen.value = false;
        
        setTimeout(() => location.reload(), 2000);

    } catch (error) {
        const response = axios.isAxiosError(error) ? error.response : null;

        if (response?.status === 422) {
            Object.values(response.data.errors)
                .flat()
                .forEach(message => toast.error(String(message)));
            return;
        }

        toast.error(response?.data?.message || 'An unexpected error occurred');
    }
};
</script>

<template>
    <Dialog v-model:open="isDialogOpen">
        <DialogTrigger as-child>
            <Button size="sm" variant="default">
                <slot name="trigger">Add a Voter</slot>
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
                        {{ isLoading ? 'Processing...' : 'Register Voter' }}
                    </Button>
                </template>
            </VoterRegistrationForm>
        </DialogContent>
    </Dialog>
</template>