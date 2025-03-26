<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref } from 'vue';

const toast = useToast();
const isLoading = ref(false);
const isActivatingAll = ref(false);

const props = defineProps<{
    inactiveVoters: Array<{
        id: number;
        student_number: string;
        full_name: string;
        student_year: string;
        class_type: string;
        created_at: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Voters',
        href: '/voters',
    },
    {
        title: 'Status',
        href: '/voters/status',
    },
];

const activateVoter = async (voterId: number) => {
    isLoading.value = true;
    try {
        const response = await axios.post(`/api/voters/status/activate/${voterId}`, {}, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        toast.success(response.data.message);
        // Refresh the page to get updated data
        router.reload({ only: ['inactiveVoters'] });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response) {
            toast.error(error.response.data.message || 'Failed to activate voter');
        } else {
            toast.error('An unexpected error occurred');
        }
    } finally {
        isLoading.value = false;
    }
};

const activateAll = async () => {
    isActivatingAll.value = true;
    try {
        const response = await axios.post('/api/voters/status/activate-all', {}, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        toast.success(response.data.message);
        // Refresh the page to get updated data
        router.reload({ only: ['inactiveVoters'] });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response) {
            toast.error(error.response.data.message || 'Failed to activate voters');
        } else {
            toast.error('An unexpected error occurred');
        }
    } finally {
        isActivatingAll.value = false;
    }
};
</script>

<template>
    <Head title="Voter Status" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-8">
            <div class="bg-card rounded-lg shadow-sm border p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Pending Voter Activations</h1>
                    <Button 
                        v-if="inactiveVoters.length > 0" 
                        @click="activateAll"
                        :disabled="isActivatingAll"
                        class="bg-green-600 hover:bg-green-700"
                    >
                        <span v-if="!isActivatingAll">Activate All</span>
                        <span v-else>Activating...</span>
                    </Button>
                </div>

                <div class="border rounded-lg overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Student Number</TableHead>
                                <TableHead>Full Name</TableHead>
                                <TableHead>Year</TableHead>
                                <TableHead>Class</TableHead>
                                <TableHead>Registered At</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="inactiveVoters.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No pending voter activations
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="voter in inactiveVoters" :key="voter.id">
                                <TableCell>{{ voter.student_number }}</TableCell>
                                <TableCell>{{ voter.full_name }}</TableCell>
                                <TableCell>{{ voter.student_year }}</TableCell>
                                <TableCell>{{ voter.class_type }}</TableCell>
                                <TableCell>{{ voter.created_at }}</TableCell>
                                <TableCell>
                                    <Button 
                                        @click="activateVoter(voter.id)"
                                        :disabled="isLoading"
                                        size="sm"
                                        class="bg-green-600 hover:bg-green-700"
                                    >
                                        <span v-if="!isLoading">Activate</span>
                                        <span v-else>Activating...</span>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>