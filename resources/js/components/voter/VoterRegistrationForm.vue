<script setup lang="ts">
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';
import { ref } from 'vue';

const emit = defineEmits(['submit']);

// Form validation schema
const formSchema = toTypedSchema(z.object({
    student_number: z.string().min(5, "Student number must be at least 5 characters"),
    full_name: z.string().min(3, "Full name must be at least 3 characters"),
    student_year: z.string().min(1, "Please select year"),
    class_type: z.string().min(1, "Please select class type"),
    sex: z.string().min(1, "Please select gender"),
    password: z.string().min(6, "Password must be at least 6 characters"),
}));

const formData = ref({
    student_number: '',
    full_name: '',
    student_year: '',
    class_type: '',
    sex: '',
    password: '',
});

const isLoading = ref(false);

const onSubmit = async () => {
    isLoading.value = true;
    try {
        emit('submit', formData.value);
    } finally {
        isLoading.value = false;
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
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Full Name -->
            <FormField v-slot="{ componentField }" name="full_name">
                <FormItem>
                    <FormLabel>Full Name</FormLabel>
                    <FormControl>
                        <Input 
                            type="text" 
                            v-bind="componentField" 
                            v-model="formData.full_name" 
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Student Year -->
            <FormField v-slot="{ componentField }" name="student_year">
                <FormItem>
                    <FormLabel>Year</FormLabel>
                    <Select v-bind="componentField" v-model="formData.student_year">
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

            <!-- Class Type -->
            <FormField v-slot="{ componentField }" name="class_type">
                <FormItem>
                    <FormLabel>Class Type</FormLabel>
                    <Select v-bind="componentField" v-model="formData.class_type">
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue placeholder="Select class type" />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem value="A">Class A</SelectItem>
                            <SelectItem value="B">Class B</SelectItem>
                            <SelectItem value="C">Class C</SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Gender -->
            <FormField v-slot="{ componentField }" name="sex">
                <FormItem>
                    <FormLabel>Gender</FormLabel>
                    <Select v-bind="componentField" v-model="formData.sex">
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

            <!-- Password -->
            <FormField v-slot="{ componentField }" name="password">
                <FormItem>
                    <FormLabel>Password</FormLabel>
                    <FormControl>
                        <Input 
                            type="password" 
                            v-bind="componentField" 
                            v-model="formData.password" 
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <div class="flex justify-end gap-2 mt-2">
                <slot name="actions" :isLoading="isLoading">
                    <Button type="submit" :disabled="isLoading">
                        <span v-if="!isLoading">Register Voter</span>
                        <span v-else>Processing...</span>
                    </Button>
                </slot>
            </div>
        </div>
    </Form>
</template>