<script>
import {Head} from "@inertiajs/vue3";
import {Transition} from "vue";
import HeaderComponent from "@/Components/HeaderComponent.vue";
import Sidebar from "@/Components/Sidebar.vue";
import axios from "axios";
import Dashboard from "@/Pages/Dashboard.vue";
import Products from "@/Pages/Products.vue";
import Order from "@/Pages/Order.vue";
import Purchase from "@/Pages/Purchase.vue";
import Reports from "@/Pages/Reports.vue";
import Supplier from "@/Pages/Supplier.vue";
import User from "@/Pages/User.vue";

export default {
    name: "Layout",
    components: {
        Dashboard,
        Products,
        Order,
        Purchase,
        Reports,
        Supplier,
        User,
        Sidebar,
        HeaderComponent,
        Transition,
        Head
    },
    async mounted() {
        await this.userLogged();
    },
    updated() {
        this.$refs.header.handleFirstAccessUser();
    },
    methods: {
        async userLogged() {
            try {
                const response = await axios.get('/user/logged');
                this.authUser = response?.data?.user;
                this.currentComponent = this.authUser?.level !== 'operator' ? 'Dashboard' : 'Products';
            } catch (errors) {
                console.error(errors)
            }
        },
        handleUserUpdated(userUpdated) {
            this.authUser = userUpdated;
        },
        changeComponent(value) {
            this.currentComponent = value;
        }
    }, data() {
        return {
            authUser: null,
            currentComponent: ''
        }
    }
}
</script>

<template class="content" v-if="authUser">
    <Head name="Reports"/>
    <HeaderComponent v-if="authUser" :auth-user="authUser" @profile-updated="handleUserUpdated" ref="header" @user-updated="userLogged"/>
    <main class="content__main">
        <div class="content__main--sidebar">
            <Sidebar
                v-if="authUser"
                :currentComponent="currentComponent"
                :auth-user="authUser"
                @componentSelected="changeComponent"
            />
        </div>
        <div class="content__main--slot">
            <Transition name="fade" mode="out-in">
                <component
                    v-if="authUser"
                    :is="currentComponent"
                    :auth-user="authUser"
                />
            </Transition>
        </div>
    </main>
</template>

<style scoped>
@import '/resources/css/pages/layout.css';
</style>
