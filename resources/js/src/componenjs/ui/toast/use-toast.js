import { ref } from 'vue'

const toasts = ref([])

export function useToast() {
  function toast({ title, description, variant = 'default' }) {
    const id = Math.random().toString(36).slice(2)
    toasts.value.push({ id, title, description, variant })
    setTimeout(() => {
      toasts.value = toasts.value.filter(t => t.id !== id)
    }, 4000)
  }
  return { toast, toasts }
}
