<template>
    <section class="content">
        <section class="content__section content__section--components">
            <Transition name="fade" mode="out-in">
                <component :is="currentComponent" @show-register="showRegister" @show-login="showLogin"
                           @show-forgot="showForgotPassword"/>
            </Transition>

        </section>

        <section class="content__section content__section--info">
            <h1 class="content__info--title">
                Gerencie seu estoque com precisão
            </h1>
            <div class="content__topics">
                <p class="content__info--slogan">
                    Com nosso produto, você tem controle total e otimiza suas operações.
                </p>
                <div class="content__info--box">
                    <p class="content__info--topics">
                        <span class="content__info--icons">📦</span> Controle total de estoque, sem complicação
                    </p>
                </div>
                <div class="content__info--box">
                    <p class="content__info--topics">
                        <span class="content__info--icons content__info--icon-lamp">💡</span><span
                        class="content__info--demand">Previsão de demanda precisa</span>
                    </p>
                </div>
                <div class="content__info--box">
                    <p class="content__info--topics">
                        <span class="content__info--icons">📊</span> Relatórios em tempo real
                    </p>
                </div>
            </div>
        </section>
    </section>
</template>

<script>
import Login from "@/Pages/Auth1/Login.vue";
import Register from "@/Pages/Auth1/Register.vue";
import ForgotPassword from "@/Pages/Auth1/ForgotPassword.vue";
import {Transition} from "vue";

export default {
    name: "Authentication",
    components: {Login, Register, ForgotPassword, Transition},
    data() {
        return {
            currentComponent: "Login"
        }
    },
    methods: {
        showRegister() {
            this.currentComponent = "Register";
        },
        showLogin() {
            this.currentComponent = "Login";
        },
        showForgotPassword() {
            this.currentComponent = "ForgotPassword";
        }
    }
}
</script>

<style scoped>
@import "/resources/css/pages/authentication.css";
</style>
