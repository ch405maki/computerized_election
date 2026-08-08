<!-- components/CreateUserDialog.vue -->
<template>
    <Dialog v-model:open="isOpen" class="dark:bg-gray-800">
        <DialogTrigger as-child>
            <Button @click="openDialog"> <UserRoundPlus class="mr-2 h-4 w-4" /> Create </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle class="text-lg font-bold dark:text-gray-200">Create New User</DialogTitle>
                <DialogDescription class="text-sm dark:text-gray-400">Fill in the details to add a new user.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="createUser">
                <div class="grid gap-4">
                    <input v-model="formData.name" type="text" placeholder="Name" class="form-input" required />
                    <input v-model="formData.email" type="email" placeholder="Email" class="form-input" required />
                    <input v-model="formData.password" type="password" placeholder="Password" class="form-input" required />
                    <select v-model="formData.role" class="form-input" required>
                        <option value="superadmin">Superadmin</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                    <select v-model="formData.status" class="form-input" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <DialogFooter class="mt-4">
                    <Button
                        variant="outline"
                        @click="closeDialog"
                        class="rounded border border-gray-300 bg-gray-100 px-4 py-2 font-bold text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200"
                        >Cancel</Button
                    >
                    <Button
                        type="submit"
                        :disabled="loading"                    >
                        <span v-if="loading">Creating...</span>
                        <span v-else>Create</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import axios from 'axios';
import { UserRoundPlus } from 'lucide-vue-next';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
const isOpen = ref(false);
const loading = ref(false);
const formData = ref({ name: '', email: '', password: '', role: 'user', status: 'active' });

const openDialog = () => (isOpen.value = true);
const closeDialog = () => (isOpen.value = false);

const createUser = async () => {
    loading.value = true;
    try {
        await axios.post('/api/users', formData.value);
        toast.success('User created successfully!');
        setTimeout(() => location.reload(), 2000);
    } catch (error) {
        toast.error('Failed to create user');
    } finally {
        loading.value = false;
        closeDialog();
    }
};
</script>

<style scoped>
.form-input {
    @apply w-full rounded border border-gray-300 p-2 dark:border-gray-600 dark:bg-gray-700;
}
</style>
