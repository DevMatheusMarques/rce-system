<script xmlns="http://www.w3.org/1999/html">
export default {
    name: "InputCheckbox",
    props: {
        ckbId: {
            type: String,
            required: true,
        },
        ckbChecked: {
            type: Boolean,
        },
        ckbClass: {
            type: String,
        }
    }
}
</script>

<template>
    <input type="checkbox" :class="['checkbox__input', ckbClass]" :id="ckbId" :checked="ckbChecked" />
</template>

<style scoped>
@import "/resources/css/components/input-checkbox.css";
</style>
