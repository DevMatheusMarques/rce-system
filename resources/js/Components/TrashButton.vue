<template>
    <button class="content__img" @click="handleClick">
        <img class="content__img--img" src="/assets/icons/trash.svg" alt="">
    </button>
</template>

<script>
export default {
    name: "TrashButton",
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

<style scoped>

.content__img {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: .5rem;
    background-color: var(--gray-light);
    border: 1px solid var(--gray-light-hover);
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    transition: background-color .2s ease-in-out;
}

.content__img--img {
    width: 1rem;
}

.content__img:hover {
    background-color: var(--gray-light-hover);
}
</style>
