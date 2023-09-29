<script setup lang="ts">
import { onMounted, ref, Ref } from "vue";

defineProps<{
    restaurant: any;
}>();

const currentOrigin: Ref<string> = ref(''); // Set your desired latitude value

const getCurrentLocation = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                currentOrigin.value = `${position.coords.latitude},${position.coords.longitude}`;
            },
            (error) => {
                console.error(error);
            }
        );
    } else {
        console.log("Geolocation is not supported by this browser.");
    }
};

const redirectLocation = (latitude: number, longitude: number): void => {
    // redirect to Google Map
    const navigateUrl = `https://www.google.com/maps/dir/${currentOrigin.value}/${latitude},${longitude}`;
    window.open(navigateUrl, "_blank");
};

onMounted(() => {
    // current location
    getCurrentLocation();
});
</script>

<template>
    <div class="card flex flex-col justify-between">
        <!-- set photo -->
        <img
            v-if="restaurant.photo != null"
            :src="restaurant.photo"
            class="object-cover"
            alt="Location Image"
        />
        <img
            v-else
            src="https://placehold.co/1200x750?text=No%20Image"
            class="object-cover"
            alt="Location Image"
        />
        <div class="card-body d-flex flex-column justify-between">
            <h5 class="card-title font-bold">
                {{ restaurant.name }}
            </h5>
            <p class="card-text mb-4">
                {{ restaurant.address }}
            </p>
            <div class="row">
                <div class="col-6">
                    <button
                        type="button"
                        class="btn btn-info"
                        @click="
                            redirectLocation(
                                restaurant.latitude, restaurant.longitude
                            )
                        "
                    >
                        Direction
                    </button>
                </div>
                <div class="col-6 text-right">
                    <small class="text-muted">{{
                        restaurant.distance?.text || null
                    }}</small>
                </div>
            </div>
        </div>
    </div>
</template>
