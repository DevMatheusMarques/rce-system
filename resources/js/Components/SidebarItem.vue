<script>
import Tooltip from "@/Components/Tooltip.vue";
import useTranslateText from "../Composables/useTranslateText.js";

export default {
    name: "SidebarItem",
    methods: {useTranslateText},
    components: {Tooltip},
    props: {
        icon: String,
        activeIcon: String,
        name: String,
        currentComponent: String,
        linkTextVisible: Boolean,
        customClass: String,
    },
    emits: ['componentSelected']
}
</script>

<template>
    <li class="sidebar__item">
        <div
            @click="$emit('componentSelected')"
            :class="['list__item', { 'list__item--active': currentComponent === name }]"
        >
            <img
                :class="[customClass !== '' ? customClass : 'link__icon']"
                :src="currentComponent === name ? activeIcon : icon"
                alt=""
                v-if="linkTextVisible"
            />
            <Tooltip :text="useTranslateText(name)" v-if="!linkTextVisible">
                <img
                    :class="[customClass !== '' ? customClass : 'link__icon']"
                    :src="currentComponent === name ? activeIcon : icon"
                    alt=""
                />
            </Tooltip>
            <Transition name="fade">
                <p
                    class="link__text"
                    v-if="linkTextVisible"
                    :style="{color: currentComponent === name ? 'var(--blue-sidebar)' : 'white',}"
                >
                    {{ useTranslateText(name) }}
                </p>
            </Transition>
        </div>
    </li>
</template>
<style scoped>
@import "/resources/css/components/sidebar.css";
</style>