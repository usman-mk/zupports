<script setup lang="ts">
import RestaurantItem from './RestaurantItem.vue';

defineProps<{
    restaurants: any;
}>();
</script>

<template>
    <div
        class="col-lg-4 mb-3 d-flex align-items-stretch"
        v-for="restaurant in restaurants"
    >
        <RestaurantItem :restaurant="restaurant" />
    </div>
</template>
