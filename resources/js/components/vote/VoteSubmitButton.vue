<script setup lang="ts">
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'
import { Checkbox } from '@/components/ui/checkbox'
import { ref } from 'vue'

const props = defineProps<{
  isVoting: boolean
  allPositionsSelected: boolean
}>()

const emit = defineEmits(['vote'])

const isDialogOpen = ref(false)
const isConfirmed = ref(false)

const handleVoteClick = () => {
  isDialogOpen.value = true
}

const confirmVote = () => {
  if (isConfirmed.value) {
    emit('vote')
    isDialogOpen.value = false
    isConfirmed.value = false // Reset for next time
  }
}
</script>

<template>
  <div>
    <div class="container mx-auto flex justify-end">
      <Button 
        @click="handleVoteClick"
        :disabled="isVoting || !allPositionsSelected"
        size="lg"
        class="min-w-[200px]"
      >
        <span v-if="!isVoting">Submit Your Vote</span>
        <span v-else class="flex items-center gap-2">
          <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Processing...
        </span>
      </Button>

      <Dialog v-model:open="isDialogOpen">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Your Vote</DialogTitle>
            <DialogDescription>
              Please review your selections carefully. Votes cannot be changed after submission.
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center space-x-2 py-4">
            <Checkbox id="terms" v-model:checked="isConfirmed" />
            <label
              for="terms"
              class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >
              I confirm that my selections are final and I want to submit my vote.
            </label>
          </div>

          <DialogFooter>
            <Button variant="outline" @click="isDialogOpen = false">
              Cancel
            </Button>
            <Button 
              type="submit"
              :disabled="!isConfirmed"
              @click="confirmVote"
            >
              Submit Vote
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>