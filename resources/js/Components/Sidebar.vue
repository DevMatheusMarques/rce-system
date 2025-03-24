<script>
import Anchor from "@/Components/Anchor.vue";
import axios from "axios";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import {Link} from '@inertiajs/vue3';
import Tooltip from "@/Components/Tooltip.vue";
import SidebarItem from "@/Components/SidebarItem.vue";

export default {
    name: "Sidebar",
    components: {SidebarItem, Tooltip, Anchor, Link},
    props: {
        currentComponent: {
            type: String,
            required: true,
        },
        authUser: Object
    },
    mounted() {
        this.toast = useSwalAlert({}, 2000)
        this.handlePreferencesSidebar();

        if (this.authUser && this.authUser.level) {
            this.sidebarItems = this.allSidebarItems.filter(item =>
                item.levels.includes(this.authUser.level)
            );
        }
    },
    updated() {
        this.handlePreferencesSidebar();
    },
    data() {
        return {
            errors: [],
            toast: null,
            linkTextVisible: false,
            lock: false,
            classSidebar: 'sidebar__hover',
            isLoggingOut: false,
            sidebarItems: [],
            allSidebarItems: [
                {
                    name: "Dashboard",
                    icon: "/assets/icons/house-white.svg",
                    activeIcon: "/assets/icons/house-blue.svg",
                    customClass: '',
                    levels: ['admin', 'manager']
                },
                {
                    name: "Products",
                    icon: "/assets/icons/boxes-stacked-white.svg",
                    activeIcon: "/assets/icons/boxes-stacked-blue.svg",
                    customClass: '',
                    levels: ['admin', 'manager', 'operator']
                },
                {
                    name: "Purchase",
                    icon: "/assets/icons/cart-white.svg",
                    activeIcon: "/assets/icons/cart-blue-sidebar.svg",
                    customClass: '',
                    levels: ['admin', 'manager', 'operator']
                },
                {
                    name: "Order",
                    icon: "/assets/icons/clipboard-white.svg",
                    activeIcon: "/assets/icons/clipboard-blue.svg",
                    customClass: '',
                    levels: ['admin', 'manager', 'operator']
                },
                {
                    name: "User",
                    icon: "/assets/icons/user-white.svg",
                    activeIcon: "/assets/icons/user-blue.svg",
                    customClass: 'custom__link__icon',
                    levels: ['admin', 'manager']
                },
                {
                    name: "Supplier",
                    icon: "/assets/icons/truck-field-white.svg",
                    activeIcon: "/assets/icons/truck-field-blue.svg",
                    customClass: '',
                    levels: ['admin', 'manager', 'operator']
                }
            ]
        }
    },
    methods: {
        handlePreferencesSidebar() {
            if (this.authUser === null) {
                return
            }
            switch (this.authUser.sidebar) {
                case 'hover':
                    this.classSidebar = "sidebar__hover";
                    this.lock = false;
                    break;

                case 'open-lock':
                    this.classSidebar = "open";
                    this.lock = true;
                    this.linkTextVisible = true;
                    break;

                case 'close-lock':
                    this.classSidebar = "close";
                    this.lock = true;
                    this.linkTextVisible = false;
                    break;
            }
        },
        async logout() {
            try {
                const response = await axios.post('/logout');
                await this.toast.fire({
                    icon: 'success',
                    title: response.data.message,
                    timer: 1000
                });
                window.location.href = response.data.redirect;
            } catch (errors) {
                this.errors = errors.response.data.message;
                await this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            }
        },
        handleMouseEnter() {
            if (!this.lock && this.classSidebar === 'sidebar__hover') {
                this.linkTextVisible = true;
            }
        },
        handleMouseLeave() {
            if (!this.lock && this.classSidebar === 'sidebar__hover') {
                this.linkTextVisible = false;
            }
        },

    }, emits: ['componentSelected']
}

</script>

<template>
    <div :class="['sidebar', classSidebar]"
         @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave">
        <div class="sidebar__header">
            <div class="header__item">

                <img v-if="linkTextVisible" class="header__item--icon" src="/assets/icons/hamburguer.svg" alt="">
                <Tooltip text="Painel de Controle" v-if="!linkTextVisible">
                    <img class="header__item--icon" src="/assets/icons/hamburguer.svg" alt="">
                </Tooltip>
                <Transition name="fade">
                    <p class="link__text--panel" v-if="linkTextVisible">
                        Painel de Controle</p>
                </Transition>
            </div>
        </div>

        <ul class="sidebar__list">
            <SidebarItem
                v-for="item in sidebarItems"
                :key="item.name"
                :icon="item.icon"
                :activeIcon="item.activeIcon"
                :name="item.name"
                :currentComponent="currentComponent"
                :linkTextVisible="linkTextVisible"
                :customClass="item.customClass"
                @componentSelected="$emit('componentSelected', item.name)"
            />
        </ul>
        <div class="sidebar__footer">
            <div class="footer__item" @click="logout">
                <img class="link__icon"
                     src="/assets/icons/door-open-white.svg"
                     alt=""
                     v-if="linkTextVisible"
                >
                <Tooltip text="Sair" v-if="!linkTextVisible">
                    <img class="link__icon"
                         src="/assets/icons/door-open-white.svg"
                         alt=""
                    >
                </Tooltip>
                <Transition name="fade">
                    <p class="link__text" v-if="linkTextVisible"
                       :style="{ color: currentComponent === 'others' ? 'var(--blue-sidebar)' : 'white' }"
                    >
                        Sair</p>
                </Transition>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import "/resources/css/components/sidebar.css";
</style>
