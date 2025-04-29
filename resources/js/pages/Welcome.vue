<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head, Link  } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from "vue-toastification";

const toast = useToast();
const form = useForm({
  student_number: '',
  password: '',
});

const submit = () => {
  form.post(route('voter.login'), {
    onSuccess: () => toast.success("Login successful!"),
    onError: () => toast.error("Invalid credentials."),
  });
};
</script>

<template>
  <Head title="Election Login" />
  <div class="flex min-h-screen items-center justify-center dark:bg-zinc-950 bg-white p-6">
    <Card class="w-full max-w-sm">
      <CardHeader>
        <CardTitle class="text-center">Voter Login</CardTitle>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit">
          <div class="mb-4">
            <Label for="student_number">Student Number</Label>
            <Input id="student_number" v-model="form.student_number" type="text" required />
          </div>
          <div class="mb-4">
            <Label for="password">Password</Label>
            <Input id="password" v-model="form.password" type="password" required />
          </div>
          <Button type="submit" class="w-full" :disabled="form.processing">Login</Button>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
