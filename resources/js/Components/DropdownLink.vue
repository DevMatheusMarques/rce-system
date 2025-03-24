<template>
    <div class="dropdown__link" @click="handleClick">
        <slot></slot>
    </div>
</template>

<script>
export default {
    props: {
        action: {
            type: Function,
        }
    },
    methods: {
        handleClick() {
            // this.action();
        }
    }
};
</script>

<style scoped>
@import "/resources/css/components/dropdown-link.css";
</style>
