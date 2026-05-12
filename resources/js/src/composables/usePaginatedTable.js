import { computed, ref } from 'vue';

export function usePaginatedTable(source, filterFn, initialPageSize = 10) {
  const searchQuery = ref('');
  const page = ref(1);
  const pageSize = ref(initialPageSize);

  const filteredRows = computed(() => {
    const data = source();
    const query = searchQuery.value.trim();

    if (!query) {
      return data;
    }

    return data.filter((row) => filterFn(row, query));
  });

  const totalPages = computed(() => Math.max(1, Math.ceil(filteredRows.value.length / pageSize.value)));

  const paginatedRows = computed(() => {
    const start = (page.value - 1) * pageSize.value;
    return filteredRows.value.slice(start, start + pageSize.value);
  });

  const setSearch = (value) => {
    searchQuery.value = value;
    page.value = 1;
  };

  const setPage = (nextPage) => {
    page.value = Math.min(Math.max(1, nextPage), totalPages.value);
  };

  const setPageSize = (size) => {
    pageSize.value = size;
    page.value = 1;
  };

  return {
    searchQuery,
    page,
    pageSize,
    filteredRows,
    paginatedRows,
    totalPages,
    setSearch,
    setPage,
    setPageSize,
  };
}
