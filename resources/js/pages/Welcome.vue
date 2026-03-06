<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
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

// --- Slideshow Logic ---
const images = [
  '/images/adminbg1.png',
  '/images/adminbg2.jpg',
  '/images/adminbg3.png',
  '/images/ausl6.jpg'
];

const currentIndex = ref(0);
let timer: any = null;

onMounted(() => {
  timer = setInterval(() => {
    currentIndex.value = (currentIndex.value + 1) % images.length;
  }, 5000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});

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
  
  <div class="relative min-h-screen flex flex-col overflow-hidden">
    
    <div class="absolute inset-0 z-0">
      <transition-group name="fade">
        <div 
          v-for="(img, index) in images" 
          :key="img"
          v-show="currentIndex === index"
          class="absolute inset-0 bg-cover bg-center bg-no-repeat"
          :style="{ backgroundImage: `url('${img}')` }"
        >
          <div class="absolute inset-0 bg-black/50"></div>
        </div>
      </transition-group>
    </div>

    <WelcomeHeader class="relative z-10 bg-white/20" />

    <main class="relative z-10 flex-grow grid place-items-center">
      <div class="w-full px-6 flex justify-center">
        <Card class="w-full max-w-[400px] rounded-2xl text-white bg-white/10 border border-white/20 backdrop-blur-xl px-5 shadow-2xl">
          <CardHeader>
            <CardTitle class="text-center tracking-widest mb-4">Voter's Login</CardTitle>
          </CardHeader>
          <CardContent class="flex flex-col justify-center">
            <form @submit.prevent="submit">
              <div class="mb-3">
                <Label for="student_number" class="text-white">Student Number</Label>
                <Input
                  id="student_number"
                  v-model="form.student_number"
                  type="text"
                  required
                  placeholder="Enter Student Number"
                  class="pr-10 border-2 border-white/50 text-white placeholder:text-white/60 focus:ring-white bg-transparent"
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
                    placeholder="Enter Password"
                    class="pr-10 border-2 border-white/50 text-white placeholder:text-white/60 focus:ring-white bg-transparent"
                  />
                  <HoverCard :open-delay="0" :close-delay="0">
                    <HoverCardTrigger as-child>
                      <button
                        type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-colors"
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

              <div v-if="form.errors.invalid_credentials" class="mb-3 text-center">
                <p class="text-[0.75rem] leading-tight text-white">
                  * Please check if your Student Number or Password is correct,
                  or email at
                  <span class="font-bold underline">onlinesupport@arellanolaw.edu</span>
                  for verification.
                </p>
              </div>

              <Button
                type="submit"
                class="w-full bg-purple-700 hover:bg-purple-600 text-white transition-colors"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Logging in...' : 'Login' }}
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>
    </main>

    <WelcomeFooter class="relative z-10" />
  </div>
</template>

<style scoped>
/* Transitions for the background images */
.fade-enter-active {
  animation: kenburns 12s infinite alternate; /* Slower zoom for subtler effect */
  transition: opacity 1.5s ease-in-out;
}

.fade-leave-active {
  transition: opacity 1.5s ease-in-out;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Form Styles */
:deep(input) {
  background-color: rgba(255, 255, 255, 0.1) !important;
  color: white !important;
}

:deep(input:focus) {
  border-color: white !important;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3) !important;
}
</style>