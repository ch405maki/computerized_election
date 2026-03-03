<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
status?: string;
}>();

const form = useForm({
email: '',
});

const submit = () => {
form.post(route('password.email'));
};
</script>

<template>
<div class="min-h-screen w-full flex items-center justify-center pr-16 bg-cover bg-center bg-no-repeat" 
        style="background-image: url('/images/bg-only.jpg');"> 
    <Head title="Forgot Password" />

    <div class="w-full max-w-md p-10 rounded-3xl bg-black/40 backdrop-blur-md border border-white/20 text-white shadow-2xl">
        
        <div class="flex flex-col items-center mb-8">
            <Link href="/" class="transition-transform hover:scale-110 active:scale-95">
                <img src="/images/logo/ausl.png" alt="University Logo" class="h-28 w-28 mb-4 object-contain cursor-pointer" />
            </Link>
            
            <h1 class="text-2xl font-semibold">Forgot Password</h1>
            <p class="text-sm text-gray-200 mt-2 text-center max-w-xs">
                Enter your email below to receive a password reset link
            </p>
        </div>

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-400">
            {{ status }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="email" class="text-white">Email Address</Label>
                    <Input 
                        id="email" 
                        type="email" 
                        name="email" 
                        v-model="form.email" 
                        autofocus 
                        placeholder="email@example.com" 
                        class="bg-blue-50/90 text-black border-none h-12"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="my-6">
                    <Button 
                        class="w-full bg-[#1e1e1e] hover:bg-black text-white h-12 rounded-lg" 
                        :disabled="form.processing"
                    >
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                        Email password reset link
                    </Button>
                </div>
            </form>

            <div class="text-center text-sm text-gray-300">
                Or, return to
                <TextLink :href="route('login')" class="text-white underline font-semibold ml-1">Log in</TextLink>
            </div>
        </div>
    </div>
</div>
</template>