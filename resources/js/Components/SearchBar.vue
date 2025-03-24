<template>
    <div class="main__search" :id="customId">
        <input
            @keydown.prevent.enter="handleClick"
            :type="customType ?? 'text'"
            :name="customName ?? ''"
            :placeholder="placeholder ?? 'Pesquisar'"
            :id="customId ?? 'search-input'"
            v-model="inputValue"
            class="form__input"
            ref="searchInput"
            autocomplete="off"
            @input="removeStyleInputError"
        >
        <img v-if="showXButton" @click="handleCLickClearForm" class="x__img" src="/assets/icons/x-icon.svg" alt="">
        <span class="content__img" @click="handleClick">
            <img class="content__img--img" src="/assets/icons/search.svg" alt="">
        </span>
    </div>
</template>

<script>

export default {
    name: "SearchBar",
    emits: ['search', 'clearForm'],
    data() {
        return {
            showXButton: false,
            inputValue: ''
        }
    },
    watch: {
        customValue(val) {
            this.inputValue = this.customValue;
        },
        inputValue(value) {
            this.showXButton = value !== '';
        }
    },
    methods: {
        handleClick() {
            this.showXButton = true;
            this.$emit('search', this.inputValue);
        },
        handleCLickClearForm() {
            this.inputValue = '';
            this.showXButton = false;
            this.$emit('clearForm');
        },
        removeStyleInputError() {
            const input = document.getElementById(this.customId);
            if (input !== null && input.classList.contains('input-error')) {
                input.classList.remove('input-error');
            }
        },
    },
    props: {
        placeholder: String,
        customId: String,
        customType: String,
        customName: String,
        customValue: String|Number,
    }
}
</script>

<style scoped>
.main__search {
    display: flex;
    position: relative;
}

.form__input {
    width: 100%;
    border-radius: 4px 4px 4px 4px;
    border-right: none;
    padding-right: 2.5rem;
}

.x__img {
    position: absolute;
    right: 3rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1rem;
    cursor: pointer;
}

.content__img {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: .65rem;
    background-color: var(--gray-light);
    border: 1px solid var(--gray-light-hover);
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    transition: background-color .2s ease-in-out;
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
}

.content__img--img {
    width: 1.2rem;
}

.content__img:hover {
    background-color: var(--gray-light-hover);
}

</style>
