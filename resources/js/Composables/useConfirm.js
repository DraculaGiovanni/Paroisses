import { ref } from 'vue';

const isOpen = ref(false);
const dialogConfig = ref({
  title: '',
  message: '',
  confirmText: 'Confirmer',
  cancelText: 'Annuler',
  variant: 'primary',
});
let resolveCallback = null;

export function useConfirm() {
  const confirm = (config = {}) => {
    dialogConfig.value = {
      title: config.title || 'Confirmer l\'action',
      message: config.message || 'Êtes-vous sûr de vouloir continuer ?',
      confirmText: config.confirmText || 'Confirmer',
      cancelText: config.cancelText || 'Annuler',
      variant: config.variant || 'primary',
    };
    
    isOpen.value = true;
    
    return new Promise((resolve) => {
      resolveCallback = resolve;
    });
  };
  
  const handleConfirm = () => {
    isOpen.value = false;
    if (resolveCallback) {
      resolveCallback(true);
      resolveCallback = null;
    }
  };
  
  const handleCancel = () => {
    isOpen.value = false;
    if (resolveCallback) {
      resolveCallback(false);
      resolveCallback = null;
    }
  };
  
  return {
    isOpen,
    dialogConfig,
    confirm,
    handleConfirm,
    handleCancel,
  };
}
