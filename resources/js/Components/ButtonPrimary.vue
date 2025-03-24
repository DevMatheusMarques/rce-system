<script>
export default {
    name: "ButtonPrimary",
    props: {
        meuId: String,
        name: String,
        delay: {
            default: 1500,
            type: Number,
        }
    },
    methods: {
        handleClick(event) {
            event.target.disabled = true;
            this.style = 'button--disabled';
            setTimeout(() => {
                event.target.disabled = false;
                this.style = 'button';
            }, this.delay);
        }
    }, data() {
        return {
            style: 'button'
        }
    }
}
</script>

<template>
    <button
        @click="handleClick"
        :id="meuId"
        :class="style"
    >{{ name }}
    </button>
</template>

<style scoped>
.button {
    background-color: var(--green-primary);
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background-color .2s ease-in-out;
}

.button:hover {
    background-color: var(--green-primary-hover);
}

.button--disabled {
    background-color: #76c38f;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background-color .2s ease-in-out;
}
</style>
