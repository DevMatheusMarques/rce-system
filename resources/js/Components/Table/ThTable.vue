<script>
export default {
    name: "ThTable",
    props: {
        thOrdered: {
            type: Boolean,
            default: true
        },
        thId: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            iconActive: '/assets/icons/sort.svg',
            countIcon: 1
        }
    },
    methods: {
        changeIconActive() {
            if (this.thOrdered) {
                switch (this.countIcon) {
                    case 0:
                        this.iconActive = '/assets/icons/sort.svg';
                        this.countIcon++;
                        this.$emit('order');
                        break
                    case 1:
                        this.iconActive = '/assets/icons/sort-up.svg';
                        this.countIcon++;
                        this.$emit('order', {order_direction: 'asc', order_by: this.thId});
                        this.restoreDefaultOthersTh();
                        break
                    case 2:
                        this.iconActive = '/assets/icons/sort-down.svg'; //maior para o menor
                        this.countIcon = 0;
                        this.$emit('order', {order_direction: 'desc', order_by: this.thId});
                        this.restoreDefaultOthersTh();
                        break
                    default:
                        this.iconActive = '/assets/icons/sort.svg';
                        this.countIcon = 0;
                }
            }
        },
        restoreDefaultOthersTh() {
            const ths = document.querySelectorAll('.custom__th--icon');
            ths.forEach(th => {
                th.src = '/assets/icons/sort.svg';
            });
        }
    }, emits: ['order'],
}
</script>

<template>
    <th scope="col"
        :id="thId"
        :class="['th', thOrdered ? 'th__ordered' : '']"
        @click="changeIconActive"
    >
        <div class="th__content">
            <div class="content__text">
                <slot/>
            </div>
            <div v-if="thOrdered" class="content__img">
                <img
                    :src="iconActive"
                    alt="" class="content__img--img custom__th--icon">
            </div>
        </div>
    </th>
</template>

<style scoped>
.th {
    background-color: var(--gray-light);
    padding: 0 .7rem;
}

.th__ordered {
    cursor: pointer;
    user-select: none;
}

.th__content {
    display: flex;
    padding: .2rem 0;
    justify-content: space-between;
    align-items: center;
}

.th__center .th__content {
    display: flex;
    justify-content: center;
    align-items: center;
}

.content__text {
    white-space: nowrap;
}

img {
    max-width: 100%;
    margin-left: 1rem;
}

.content__img {
    justify-self: center;
}

.content__img--img {
    width: .5rem;
}

</style>
