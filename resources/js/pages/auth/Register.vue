<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
name: '',
email: '',
password: '',
password_confirmation: '',
});

const submit = () => {
form.post(route('register'), {
onFinish: () => form.reset('password', 'password_confirmation'),
});
};
</script>

<template>
<AuthSplitLayout
title="Create an account"
description="Enter your details below to create your account"
>
<Head title="Register" />

        <template #logo>
            <div class="flex items-center gap-2 mb-4">
                <img src="/logo.svg" class="h-10 w-10" alt="Logo" />
                <span class="text-xl font-bold">Your Brand</span>
            </div>
        </template>

        <form @submit.prevent="submit" class="flex flex-col gap-5">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="name" class="font-medium text-xs">Name</Label>
                    <Input 
                        id="name" 
                        type="text" 
                        required 
                        autofocus 
                        v-model="form.name" 
                        class="h-11 rounded-xl" 
                        placeholder="Full name" 
                    />
                    <InputError :message="form.errors.name" />
                </div>
<form @submit.prevent="submit" class="flex flex-col gap-4 text-xs">
    <div class="grid gap-4">
    <!-- Name -->
    <div class="grid gap-1">
        <Label for="name" class="text-xs">Name</Label>
        <Input
        id="name"
        type="text"
        required
        autofocus
        :tabindex="1"
        autocomplete="name"
        v-model="form.name"
        placeholder="Full name"
        class="text-xs py-1.5"
        />
        <InputError :message="form.errors.name" class="text-[11px]" />
    </div>

                <div class="grid gap-2">
                    <Label for="email" class="font-medium text-xs">Email address</Label>
                    <Input 
                        id="email" 
                        type="email" 
                        required 
                        v-model="form.email" 
                        class="h-11 rounded-xl" 
                        placeholder="email@example.com" 
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="password" class="font-medium text-xs">Password</Label>
                        <Input 
                            id="password" 
                            type="password" 
                            required 
                            v-model="form.password" 
                            class="h-11 rounded-xl" 
                            placeholder="••••••••" 
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="font-medium text-xs">Confirm</Label>
                        <Input 
                            id="password_confirmation" 
                            type="password" 
                            required 
                            v-model="form.password_confirmation" 
                            class="h-11 rounded-xl"  
                            placeholder="••••••••" 
                        />
                    </div>
                </div>
                <InputError :message="form.errors.password" />
                <InputError :message="form.errors.password_confirmation" />

                <Button 
                    type="submit" 
                    class="mt-4 w-full py-6 font-bold rounded-lg transition-all active:scale-95" 
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
                    Sign Up
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground mt-2">
                Already have an account?
                <TextLink :href="route('login')" class="hover:underline underline-offset-4 font-semibold ml-1">
                    Log in
                </TextLink>
            </div>
        </form>
    </AuthSplitLayout>
</template>