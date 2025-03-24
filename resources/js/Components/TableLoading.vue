<script>

export default {
    name: "TableLoading",
    props: {
        countRows: {
            type: Number,
            required: true
        },
        countColumns: {
            type: Number,
            required: true
        },
        countWord: {
            type: Number,
            required: true
        },
    },
    computed: {
        items() {
            return Array.from({length: this.count});
        }
    }, data() {
        return {
            word: 'loren'
        }
    }
}
</script>

<template>
    <tbody>
    <tr v-for="row in countRows" :key="row" class="row">
        <td v-for="column in countColumns">
            <p class="loading__background hello">loren</p>
        </td>
    </tr>
    </tbody>
</template>

<style scoped>
.hello {
    width: 80%;
    height: 50%;
    margin-top: 12px;
}

@keyframes placeHolderShimmer {
    0% {
        background-position: -468px 0;
    }
    100% {
        background-position: 468px 0;
    }
}

.loading__background {
    animation-duration: 1.25s;
    animation-fill-mode: forwards;
    animation-iteration-count: infinite;
    animation-name: placeHolderShimmer;
    animation-timing-function: linear;
    background: linear-gradient(to right, var(--gray-light-hover) 10%, var(--gray-light) 18%, var(--gray-light-hover) 33%);
    background-size: 800px 104px;
    color: transparent;
}

tbody {
    user-select: none;
}

td:first-of-type {
    border-left: 4px solid var(--blue-secondary);
    padding-left: .5rem;
}

.row {
    border-bottom: 1px solid var(--gray-dark);
    height: 3rem;
}
</style>
