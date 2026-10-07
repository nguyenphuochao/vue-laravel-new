<script setup>
import { ref } from "vue";

const msg = ref("Hello World!");

function updateMsg() {
  msg.value = "My name is James";
}
</script>

<template>
  <div class="my-component">{{ msg }}</div>
  <button @click="updateMsg">Update</button>
</template>

<style lang="scss" scoped>
    .my-component {
        color: red;
    }
</style>
