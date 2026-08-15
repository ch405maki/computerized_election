<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage, router } from '@inertiajs/vue3'; 
import { FileDown, Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue'; 
import { exportElectionResultsPdf } from '@/lib/exportElectionResultsPdf';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useToast } from 'vue-toastification'; 

interface CandidateResult {
    id: number;
    candidate_name: string;
    candidate_party: string | null;
    candidate_picture: string | null;
    votes: number;
}

const props = defineProps<{
    election: {
        id: number;
        name: string;
        start_date: string;
        end_date: string;
    } | null;
    positions?: Record<string, CandidateResult[]>;
    signatureUrl?: string | null;
}>();

const toast = useToast(); 
const page = usePage();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Results History', href: '/reports/results' },
    { title: props.election?.name || 'Election Results', href: route('results.show', props.election?.id) },
]);

const formattedDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const electionDateRange = computed(() => {
    if (!props.election) return '';
    return `${formattedDate(props.election.start_date)} - ${formattedDate(props.election.end_date)}`;
});

const processedPositions = computed(() => {
    if (!props.positions) return [];
    
    return Object.entries(props.positions).map(([name, candidates]) => {
        const sorted = [...candidates].sort((a, b) => b.votes - a.votes);
        const totalVotes = sorted.reduce((total, c) => total + c.votes, 0);
        
        return { name, candidates: sorted, totalVotes };
    });
});

const currentUserName = computed(() => {
    return (page.props.auth as any)?.user?.name || 'ADMINISTRATOR';
});

// --- METHODS ---
const downloadPDF = async () => {
    if (!props.election || !props.positions) {
        toast.warning('Election data is not fully loaded yet.');
        return;
    }

    await exportElectionResultsPdf(
        props.election, 
        props.positions, 
        props.signatureUrl, 
        currentUserName.value
    );
};

// --- E-SIGNATURE MODAL LOGIC ---
const isUploadDialogOpen = ref(false);
const isReplacing = ref(false); 
const isDeleteDialogOpen = ref(false); 
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    signature: null as File | null,
});

const openDialog = () => {
    isReplacing.value = false; 
    isUploadDialogOpen.value = true;
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.signature = target.files[0];
    }
};

const submitSignature = () => {
    form.post(route('signature.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            isUploadDialogOpen.value = false;
            form.reset();
            if (fileInput.value) fileInput.value.value = '';
            toast.success('E-Signature saved successfully!'); 
        },
        onError: () => {
            toast.error('Failed to upload signature. Please check the file.');
        }
    });
};

const executeRemoveSignature = () => {
    router.delete(route('signature.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteDialogOpen.value = false; 
            isUploadDialogOpen.value = false; 
            toast.success('E-Signature removed successfully!');
        },
        onError: () => toast.error('Failed to remove signature.')
    });
};
</script>

<template>
    <Head :title="`Results - ${election?.name || 'Election'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4">
            <!-- Header -->
            <div class="flex items-end justify-between">
                <div>
                    <h1 class="mb-2 text-2xl font-bold">Election Results</h1>
                    <h2 class="text-xl">{{ election?.name || 'Loading...' }}</h2>
                    <p v-if="election" class="text-muted-foreground">
                        {{ electionDateRange }}
                    </p>
                </div>

                <div class="flex flex-row items-center gap-2">
                    <Button @click="openDialog" variant="outline">
                        <Upload class="w-4 h-4 mr-2"/>
                        <span>{{ signatureUrl ? 'Manage E-Signature' : 'Upload E-Signature' }}</span>
                    </Button>
                    <Button @click="downloadPDF" variant="default">
                        <FileDown class="mr-2 h-4 w-4" />
                        <span>Download PDF</span>
                    </Button>
                </div>
            </div>

            <!-- Content -->
            <div v-if="!positions" class="py-8 text-center">
                <p class="text-muted-foreground">Loading election results...</p>
            </div>

            <div v-else class="space-y-8">
                <template v-if="processedPositions.length > 0">
                    <div v-for="position in processedPositions" :key="position.name" class="rounded-lg border shadow-sm">
                        <h3 class="rounded-t bg-[#6b21a8] px-4 py-2 text-lg font-semibold text-white">
                            {{ position.name }}
                        </h3>

                        <Table>
                            <TableHeader>
                                <TableRow class="hover:bg-transparent">
                                    <TableHead class="w-[80px] text-center">Rank</TableHead>
                                    <TableHead>Candidate Name</TableHead>
                                    <TableHead>Party</TableHead>
                                    <TableHead class="text-right">Votes</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(candidate, index) in position.candidates" :key="candidate.id">
                                    <TableCell class="text-center">{{ index + 1 }}</TableCell>
                                    <TableCell>{{ candidate.candidate_name }}</TableCell>
                                    <TableCell>{{ candidate.candidate_party || 'Independent' }}</TableCell>
                                    <TableCell class="text-right">{{ candidate.votes }}</TableCell>
                                </TableRow>

                                <TableRow class="bg-muted/50 hover:bg-transparent">
                                    <TableCell></TableCell>
                                    <TableCell></TableCell>
                                    <TableCell class="pt-4 font-bold text-black">TOTAL VOTES</TableCell>
                                    <TableCell class="pt-4 text-right font-bold text-black">
                                        {{ position.totalVotes }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </template>
                <div v-else class="py-8 text-center text-muted-foreground">No results available for this election.</div>
            </div>
        </div>
    </AppLayout>

    <!-- Manage/Upload Signature Dialog -->
    <Dialog :open="isUploadDialogOpen" @update:open="isUploadDialogOpen = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>E-Signature</DialogTitle>
                <DialogDescription v-if="signatureUrl && !isReplacing">
                    Your current e-signature is displayed below.
                </DialogDescription>
                <DialogDescription v-else>
                    Please upload a clear PNG image of your e-signature. This will be attached to your account.
                </DialogDescription>
            </DialogHeader>

            <div v-if="signatureUrl && !isReplacing" class="flex flex-col gap-4 py-4">
                <div class="flex items-center justify-center rounded-lg border border-dashed p-6 bg-white shadow-sm">
                    <img :src="signatureUrl" alt="Your E-Signature" class="max-h-32 object-contain" />
                </div>
                
                <DialogFooter class="mt-4 sm:justify-between w-full">
                    <Button type="button" variant="outline" @click="isUploadDialogOpen = false">
                        Close
                    </Button>
                    <div class="flex gap-2">
                        <Button type="button" variant="destructive" @click="isDeleteDialogOpen = true">
                            Remove
                        </Button>
                        <Button type="button" @click="isReplacing = true">
                            Replace
                        </Button>
                    </div>
                </DialogFooter>
            </div>

            <form v-else @submit.prevent="submitSignature">
                <div class="grid gap-4 py-4">
                    <div class="flex flex-col gap-2">
                        <label for="signature" class="text-sm font-medium">Signature File (PNG only)</label>
                        <input 
                            id="signature" 
                            type="file" 
                            accept="image/png" 
                            @change="handleFileUpload" 
                            ref="fileInput"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <span v-if="form.errors.signature" class="text-sm text-red-500">
                            {{ form.errors.signature }}
                        </span>
                    </div>
                </div>

                <DialogFooter class="sm:justify-between w-full">
                    <Button v-if="signatureUrl" type="button" variant="ghost" @click="isReplacing = false">
                        Back
                    </Button>
                    <Button v-else type="button" variant="outline" @click="isUploadDialogOpen = false">
                        Cancel
                    </Button>
                    
                    <Button type="submit" :disabled="form.processing || !form.signature">
                        {{ form.processing ? 'Uploading...' : 'Save Signature' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Confirm Delete Dialog -->
    <Dialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Remove E-Signature</DialogTitle>
                <DialogDescription>
                    Are you sure you want to remove your e-signature? This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="mt-4 gap-2 sm:justify-end">
                <Button type="button" variant="outline" @click="isDeleteDialogOpen = false">
                    Cancel
                </Button>
                <Button type="button" variant="destructive" @click="executeRemoveSignature">
                    Yes, Remove
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>