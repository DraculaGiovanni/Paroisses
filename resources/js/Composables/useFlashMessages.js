import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from './useToast';

export function useFlashMessages() {
  const page = usePage();
  const { success: showSuccess, error: showError } = useToast();
  
  // Surveiller les messages flash
  watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
      showSuccess(flash.success);
    }
    
    if (flash?.error) {
      showError(flash.error);
    }
  }, { immediate: true, deep: true });
}
