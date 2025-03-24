<script>
export default {
    name: "ButtonDanger",
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
        }    }, data() {
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
    >{{ name }}</button>
</template>

<style scoped>
.button {
    background-color: var(--red-danger);
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background-color .2s ease-in-out;
}
.button:hover {
    background-color: var(--red-danger-hover);
}
.button--disabled {
    background-color: #c37676;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background-color .2s ease-in-out;
}
</style>
