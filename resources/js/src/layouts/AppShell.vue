<script setup lang="ts">
import AppLayout from './AppLayout.vue';
// User is guaranteed to be authenticated by Laravel's auth middleware
// on the Blade route. window.__AUTH_USER__ is injected by app.blade.php.
</script>

<template>
    <AppLayout />
</template>
