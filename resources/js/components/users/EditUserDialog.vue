<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import axios from 'axios';
import { Loader2, UserRoundPen } from 'lucide-vue-next';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

type Permissions = Record<string, boolean>;

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
}

interface UserProp extends User {
    permissions?: any;
}

interface UserData extends User {
    permissions: Permissions;
    password?: string; // Added optional password field
}

const props = defineProps<{ user: UserProp }>();
const toast = useToast();

const permissionModules: Record<string, string[]> = {
    Dashboard: ['showDashboardTab', 'showRanking', 'showChart', 'showCandidateNames'],
    Voters: ['showVoterTab', 'uploadExcel', 'addVoter', 'editVoter', 'deleteVoter'],
    Candidates: ['showCandidateTab', 'addCandidate', 'deleteCandidate'],
    Reports: ['showReportsTab', 'showElectionResults'],
    Election: ['showElectionTab', 'createElection', 'editElection', 'deleteElection'],
};

const selectedModule = ref<string>(Object.keys(permissionModules)[0]);
const isSaving = ref(false);

const defaultPermissions: Permissions = Object.values(permissionModules)
    .flat()
    .reduce((acc, key) => {
        acc[key] = false;
        return acc;
    }, {} as Permissions);

const userPermissions = (): UserData => {
    const filteredPermissions: Permissions = { ...defaultPermissions };

    let rawPermissions = props.user.permissions;

    if (typeof rawPermissions === 'string') {
        try {
            rawPermissions = JSON.parse(rawPermissions);
        } catch (e) {
            rawPermissions = {};
        }
    }

    if (rawPermissions && typeof rawPermissions === 'object') {
        for (const key of Object.keys(defaultPermissions)) {
            if (key in rawPermissions) {
                const val = rawPermissions[key];
                filteredPermissions[key] = val === true || val === 'true' || val === 1;
            }
        }
    }

    return {
        ...props.user,
        permissions: filteredPermissions,
        password: '', // Initialize as empty
    };
};

const userData = ref<UserData>(userPermissions());

// Open Dialog
const openDialog = () => {
    userData.value = userPermissions();
    selectedModule.value = Object.keys(permissionModules)[0];
};

// Update User
const updateUser = async () => {
    isSaving.value = true;
    try {
        // Clone the payload so we can manipulate it before sending
        const payload = { ...userData.value };
        
        // Remove password from payload if it wasn't filled out
        if (!payload.password) {
            delete payload.password;
        }

        await axios.put(`/api/users/${payload.id}`, payload);

        toast.success('User updated successfully!');
        setTimeout(() => {
            location.reload();
        }, 2000);
    } catch (error) {
        toast.error('Failed to update user. Please try again.');
        isSaving.value = false;
    }
};
</script>

<template>
    <Sheet>
        <!-- Sheet Trigger -->
        <SheetTrigger variant="destructive" @click="openDialog">
            <div class="mr-2 rounded-md bg-blue-50 px-3 py-[10px] hover:bg-blue-100">
                <UserRoundPen class="h-4 w-4 text-blue-500 hover:text-blue-700 dark:text-blue-400" />
            </div>
        </SheetTrigger>

        <!-- Sheet Content -->
        <SheetContent class="flex h-full max-h-screen flex-col text-gray-900 dark:text-gray-200">
            <SheetHeader>
                <SheetTitle>Edit User</SheetTitle>
                <SheetDescription>Modify user details and save changes.</SheetDescription>
            </SheetHeader>

            <div class="-mr-2 mt-4 flex-1 overflow-y-auto pr-2">
                <form @submit.prevent="updateUser">
                    <div class="space-y-4 pb-6">
                        <!-- Name Field -->
                        <label class="form-field">
                            <span class="field-label">Name</span>
                            <input v-model="userData.name" type="text" class="input" required />
                        </label>

                        <!-- Email Field -->
                        <label class="form-field">
                            <span class="field-label">Email</span>
                            <input v-model="userData.email" type="email" class="input" required />
                        </label>

                        <!-- Password Field -->
                        <label class="form-field">
                            <span class="field-label">Password <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(Leave blank to keep current)</span></span>
                            <input v-model="userData.password" type="password" class="input" placeholder="Enter new password" />
                        </label>

                        <!-- Role Field -->
                        <label class="form-field">
                            <span class="field-label">Role</span>
                            <select v-model="userData.role" class="input">
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </label>

                        <!-- Status Field -->
                        <label class="form-field">
                            <span class="field-label">Status</span>
                            <select v-model="userData.status" class="input">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </label>

                        <!-- Permissions Field -->
                        <div class="pt-2">
                            <span class="field-label mb-2 block font-medium">Permissions</span>

                            <div class="space-y-4 rounded-md border border-gray-300 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-800">
                                <!-- Module Dropdown -->
                                <select v-model="selectedModule" class="input font-medium">
                                    <option v-for="(keys, moduleName) in permissionModules" :key="moduleName" :value="moduleName">
                                        {{ moduleName }}
                                    </option>
                                </select>

                                <hr class="border-gray-300 dark:border-gray-600" />

                                <!-- Dynamic Checkboxes -->
                                <div class="space-y-4">
                                    <div v-for="key in permissionModules[selectedModule]" :key="key" class="flex items-center space-x-3">
                                        <Checkbox :id="String(key)" v-model:checked="userData.permissions[key]" />
                                        <label
                                            :for="String(key)"
                                            class="cursor-pointer select-none text-sm font-medium capitalize leading-none text-gray-700 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-gray-300"
                                        >
                                            {{
                                                String(key)
                                                    .replace(/([A-Z])/g, ' $1')
                                                    .trim()
                                            }}
                                        </label>
                                    </div>

                                    <p v-if="permissionModules[selectedModule].length === 0" class="text-sm italic text-gray-500">
                                        No permissions available for this module.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <Button type="submit" class="mt-4 w-full" :disabled="isSaving">
                            <Loader2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                            {{ isSaving ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </div>
        </SheetContent>
    </Sheet>
</template>

<style scoped>
.form-field {
    @apply block;
}
.field-label {
    @apply text-gray-700 dark:text-gray-300;
}
.input {
    @apply w-full rounded-md border border-gray-300 bg-gray-100 p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    @apply rounded-full bg-gray-300 dark:bg-gray-600;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    @apply bg-gray-400 dark:bg-gray-500;
}
</style>