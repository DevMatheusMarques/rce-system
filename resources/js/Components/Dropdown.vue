<template>
    <div class="dropdown">
        <button @click="toggleDropdown">
            {{ buttonLabel }}
            <img src="/assets/icons/angle-down-blue.svg"
                 class="arrow-icon" alt="Seta para baixo">
        </button>
        <div v-if="isOpen" class="dropdown__menu">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            isOpen: false
        };
    },
    props: {
        buttonLabel: {
            type: String,
        },
        opened: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        toggleDropdown() {
            this.isOpen = !this.isOpen;
        }
    },
    watch: {
        opened(newVal, oldVal) {
            if (newVal) {
                this.isOpen = false;
            }
        }
    }
};
</script>

<style scoped>
@import "/resources/css/components/dropdown.css";
</style>
