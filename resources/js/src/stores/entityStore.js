import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useEntityStore = defineStore('entity-store', () => {
  const isModalOpen = ref(false);
  const selectedEntity = ref(null);
  const isSubmitting = ref(false);

  const openCreate = () => {
    selectedEntity.value = null;
    isModalOpen.value = true;
  };

  const openEdit = (entity) => {
    selectedEntity.value = entity;
    isModalOpen.value = true;
  };

  const closeModal = () => {
    isModalOpen.value = false;
    selectedEntity.value = null;
  };

  return {
    isModalOpen,
    selectedEntity,
    isSubmitting,
    openCreate,
    openEdit,
    closeModal,
  };
});
