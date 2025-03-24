<script>
export default {
    name: "Pagination",
    props: {
        pagination: {}
    },
    computed: {
        indexNextPage() {
            return Object.keys(this.pagination.links).length - 1
        },
    },
    methods: {
        toPage(url){
            if (url) {
                this.$emit('toPage', url);
            }
        }
    }, emits: ['toPage']
}
</script>

<template>
    <div class="pagination">
        <div class="pagination__abstract">
            <p> Exibindo {{ pagination.current_page }} de {{ pagination.last_page }} páginas, com total de {{ pagination.total }} resultados.</p>
        </div>
        <template v-if="pagination.to !== 0">

            <div class="pagination__navigation">
                <div
                    v-for="(link, index) in pagination.links"
                    :key="index"
                    class="navigation"
                >
                    <button
                        @click="toPage(link.url)"
                        :class="
                        ['navigation__previous', 'navigation__link',
                            link.active ? 'link__active' : '',

                         ]"
                        v-if="index === 0">
                        <img class="navigation--img" src="/assets/icons/angle-left.svg" alt="">
                    </button>

                    <button
                        @click="toPage(link.url)"
                        v-if="index !== 0 && index !== indexNextPage"
                        :class="
                        ['navigation__link',
                         link.active ? 'link__active' : '',
                         ]"
                    >
                        {{ link.label }}
                    </button>

                    <button
                        @click="toPage(link.url)"
                        :class="
                        ['navigation__next', 'navigation__link',
                         link.active ? 'link__active' : '',

                         ]"
                        v-if="index === indexNextPage">

                        <img class="navigation--img" src="/assets/icons/angle-right.svg" alt="">
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
@import "../../../css/components/table/pagination.css";
</style>
