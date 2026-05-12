import { ref } from 'vue';
import { useApi } from './useApi';

export function useVies() {
  const loading = ref(false);
  const error = ref(null);
  const result = ref(null);

  const { post } = useApi();

  /**
   * Validate a European VAT number via VIES.
   *
   * @param {string} nif  Full VAT including country code prefix, e.g. "PT500123456"
   */
  const validate = async (nif) => {
    loading.value = true;
    error.value = null;
    result.value = null;

    try {
      const data = await post(`/api/v1/vies/${encodeURIComponent(nif)}`);
      result.value = data;

      if (!data.valid) {
        error.value = data.message ?? 'NIF nao encontrado no VIES.';
      }

      return data;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro ao validar NIF na VIES.';
      return null;
    } finally {
      loading.value = false;
    }
  };

  return { loading, error, result, validate };
}
