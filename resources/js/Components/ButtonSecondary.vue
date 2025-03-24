<script>
export default {
    name: "ButtonSecondary",
    props: {
        meuId: String,
        name: String
    },
    methods: {
        handleClick(event) {
            event.target.disabled = true;
            this.style = 'button--disabled';
            setTimeout(() => {
                event.target.disabled = false;
                this.style = 'button';
            }, 1500);
        }
    }
}
</script>

<template>
    <button
        @click="handleClick"
        :id="meuId" class="button">{{ name }}</button>
</template>

<style scoped>
.button {
    background-color: var(--gray-light);
    color: var(--blue-secondary);
    padding: 0 .7rem;
    height: 100%;
    border-radius: 4px;
    border: 1px solid var(--gray-light-hover);
    transition: background-color .2s ease-in-out;
}
.button:hover {
    background-color: var(--gray-light-hover);
}
</style>
