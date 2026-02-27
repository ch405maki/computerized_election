<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/components/ui/hover-card';
import { useToast } from 'vue-toastification';
import WelcomeHeader from '@/components/welcome/WelcomeHeader.vue';
import WelcomeFooter from '@/components/welcome/WelcomeFooter.vue';
import { Eye, EyeOff } from 'lucide-vue-next';

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
  <div class="min-h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('/images/AU BG.jpg');">
    <WelcomeHeader class="bg-purple-200/25" />

    <div class="min-h-[calc(100vh-130px)] grid place-items-center">
      <div class="flex items-center justify-center gap-0">
      <!-- Card -->
      <Card class="rounded-2xl rounded-r-none w-[330px] h-[378px] text-white bg-transparent-100 border backdrop-blur-xl px-5 shadow-2xl">
        <CardHeader>
          <CardTitle class="text-center tracking-widest mb-4">Voter's Login</CardTitle>
        </CardHeader>
      <CardContent class="flex flex-col justify-center flex-1 overflow-y-auto">
          <form @submit.prevent="submit">
            <div class="mb-3">
              <Label for="student_number" class="text-white">Student Number</Label>
              <Input
                id="student_number"
                v-model="form.student_number"
                type="text"
                required
                class="pr-10 border-2 border-white text-white placeholder-white focus:outline-none focus:ring-2 focus:ring-white bg-transparent"
              />
            </div>

            <div class="mb-3">
              <Label for="password" class="text-white">Password</Label>
              <div class="relative">
                <Input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  class="pr-10 border-2 border-white text-white placeholder-white focus:outline-none focus:ring-2 focus:ring-white bg-transparent"
                />
                <HoverCard>
                  <HoverCardTrigger as-child>
                    <button
                      type="button"
                      class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                      @click="togglePasswordVisibility"
                    >
                      <Eye v-if="!showPassword" class="h-5 w-5 text-white" />
                      <EyeOff v-else class="h-5 w-5 text-white" />
                    </button>
                  </HoverCardTrigger>
                  <HoverCardContent class="w-auto p-2 text-sm">
                    {{ showPassword ? 'Hide password' : 'Show password' }}
                  </HoverCardContent>
                </HoverCard>
              </div>
            </div>

            <div v-if="form.errors.invalid_credentials" class="mb-3">
              <p class="text-center text-[0.70rem] leading-tight">
                * Please check if your Student Number or Password is correct,
                or email at
                <span class="font-bold text-purple-900 underline">onlinesupport@arellanolaw.edu</span>
                for verification.
              </p>
            </div>

            <Button
              type="submit"
              class="w-full bg-purple-700 rounded-l hover:bg-purple-600"
              :disabled="form.processing"
            >
              Login
            </Button>
          </form>
        </CardContent>
      </Card>

      <!-- Image -->
      <img src="/images/STATUE.jpg" alt="Login illustration"
          class="w-[330px] h-[378px] brightness-[1.25] object-cover border-t border-r border-b rounded-r-2xl shadow-2xl" />
            </div>
          </div>
          <WelcomeFooter />
        </div>
</template>