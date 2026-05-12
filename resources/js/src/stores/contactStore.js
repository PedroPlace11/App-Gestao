import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useContactStore = defineStore('contact-store', () => {
  const isModalOpen = ref(false);
  const selectedContact = ref(null);
  const isSubmitting = ref(false);

  const openCreate = () => {
    selectedContact.value = null;
    isModalOpen.value = true;
  };

  const openEdit = (contact) => {
    selectedContact.value = contact;
    isModalOpen.value = true;
  };

  const closeModal = () => {
    isModalOpen.value = false;
    selectedContact.value = null;
  };

  return {
    isModalOpen,
    selectedContact,
    isSubmitting,
    openCreate,
    openEdit,
    closeModal,
  };
});
