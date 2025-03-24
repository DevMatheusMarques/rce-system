<script>
import Dropdown from "@/Components/Dropdown.vue";
import ThTable from "@/Components/Table/ThTable.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import InputCheckbox from "@/Components/InputCheckbox.vue";

export default {
    name: "TableSupplierLoading",
    components: {InputCheckbox, DropdownLink, ThTable, Dropdown},
    data() {
        return {
            faker: [
                {id: 1}, {id: 2}, {id: 3}, {id: 4}, {id: 5}, {id: 6}, {id: 7}, {id: 8}, {id: 9},
            ]
        }
    }, props: {
        authUser: Object
    }
}
</script>

<template>
    <tbody>
    <tr v-for="(fake, index) in faker" :key="index" class="row">
        <td><p class="loading__background hello">00.000.000/0000-00</p></td>
        <td><p class="loading__background hello">lorem ipsun lorem ipsun lorem ipsun lorem ipsun </p></td>
        <td><p class="loading__background hello">Presidente Prudente</p></td>
        <td><p class="loading__background hello">São Paulo</p></td>
        <template v-if="authUser.level !== 'operator'">
            <td><p class="loading__background hello">Ações</p></td>
        </template>
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
