<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from 'vue-toastification';
import WelcomeHeader from '@/components/welcome/WelcomeHeader.vue';
import WelcomeFooter from '@/components/welcome/WelcomeFooter.vue';

const toast = useToast();
const form = useForm({
  student_number: '',
  password: '',
});

const submit = () => {
  form.post(route('voter.login'), {
    onSuccess: () => toast.success('Login successful!'),
    onError: () => toast.error('Invalid credentials.'),
  });
};
</script>

<template>
  <Head title="Election Login" />
  <WelcomeHeader />

  <!-- Use min-h-vhs for height, flex to center content vertically and horizontally -->
  <div class="min-h-vhs flex items-center justify-center bg-bg-img mt-20">
    <!-- Card is centered inside the flex container -->
    <Card class="w-full max-w-sm text-purple-900 bg-white border-2 border-purple-900">
      <CardHeader>
        <CardTitle class="text-center tracking-widest">Voter Login</CardTitle>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit">
          <div class="mb-4">
            <Label for="student_number">Student Number</Label>
            <Input
              id="student_number"
              v-model="form.student_number"
              type="text"
              required
            />
          </div>
          <div class="mb-4">
            <Label for="password">Password</Label>
            <Input id="password" v-model="form.password" type="password" required />
          </div>
          <Button
            type="submit"
            class="w-full bg-purple-900 hover:bg-purple-700"
            :disabled="form.processing"
          >
            Login
          </Button>
        </form>
      </CardContent>
    </Card>
  </div>

  <WelcomeFooter />
</template>
