<script setup lang="ts">
import { ref } from "vue";
import {
  Sheet,
  SheetTrigger,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
} from "@/components/ui/sheet"; 
import { Button } from "@/components/ui/button"; 
import { Checkbox } from "@/components/ui/checkbox"; 
import { UserRoundPen } from "lucide-vue-next"; 
import axios from "axios";
import { useToast } from "vue-toastification";

type Permissions = Record<string, boolean>;

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  status: string;
}

interface UserProp extends User {
  permissions?: Permissions;
}

interface UserData extends User {
  permissions: Permissions;
}

const props = defineProps<{ user: UserProp }>();
const toast = useToast();

const permissionModules: Record<string, string[]> = {
  "Dashboard": ["showRanking", "showChart"],
  "Voter List": ["addVoter", "editVoter", "deleteVoter"],
  "Candidates": ["addCandidate", "deleteCandidate"],
  "Reports": ["electionResults", "showVoterTurnout"],
  "Election System": ["createElection", "editElection", "deleteElection"],
};

const selectedModule = ref<string>(Object.keys(permissionModules)[0]);

const defaultPermissions: Permissions = Object.values(permissionModules)
  .flat()
  .reduce((acc, key) => {
    acc[key] = false;
    return acc;
  }, {} as Permissions);

const userPermissions = (): UserData => {
  const filteredPermissions: Permissions = { ...defaultPermissions };

  if (props.user.permissions) {
    for (const key of Object.keys(defaultPermissions)) {
      if (key in props.user.permissions) {
        filteredPermissions[key] = props.user.permissions[key];
      }
    }
  }

  return {
    ...props.user,
    permissions: filteredPermissions,
  };
};

const userData = ref<UserData>(userPermissions());

// Open Dialog
const openDialog = () => {
  userData.value = userPermissions();
  selectedModule.value = Object.keys(permissionModules)[0]; // Reset dropdown on open
};

// Update User
const updateUser = async () => {
  try {
    await axios.put(`/api/users/${userData.value.id}`, userData.value);
    
    toast.success("User updated successfully!");
    setTimeout(() => {
      location.reload(); 
    }, 2000);
  } catch (error) {
    toast.error("Failed to update user. Please try again.");
  }
};
</script>

<template>
  <Sheet>
    <!-- Sheet Trigger -->
    <SheetTrigger variant="destructive" @click="openDialog">
      <div class="bg-blue-50 hover:bg-blue-100 rounded-md mr-2 py-[10px] px-3">
        <UserRoundPen class="w-4 h-4 text-blue-500 dark:text-blue-400 hover:text-blue-700" />
      </div>
    </SheetTrigger>

    <!-- Sheet Content -->
    <SheetContent class="text-gray-900 dark:text-gray-200 flex flex-col h-full max-h-screen">
      <SheetHeader>
        <SheetTitle>Edit User</SheetTitle>
        <SheetDescription>Modify user details and save changes.</SheetDescription>
      </SheetHeader>

      <div class="flex-1 overflow-y-auto pr-2 mt-4 -mr-2">
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
              
              <div class="space-y-4 p-4 border rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                
                <!-- Module Dropdown -->
                <select v-model="selectedModule" class="input font-medium">
                  <option 
                    v-for="(keys, moduleName) in permissionModules" 
                    :key="moduleName" 
                    :value="moduleName"
                  >
                    {{ moduleName }}
                  </option>
                </select>

                <hr class="border-gray-300 dark:border-gray-600" />

                <!-- Dynamic Checkboxes -->
                <div class="space-y-4">
                  <div 
                    v-for="key in permissionModules[selectedModule]" 
                    :key="key"
                    class="flex items-center space-x-3"
                  >
                    <Checkbox 
                      :id="String(key)" 
                      v-model:checked="userData.permissions[key]" 
                    />
                    <label 
                      :for="String(key)" 
                      class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 dark:text-gray-300 capitalize cursor-pointer select-none"
                    >
                      {{ String(key).replace(/([A-Z])/g, ' $1').trim() }}
                    </label>
                  </div>
                  
                  <p v-if="permissionModules[selectedModule].length === 0" class="text-sm text-gray-500 italic">
                    No permissions available for this module.
                  </p>
                </div>
              </div>
            </div>

            <!-- Save Changes Button -->
            <Button type="submit" class="w-full mt-4">Save Changes</Button>
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
  @apply w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200;
}

/* Scrollbar Styles */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-400 dark:bg-gray-500;
}
</style>