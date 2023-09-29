<script setup lang="ts">
import PrimaryButton from "../PrimaryButton.vue";
import TextInput from "../TextInput.vue";

defineProps<{
    form: any;
    submit?: () => {};
}>();
// default area distance
const distances = [1, 2, 5, 10, 15, 20, 30];
</script>

<template>
    <form @submit.prevent="submit">
        <div class="hero-search-content">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="form-group">
                        <div class="input-with-icon">
                            <select
                                id="distanct"
                                class="form-control"
                                v-model="form.distance"
                            >
                                <option v-for="distance in distances" :value="distance">{{ distance }} km</option>
                            </select>
                            <i class="ti-dashboard"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div
                    class="col-lg-9 col-md-9 col-sm-8 col-9 px-xl-2 px-lg-2 px-md-2"
                >
                    <!-- input search keyword -->
                    <div class="form-group">
                        <TextInput
                            id="q"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.q"
                            required
                            autocomplete="q"
                            placeholder="Ex. Bang sue"
                        />
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-4 col-3 pr-5">
                    <!-- button search place -->
                    <div class="form-group">
                        <PrimaryButton
                            class="ml-4"
                            :class="{
                                'opacity-25': form.processing,
                            }"
                            :disabled="form.processing"
                        >
                            Search
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
