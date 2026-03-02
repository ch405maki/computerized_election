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

    <!-- Email -->
    <div class="grid gap-1">
        <Label for="email" class="text-xs">Email address</Label>
        <Input
        id="email"
        type="email"
        required
        :tabindex="2"
        autocomplete="email"
        v-model="form.email"
        placeholder="email@example.com"
        class="text-xs py-1.5"
        />
        <InputError :message="form.errors.email" class="text-[11px]" />
    </div>

    <!-- Password -->
    <div class="grid gap-1">
        <Label for="password" class="text-xs">Password</Label>
        <Input
        id="password"
        type="password"
        required
        :tabindex="3"
        autocomplete="new-password"
        v-model="form.password"
        placeholder="Password"
        class="text-xs py-1.5"
        />
        <InputError :message="form.errors.password" class="text-[11px]" />
    </div>

    <!-- Confirm Password -->
    <div class="grid gap-1">
        <Label for="password_confirmation" class="text-xs">Confirm password</Label>
        <Input
        id="password_confirmation"
        type="password"
        required
        :tabindex="4"
        autocomplete="new-password"
        v-model="form.password_confirmation"
        placeholder="Confirm password"
        class="text-xs py-1.5"
        />
        <InputError :message="form.errors.password_confirmation" class="text-[11px]" />
    </div>

    <!-- Submit Button -->
    <Button
        type="submit"
        class="mt-3 w-full text-xs py-1.5"
        tabindex="5"
        :disabled="form.processing"
    >
        <LoaderCircle v-if="form.processing" class="h-3 w-3 animate-spin" />
        Create account
    </Button>
    </div>

    <!-- Footer -->
    <div class="text-center text-[11px] text-muted-foreground mt-2">
    Already have an account?
    <TextLink :href="route('login')" :tabindex="6">Log in</TextLink>
    </div>
</form>
</AuthSplitLayout>
</template>