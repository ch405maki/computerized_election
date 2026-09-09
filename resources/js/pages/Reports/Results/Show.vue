<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage, router } from '@inertiajs/vue3'; 
import { FileDown, Upload, Eye, Loader2 } from 'lucide-vue-next';
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

// --- COMPUTED STATE ---
const electionName = computed(() => props.election?.name || 'Election Results');
const isDataReady = computed(() => !!(props.election && props.positions));
const currentUserName = computed(() => (page.props.auth as any)?.user?.name || 'ADMINISTRATOR');

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Results History', href: '/reports/results' },
    { title: electionName.value, href: route('results.show', props.election?.id) },
]);

const electionDateRange = computed(() => {
    if (!props.election) return '';
    const format = (dateStr: string) => new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric',
    });
    return `${format(props.election.start_date)} - ${format(props.election.end_date)}`;
});

const processedPositions = computed(() => {
    if (!props.positions) return [];
    
    return Object.entries(props.positions).map(([name, candidates]) => {
        const sorted = [...candidates].sort((a, b) => b.votes - a.votes);
        const totalVotes = sorted.reduce((total, c) => total + c.votes, 0);
        return { name, candidates: sorted, totalVotes };
    });
});

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

const closeUploadDialog = () => {
    isUploadDialogOpen.value = false;
    form.reset();
    if (fileInput.value) fileInput.value.value = '';
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) form.signature = target.files[0];
};

const submitSignature = () => {
    form.post(route('signature.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            closeUploadDialog();
            toast.success('E-Signature saved successfully!'); 
        },
        onError: () => toast.error('Failed to upload signature. Please check the file.')
    });
};

const executeRemoveSignature = () => {
    router.delete(route('signature.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteDialogOpen.value = false; 
            closeUploadDialog();
            toast.success('E-Signature removed successfully!');
        },
        onError: () => toast.error('Failed to remove signature.')
    });
};

// --- PDF PREVIEW MODAL LOGIC ---
const isPreviewDialogOpen = ref(false);
const pdfPreviewUrl = ref<string | null>(null);
const pdfBlob = ref<Blob | null>(null);
const isDownloading = ref(false);

const openPdfPreview = async () => {
    if (!isDataReady.value) {
        return toast.warning('Election data is not fully loaded yet.');
    }

    isPreviewDialogOpen.value = true;
    pdfPreviewUrl.value = null;

    try {
        const blob = await exportElectionResultsPdf(
            props.election!, 
            props.positions!, 
            props.signatureUrl, 
            currentUserName.value,
            true
        );

        if (blob) {
            pdfBlob.value = blob;
            pdfPreviewUrl.value = URL.createObjectURL(blob);
        } else {
            throw new Error("PDF generation returned void.");
        }
    } catch (error) {
        console.error(error);
        toast.error('Failed to generate PDF preview.');
        isPreviewDialogOpen.value = false;
    }
};

const handlePreviewDialogClose = (isOpen: boolean) => {
    isPreviewDialogOpen.value = isOpen;
    if (!isOpen && pdfPreviewUrl.value) {
        URL.revokeObjectURL(pdfPreviewUrl.value);
        pdfPreviewUrl.value = null;
    }
};

const downloadFromPreview = async () => {
    if (!pdfPreviewUrl.value) return;
    
    isDownloading.value = true;
    try {
        await new Promise(resolve => setTimeout(resolve, 400));
        
        const link = document.createElement('a');
        link.href = pdfPreviewUrl.value;
        link.download = `${props.election?.name.replace(/\s+/g, '_')}_Results.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Download failed:', error);
        toast.error('An error occurred while downloading.');
    } finally {
        isDownloading.value = false;
    }
};
</script>

<template>
    <Head :title="`Results - ${electionName}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4">
            <!-- Header -->
            <div class="flex items-end justify-between">
                <div>
                    <h1 class="mb-2 text-2xl font-bold">Election Results</h1>
                    <h2 class="text-xl">{{ props.election ? electionName : 'Loading...' }}</h2>
                    <p v-if="electionDateRange" class="text-muted-foreground">
                        {{ electionDateRange }}
                    </p>
                </div>

                <div class="flex flex-row items-center gap-2">
                    <Button @click="openDialog" variant="outline">
                        <Upload class="w-4 h-4 mr-2"/>
                        <span>{{ signatureUrl ? 'Manage E-Signature' : 'Upload E-Signature' }}</span>
                    </Button>
                    <Button @click="openPdfPreview" variant="default">
                        <Eye class="mr-2 h-4 w-4" />
                        <span>Preview PDF</span>
                    </Button>
                </div>
            </div>

            <!-- Content -->
            <div v-if="!isDataReady" class="py-8 text-center">
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
                                    <!-- DRY improvement: Replaced multiple empty TableCells with colspan -->
                                    <TableCell colspan="2" />
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
                    Please upload a clear PNG/BMP image of your e-signature. This will be attached to your account.
                </DialogDescription>
            </DialogHeader>

            <div v-if="signatureUrl && !isReplacing" class="flex flex-col gap-4 py-4">
                <div class="flex items-center justify-center rounded-lg border border-dashed p-6 bg-white shadow-sm">
                    <img :src="signatureUrl" alt="Your E-Signature" class="max-h-32 object-contain" />
                </div>
                
                <DialogFooter class="mt-4 sm:justify-between w-full">
                    <Button type="button" variant="outline" @click="closeUploadDialog">
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
                        <label for="signature" class="text-sm font-medium">Signature File (PNG and BMP format only)</label>
                        <input 
                            id="signature" 
                            type="file" 
                            accept="image/png, image/bmp" 
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
                    <Button v-else type="button" variant="outline" @click="closeUploadDialog">
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

    <!-- Preview PDF Dialog -->
    <Dialog :open="isPreviewDialogOpen" @update:open="handlePreviewDialogClose">
        <DialogContent class="sm:max-w-[900px] h-[85vh] flex flex-col">
            <DialogHeader>
                <DialogTitle>Preview Election Results</DialogTitle>
                <DialogDescription>
                    Review the generated PDF document before downloading.
                </DialogDescription>
            </DialogHeader>

            <!-- PDF Viewer Iframe -->
            <div class="flex-1 w-full mt-2 bg-muted/30 rounded-md overflow-hidden border">
                <iframe 
                    v-if="pdfPreviewUrl" 
                    :src="pdfPreviewUrl" 
                    class="w-full h-full" 
                    title="PDF Preview"
                ></iframe>
                <div v-else class="flex flex-col items-center justify-center h-full text-muted-foreground">
                    <span class="animate-pulse">Generating document preview...</span>
                </div>
            </div>

            <DialogFooter class="mt-4 sm:justify-end gap-2">
                <Button type="button" variant="outline" @click="handlePreviewDialogClose(false)">
                    Close
                </Button>
                <Button 
                    type="button" 
                    variant="default" 
                    @click="downloadFromPreview" 
                    :disabled="!pdfPreviewUrl || isDownloading"
                >
                    <template v-if="isDownloading">
                        <Loader2 class="mr-2 h-4 w-4 animate-spin" />
                        Downloading...
                    </template>
                    <template v-else>
                        <FileDown class="mr-2 h-4 w-4" />
                        Download PDF
                    </template>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>