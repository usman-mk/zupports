<script setup lang="ts">
import { GoogleMap, Marker, InfoWindow, MarkerCluster } from "vue3-google-map";
import { useForm } from "@inertiajs/vue3";
import { onMounted, ref, Ref } from "vue";
import axios from "axios";

import MainLayout from "@/Layouts/MainLayout.vue";
import Form from "@/Components/Map/Form.vue";
import Restaurants from "@/Components/Map/Restaurants.vue";

// create type restaurant
type Restaurant = {
    id: string;
    name: string;
    address: string;
    photo: string;
    rating: number;
    latitude: number;
    longitude: number;
    open_now: boolean;
    types: string[];
    distance: Distance;
};

// create type distance
type Distance = {
    text: string;
    value: number;
};

// create type marker option
type MarkerOption = {
    position: {
        lat: number;
        lng: number;
    };
    label: string;
    title: string;
};

// Create reactive data
const pageToken = ref("");
const loadMoreText = ref("Load more");
// get api key from env file
const apiKey = ref(window.config.apiKey);
const latitude: Ref<number> = ref(0); // Set your desired latitude value
const longitude: Ref<number> = ref(0); // Set your desired longitude value
const currentLatitude: Ref<number> = ref(0); // Set your desired latitude value
const currentLongitude: Ref<number> = ref(0); // Set your desired longitude value
const center = ref({ lat: latitude.value, lng: longitude.value });
const area: Ref<number> = ref(1);
const restaurants: Ref<Restaurant[]> = ref([]);
const markerOptions: Ref<MarkerOption[]> = ref([]);
// set default is loading
const isLoading = ref(true);
// create form
const form = useForm({
    distance: 1,
    q: "",
});
// submit form search
const submit = async () => {
    // show loading
    isLoading.value = true;
    const response = await axios.get(route("search-places", { q: form.q }));

    // Handle the response here
    const data = response.data.data;

    getNearby(
        data.geometry.location.lat,
        data.geometry.location.lng,
        form.distance
    );
};

const getNearby = async (lat: number, lng: number, distance: number) => {
    const data = {
        search_latitude: lat,
        search_longitude: lng,
        current_latitude: currentLatitude.value,
        current_longitude: currentLongitude.value,
        distance: distance,
    };

    center.value = {
        lat: lat,
        lng: lng,
    };

    await axios
        .post(route("get-nearby"), data)
        .then((response) => {
            restaurants.value = response.data.data.data;
            // add marker
            addMarkers();
            // set page next token
            pageToken.value = response.data.data.pageToken;
        })
        .finally(() => {
            // hide loading
            isLoading.value = false;
        })
        .catch((error) => {
            console.error(
                "Error the location:",
                error
            );
        });
};

const getCurrentLocation = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                latitude.value = lat;
                longitude.value = lng;
                // set current location
                currentLatitude.value = lat;
                currentLongitude.value = lng;

                getNearby(lat, lng, form.distance);
            },
            (error) => {
                console.log(error);
            }
        );
    } else {
        console.error("Geolocation is not supported by this browser.");
    }
};

const addMarkers = () => {
    // reset marker before add
    resetMarkers();
    // add marker
    restaurants.value.forEach((restaurant, i) => {
        // create marker data
        const marker = {
            position: {
                lat: restaurant.latitude,
                lng: restaurant.longitude,
            },
            label: "",
            title: restaurant.name,
        };
        // push array marker
        markerOptions.value.push(marker);
    });
};

const resetMarkers = () => {
    // Clear the markers array
    markerOptions.value = [];
};

const loadMore = async (token: string) => {
    // show text loading button
    loadMoreText.value = "Loading...";
    // show loading
    isLoading.value = true;
    const data = {
        search_latitude: latitude.value,
        search_longitude: longitude.value,
        current_latitude: currentLatitude.value,
        current_longitude: currentLongitude.value,
        distance: area.value,
        pageToken: token,
    };
    // get nearby API
    await axios
        .post(route("get-nearby"), data)
        .then((response) => {
            restaurants.value = restaurants.value.concat(
                response.data.data.data
            );
            loadMoreText.value = "Load more";
            pageToken.value =
                response.data.data.pageToken === null
                    ? null
                    : response.data.data.pageToken;
        })
        .finally(() => {
            // hide loading
            isLoading.value = false;
        })
        .catch((error) => {
            console.error(
                "Error the location:",
                error
            );
        });
};

onMounted(() => {
    // current location
    getCurrentLocation();
});
</script>

<template>
    <MainLayout>
        <!-- Loading -->
        <div class="loading" v-if="isLoading">Loading&#8230;</div>
        <div class="container">
            <!-- form search -->
            <div class="row">
                <div class="col-12">
                    <div class="p-5 mb-4 bg-light rounded-3">
                        <div class="container-fluid py-5">
                            <Form :form="form" :submit="submit" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- google map -->
            <div class="row">
                <div class="col-12">
                    <GoogleMap
                        style="width: 100%; height: 500px;"
                        :api-key="apiKey"
                        :center="center"
                        :zoom="15"
                    >
                        <MarkerCluster>
                            <!-- set marker -->
                            <Marker
                                v-for="markerOption in markerOptions"
                                :options="markerOption"
                            >
                                <InfoWindow>
                                    <div id="content">
                                        This is the infowindow content
                                    </div>
                                </InfoWindow>
                            </Marker>
                        </MarkerCluster>
                    </GoogleMap>
                </div>
            </div>
        </div>

        <!-- card restaurant -->
        <div class="container" v-if="restaurants.length > 0">
            <div class="row justify-content-center">
                <div class="col-12 text-center">
                    <div class="my-3 p-3">
                        <h2 class="display-5">Restaurants near by.</h2>
                    </div>
                </div>
            </div>

            <div class="album py-5 bg-light">
                <div class="container">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                        <Restaurants :restaurants="restaurants" />
                    </div>
                </div>
            </div>

            <!-- Load more button -->
            <div class="row" v-if="pageToken != null && restaurants.length > 0">
                <div class="col-12 text-center">
                    <button
                        type="button"
                        @click="loadMore(pageToken)"
                        class="btn btn-default"
                    >
                        {{ loadMoreText }}
                    </button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
