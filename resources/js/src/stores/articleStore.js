import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useArticleStore = defineStore('article-store', () => {
  const isModalOpen = ref(false);
  const selectedArticle = ref(null);
  const isSubmitting = ref(false);

  const openCreate = () => {
    selectedArticle.value = null;
    isModalOpen.value = true;
  };

  const openEdit = (article) => {
    selectedArticle.value = article;
    isModalOpen.value = true;
  };

  const closeModal = () => {
    isModalOpen.value = false;
    selectedArticle.value = null;
  };

  return {
    isModalOpen,
    selectedArticle,
    isSubmitting,
    openCreate,
    openEdit,
    closeModal,
  };
});
