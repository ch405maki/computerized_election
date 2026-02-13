<script setup lang="ts">
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { toTypedSchema } from '@vee-validate/zod';
import { Loader2 } from "lucide-vue-next"; // Import the loader icon
import * as z from 'zod';
import { ref } from 'vue';

const emit = defineEmits(['submit']);

// Form validation schema
const formSchema = toTypedSchema(z.object({
    student_number: z.string().min(5, "Student number must be at least 5 characters"),
    first_name: z.string().min(2, "First name must be at least 2 characters"),
    middle_name: z.string().optional(),
    last_name: z.string().min(2, "Last name must be at least 2 characters"),
    student_year: z.string().min(1, "Please select year"),
    sex: z.string().min(1, "Please select gender"),
    password: z.string().min(6, "Password must be at least 6 characters"),
}));

const formData = ref({
    student_number: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    student_year: '',
    sex: '',
    password: '',
});

const isLoading = ref(false);

const onSubmit = async () => {
    isLoading.value = true;
    try {
        emit('submit', formData.value);
    } finally {
        setTimeout(() => {
            isLoading.value = false;
        }, 1000); 
    }
};
</script>

<template>
    <Form :validation-schema="formSchema" @submit="onSubmit" class="mt-4">
        <div class="grid gap-2">
            <!-- Student Number -->
            <FormField v-slot="{ componentField }" name="student_number">
                <FormItem>
                    <FormLabel>Student Number</FormLabel>
                    <FormControl>
                        <Input 
                            type="text" 
                            v-bind="componentField" 
                            v-model="formData.student_number" 
                            placeholder="e.g. 2023-00123"
                            :disabled="isLoading"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="last_name">
                <FormItem>
                    <FormLabel>Last Name</FormLabel>
                    <FormControl>
                        <Input 
                            type="text" 
                            v-bind="componentField" 
                            v-model="formData.last_name" 
                            placeholder="Surname"
                            :disabled="isLoading"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <FormField v-slot="{ componentField }" name="first_name">
                    <FormItem>
                        <FormLabel>First Name </FormLabel>
                        <FormControl>
                            <Input 
                                type="text" 
                                v-bind="componentField" 
                                v-model="formData.first_name" 
                                placeholder="Given Name"
                                :disabled="isLoading"
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="middle_name">
                    <FormItem>
                        <FormLabel>Middle Name <span class="text-muted-foreground text-xs">(Optional)</span></FormLabel>
                        <FormControl>
                            <Input 
                                type="text" 
                                v-bind="componentField" 
                                v-model="formData.middle_name" 
                                placeholder="Middle Name"
                                :disabled="isLoading"
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>
            </div>

            <FormField v-slot="{ componentField }" name="student_year">
                <FormItem>
                    <FormLabel>Year</FormLabel>
                    <Select v-bind="componentField" v-model="formData.student_year" :disabled="isLoading">
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue placeholder="Select year" />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem value="1">First Year</SelectItem>
                            <SelectItem value="2">Second Year</SelectItem>
                            <SelectItem value="3">Third Year</SelectItem>
                            <SelectItem value="4">Fourth Year</SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="sex">
                <FormItem>
                    <FormLabel>Gender</FormLabel>
                    <Select v-bind="componentField" v-model="formData.sex" :disabled="isLoading">
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue placeholder="Select gender" />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem value="male">Male</SelectItem>
                            <SelectItem value="female">Female</SelectItem>
                            <SelectItem value="other">Other</SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="password">
                <FormItem>
                    <FormLabel>Password</FormLabel>
                    <FormControl>
                        <Input 
                            type="password" 
                            v-bind="componentField" 
                            v-model="formData.password" 
                            :disabled="isLoading"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <div class="flex justify-end gap-2 mt-2">
                <slot name="actions" :isLoading="isLoading">
                    <Button type="submit" :disabled="isLoading">
                        <Loader2 v-if="isLoading" class="w-4 h-4 mr-2 animate-spin" />
                        <span v-if="!isLoading">Register Voter</span>
                        <span v-else>Processing...</span>
                    </Button>
                </slot>
            </div>
        </div>
    </Form>
</template>