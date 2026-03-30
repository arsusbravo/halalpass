<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import logoSm from '@images/logo-sm.png';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img :src="logoSm" alt="App Logo" :class="[className]" />
</template>
