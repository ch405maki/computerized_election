<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/components/ui/hover-card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import WelcomeFooter from '@/components/welcome/WelcomeFooter.vue';
import WelcomeHeader from '@/components/welcome/WelcomeHeader.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
const form = useForm({
    student_number: '',
    password: '',
});

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('voter.login'), {
        onSuccess: () => toast.success('Login successful!'),
        onError: () => {
            if (form.errors.student_number) {
                toast.error(form.errors.student_number);
            }
        },
    });
};
</script>

<template>
    <Head title="Election Login" />
    <div class="min-h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('/images/bg.jpg')">
        <WelcomeHeader />

        <div class="grid min-h-[calc(100vh-130px)] place-items-center">
            <Card class="w-full max-w-sm border-2 border-purple-900 bg-white/95 text-purple-900 backdrop-blur-sm">
                <CardHeader>
                    <CardTitle class="text-center tracking-widest">Voter's Login</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <Label for="student_number">Student Number</Label>
                            <Input id="student_number" v-model="form.student_number" type="text" required />
                        </div>
                        <div class="mb-4">
                            <Label for="password">Password</Label>
                            <div class="relative">
                                <Input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" required class="pr-10" />
                                <HoverCard>
                                    <HoverCardTrigger as-child>
                                        <button
                                            type="button"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                            @click="togglePasswordVisibility"
                                        >
                                            <Eye v-if="!showPassword" class="h-5 w-5" />
                                            <EyeOff v-else class="h-5 w-5" />
                                        </button>
                                    </HoverCardTrigger>
                                    <HoverCardContent class="w-auto p-2 text-sm">
                                        {{ showPassword ? 'Hide password' : 'Show password' }}
                                    </HoverCardContent>
                                </HoverCard>
                            </div>
                        </div>
                        <div v-if="form.errors.invalid_credentials" class="mb-4">
                            <p class="text-center">
                                * Please check if your Student Number or Password is correct or email at
                                <span class="font-bold text-purple-900 underline">onlinesupport@arellanolaw.edu</span>
                                for verification.
                            </p>
                        </div>
                        <Button type="submit" class="w-full bg-purple-900 hover:bg-purple-700" :disabled="form.processing"> Login </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
        <WelcomeFooter />
    </div>
</template>
